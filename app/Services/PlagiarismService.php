<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Cache;
use App\Models\Title;   
class PlagiarismService
{
    /** Windowing & scoring knobs */
    private const PLAG_WINDOW_WORDS       = 50;
    private const PLAG_STRIDE_WORDS       = 25;
    private const PLAG_MIN_CHUNK_WORDS    = 12;
    private const PLAG_MATCH_THRESHOLD    = 0.60;
    private const PLAG_TOP_MATCHES        = 30;
    private const PLAG_OVERALL_MULTIPLIER = 100;

    public function quickScore(string $rawHtmlOrText, Document $document): float
    {
        return $this->plagMaxChunkSimilarity($rawHtmlOrText, $document);
    }

    public function detailedMatches(string $html, Document $document, int $minPercent = 0): array
    {
        $txt        = $this->plagStartAfterChapterOne($this->plagCleanText($html));
        $yourChunks = $this->plagMakeChunks($txt);

        $candidateChunks = $this->plagBuildCandidateChunks($document);

        $matches    = [];
        $overallMax = 0.0;
        // convert 0–100 → 0–1, clamp
        $minSim = max(0.0, min(1.0, $minPercent / 100));

        foreach ($yourChunks as $yc) {
            $yVec = $yc['vec']; $yMag = $yc['mag'];
            if ($yMag == 0) continue;

            foreach ($candidateChunks as $cc) {
                $den = $yMag * $cc['mag']; if ($den == 0) continue;
                $sim = $this->plagDotProduct($yVec, $cc['vec']) / $den;

                if ($sim > $overallMax) $overallMax = $sim;

               if ($sim >= $minSim) {
                    $matches[] = [
                        'percent'        => round($sim * self::PLAG_OVERALL_MULTIPLIER, 2),
                        'your_excerpt'   => $yc['text'],
                        'source_excerpt' => $cc['text'],
                        'source_title'   => $cc['source_title'],
                        'source_chapter' => $cc['source_chapter'],
                        'document_id'    => $cc['document_id'],
                    ];
                }
            }
        }

        usort($matches, fn($a,$b)=>$b['percent'] <=> $a['percent']);
        $matches = array_slice($matches, 0, self::PLAG_TOP_MATCHES);

        return [
            'score'   => round($overallMax * self::PLAG_OVERALL_MULTIPLIER, 2),
            'matches' => $matches,
            'meta'    => [
                'threshold'  => $minPercent,
                'window'     => self::PLAG_WINDOW_WORDS,
                'stride'     => self::PLAG_STRIDE_WORDS,
                'candidates' => count($candidateChunks),
            ],
        ];
    }

    /** ---------- Internals (moved as‑is) ---------- */

    private function plagMakeChunks(string $text): array
    {
        $words = preg_split('/\s+/u', trim($text));
        $words = array_values(array_filter($words, fn($w)=>$w!==''));

        $chunks = []; $n = count($words);
        if ($n < self::PLAG_MIN_CHUNK_WORDS) return [];

        for ($i=0; $i<$n; $i+=self::PLAG_STRIDE_WORDS) {
            $slice = array_slice($words, $i, self::PLAG_WINDOW_WORDS);
            if (count($slice) < self::PLAG_MIN_CHUNK_WORDS) break;

            $chunkText = implode(' ', $slice);
            $vec = $this->plagTermFreqMap($this->plagTokenize($this->plagNormalizeText($chunkText)));
            $mag = $this->plagMagnitude($vec);

            $chunks[] = ['text'=>$chunkText, 'vec'=>$vec, 'mag'=>$mag];
        }
        return $chunks;
    }

    private function plagStartAfterChapterOne(string $text): string
    {
        $t = ltrim($text);

        if (preg_match('/\bchapter\s*(?:1|i|one)\b/iu', $t, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            return ltrim(mb_substr($t, $pos));
        }
        if (preg_match('/\bintroduction\b/iu', $t, $m2, PREG_OFFSET_CAPTURE)) {
            $pos = $m2[0][1];
            if ($pos < (int)(mb_strlen($t) * 0.25)) return ltrim(mb_substr($t, $pos));
        }
        return $t;
    }

    private function plagCleanText($html)
    {
        $html = preg_replace('/<img[^>]+src="data:image\/[^"]+"[^>]*>/i', '', $html);
        $html = preg_replace('/<div class="ck[^"]*"[^>]*>.*?<\/div>/si', '', $html);
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);
        return trim($text);
    }

    private function plagNormalizeText($text)
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);

        $stop = ['the','is','at','which','on','a','an','of','to','in','and','with','for','as','by','are'];
        $words = explode(' ', $text);
        $filtered = array_filter($words, fn($w)=>$w!=='' && !in_array($w, $stop, true));

        return implode(' ', $filtered);
    }
    private function plagTokenize($text){ return array_filter(explode(' ', $text)); }
    private function plagTermFreqMap($tokens){
        $f=[]; foreach($tokens as $t){ $t=trim($t); if($t==='')continue; $f[$t]=($f[$t]??0)+1; } return $f;
    }
    private function plagMagnitude($vector){ $s=0.0; foreach($vector as $v){ $s+=$v*$v; } return sqrt($s); }
    private function plagDotProduct($a,$b){ $d=0.0; foreach($a as $k=>$v){ if(isset($b[$k])) $d+=$v*$b[$k]; } return $d; }

   
    private function plagBuildCandidateChunks(Document $document): array
    {
        // ⚠️ finals-only + submitted-only cache
        $cacheKey = 'plag:cchunks:finals_only:submitted:exclude_title:' . $document->title_id;

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($document) {

            // Load OTHER titles (not this one), that are submitted and have a final document
            $finalTitles = \App\Models\Title::query()
                ->where('id', '!=', $document->title_id)
                ->where('status', 'submitted')                  // ← only submitted
                ->whereNotNull('final_document_id')             // ← must have a final doc
                ->with([
                    'finalDocument:id,title_id,chapter,content',
                    'owner:id',                                  // optional; useful for exclusions
                ])
                ->get(['id','title','final_document_id','owner_id']);

            $out = [];

            foreach ($finalTitles as $t) {
                $finalDoc = $t->finalDocument;
                if (!$finalDoc || empty($finalDoc->content)) continue;

                // OPTIONAL: also exclude same owner across different titles
                // if (($document->title->owner_id ?? null) === ($t->owner_id ?? null)) {
                //     continue;
                // }

                // Clean + chunk the FINAL doc content
                $srcText = $this->plagStartAfterChapterOne($this->plagCleanText($finalDoc->content ?? ''));
                foreach ($this->plagMakeChunks($srcText) as $chunk) {
                    $out[] = [
                        'document_id'    => $finalDoc->id,
                        'source_title'   => $t->title ?? 'Untitled',
                        'source_chapter' => $finalDoc->chapter ?? 'Final',
                        'text'           => $chunk['text'],
                        'vec'            => $chunk['vec'],
                        'mag'            => $chunk['mag'],
                    ];
                }
            }

            return $out;
        });
    }

    private function plagMaxChunkSimilarity(string $rawHtmlOrText, Document $document): float
    {
        $text = $this->plagStartAfterChapterOne($this->plagCleanText($rawHtmlOrText));
        $yourChunks = $this->plagMakeChunks($text);
        if (empty($yourChunks)) return 0.0;

        $cChunks = $this->plagBuildCandidateChunks($document);
        if (empty($cChunks)) return 0.0;

        $overallMax = 0.0;
        foreach ($yourChunks as $yc) {
            $yVec = $yc['vec']; $yMag = $yc['mag']; if ($yMag==0) continue;
            foreach ($cChunks as $cc) {
                $den = $yMag * $cc['mag']; if ($den==0) continue;
                $sim = $this->plagDotProduct($yVec, $cc['vec']) / $den;
                if ($sim > $overallMax) $overallMax = $sim;
            }
        }
        return round($overallMax * 100, 2);
    }
}
