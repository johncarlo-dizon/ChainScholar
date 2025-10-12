<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Title;
use App\Models\ResearchPaper;
use Illuminate\Support\Facades\Cache;

/**
 * Improved plagiarism checker:
 * - Less aggressive text processing
 * - Better filtering of common phrases
 * - Higher thresholds for meaningful matches
 * - Reduced false positives
 */
class PlagiarismService
{
    /** -------- Balanced Tunables -------- */
    private const WINDOW_WORDS        = 40;   // smaller windows for better precision
    private const STRIDE_WORDS        = 25;   // larger stride to reduce overlap
    private const MIN_CHUNK_WORDS     = 15;   // ignore very small slices
    private const NGRAM_N             = 6;    // longer sequences for better accuracy
    private const RETURN_TOP_MATCHES  = 20;   // cap cards shown
    private const SCORE_SCALE         = 100.0;
    private const CACHE_MINUTES       = 10;
    private const MIN_SIMILARITY      = 0.08; // 8% minimum to be considered a match

    // Weighted combination for better balance
    private const USE_WEIGHTED = true;
    private const COS_W        = 0.7;  // higher weight to semantic similarity
    private const JAC_W        = 0.3;  // lower weight to exact matches

    // stopword list (compact)
    private static array $STOP = [
        'a'=>1,'an'=>1,'the'=>1,'and'=>1,'or'=>1,'but'=>1,'if'=>1,'while'=>1,'at'=>1,'by'=>1,'for'=>1,'with'=>1,'about'=>1,'against'=>1,'between'=>1,'into'=>1,'through'=>1,'during'=>1,'before'=>1,'after'=>1,'above'=>1,'below'=>1,'to'=>1,'from'=>1,'up'=>1,'down'=>1,'in'=>1,'out'=>1,'on'=>1,'off'=>1,'over'=>1,'under'=>1,'again'=>1,'further'=>1,'then'=>1,'once'=>1,'here'=>1,'there'=>1,'when'=>1,'where'=>1,'why'=>1,'how'=>1,'all'=>1,'any'=>1,'both'=>1,'each'=>1,'few'=>1,'more'=>1,'most'=>1,'other'=>1,'some'=>1,'such'=>1,'no'=>1,'nor'=>1,'not'=>1,'only'=>1,'own'=>1,'same'=>1,'so'=>1,'than'=>1,'too'=>1,'very'=>1,'can'=>1,'will'=>1,'just'=>1,'don'=>1,'should'=>1,'now'=>1,'is'=>1,'am'=>1,'are'=>1,'was'=>1,'were'=>1,'be'=>1,'been'=>1,'being'=>1,'of'=>1,'as'=>1,'it'=>1,'its'=>1,'this'=>1,'that'=>1,'these'=>1,'those'=>1,'which'=>1,'who'=>1,'whom'=>1,'what'=>1,'via'=>1
    ];

    // Common academic phrases to ignore
    private static array $COMMON_PHRASES = [
        'this study shows that', 'in this paper we', 'the results indicate',
        'as shown in table', 'it can be seen that', 'in conclusion',
        'the purpose of this', 'this research examines', 'the data suggest',
        'previous research has', 'literature review shows', 'methodology section describes',
        'findings of this study', 'limitations of this study', 'future research should',
        'according to the results', 'the analysis reveals that', 'in summary',
        'the main objective is', 'as previously mentioned'
    ];

    /** Cached corpus stats */
    private array $idf = [];           // token => idf weight
    private array $commonNgrams = [];  // n-grams flagged as boilerplate

    // Limiters for safety
    private const MAX_PDFS                  = 1000;
    private const MAX_CHUNKS_PER_SOURCE     = 200;   // reduced from 250
    private const MAX_EXCERPT_CHARS         = 480;

    /** ---------- Public API ---------- */

    /** Quick overall score = max similarity across windows */
    public function quickScore(string $rawHtmlOrText, Document $document): float
    {
        $clean = $this->stripBoilerplate($this->htmlToCleanText($rawHtmlOrText));
        $your  = $this->makeChunks($clean);

        $cands = $this->candidateChunks($document);
        if (empty($your) || empty($cands)) return 0.0;

        $this->ensureCorpusStats();

        $max = 0.0;
        foreach ($your as $yc) {
            foreach ($cands as $cc) {
                $sim = $this->combinedSimilarity($yc, $cc);
                if ($sim > $max) $max = $sim;
            }
        }
        return round($max * self::SCORE_SCALE, 2);
    }

    /** Detailed matches (cards) + overall score */
    public function detailedMatches(string $html, Document $document, int $minPercent = 0): array
    {
        $txt  = $this->stripBoilerplate($this->htmlToCleanText($html));
        $your = $this->makeChunks($txt);

        if (empty($your)) {
            return [
                'score'   => 0,
                'matches' => [],
                'aggregate' => [],
                'meta' => [
                    'window'     => self::WINDOW_WORDS,
                    'stride'     => self::STRIDE_WORDS,
                    'ngram'      => self::NGRAM_N,
                    'candidates' => 0,
                    'note'       => 'No analyzable content (too short).',
                ],
            ];
        }

        $cands  = $this->candidateChunks($document);
        $minSim = max($minPercent / self::SCORE_SCALE, self::MIN_SIMILARITY);

        $this->ensureCorpusStats();

        $matches = [];
        $overall = 0.0;
        $seenYour = [];
        $bySource = [];

        foreach ($your as $yc) {
            $best = null;
            foreach ($cands as $cc) {
                $sim = $this->combinedSimilarity($yc, $cc);
                $simPct = $sim * self::SCORE_SCALE;
                
                if ($sim < $minSim) continue;
                if ($simPct > $overall) $overall = $simPct;

                $m = [
                    'percent'        => round($simPct, 2),
                    'your_excerpt'   => $yc['text'],
                    'source_excerpt' => $cc['text'],
                    'source_title'   => $cc['source_title'],
                    'source_chapter' => $cc['source_chapter'],
                    'document_id'    => $cc['document_id'],
                ];

                // Quality check - skip poor matches
                if (!$this->isQualityMatch($m)) continue;
                
                if ($best === null || $m['percent'] > $best['percent']) $best = $m;
            }
            
            if ($best) {
                // Compact excerpts
                $trim = static function (string $s, int $max = 1200): string {
                    $s = trim($s);
                    return mb_strlen($s) > $max ? (mb_substr($s, 0, $max) . '…') : $s;
                };
                $best['your_excerpt']   = $trim((string)$best['your_excerpt']);
                $best['source_excerpt'] = $trim((string)$best['source_excerpt']);

                $h = substr(md5(mb_strtolower($best['your_excerpt'])), 0, 16);
                if (!isset($seenYour[$h])) {
                    $seenYour[$h] = true;
                    $matches[] = $best;

                    $sid = $best['document_id'];
                    if (!isset($bySource[$sid]) || $best['percent'] > $bySource[$sid]['max_percent']) {
                        $bySource[$sid] = [
                            'document_id' => $sid,
                            'source_title'=> $best['source_title'],
                            'max_percent' => $best['percent'],
                            'sample_your' => $best['your_excerpt'],
                            'sample_src'  => $best['source_excerpt'],
                        ];
                    }
                }
            }
        }

        usort($matches, fn($a,$b)=>$b['percent'] <=> $a['percent']);
        $matches = array_slice($matches, 0, self::RETURN_TOP_MATCHES);

        return [
            'score'     => round($overall, 2),
            'matches'   => $matches,
            'aggregate' => array_values($bySource),
            'meta'      => [
                'window'     => self::WINDOW_WORDS,
                'stride'     => self::STRIDE_WORDS,
                'ngram'      => self::NGRAM_N,
                'candidates' => count($cands),
            ],
        ];
    }

    /** ---------- Similarity ---------- */

    private function combinedSimilarity(array $a, array $b): float
    {
        $cos = $this->cosineTfidf($a['tf'], $b['tf'], $this->idf);
        $jac = $this->jaccardFiltered($a['ngrams'], $b['ngrams'], $this->commonNgrams);
        
        if (self::USE_WEIGHTED) {
            $combined = (self::COS_W * $cos) + (self::JAC_W * $jac);
        } else {
            $combined = max($cos, $jac);
        }
        
        // Apply minimum threshold
        return $combined < self::MIN_SIMILARITY ? 0.0 : $combined;
    }

    private function cosineTfidf(array $va, array $vb, array $idf): float
    {
        $ma=0.0; foreach ($va as $k=>$tf){ $w=$idf[$k]??1.0; $wt=$tf*$w; $ma += $wt*$wt; }
        $mb=0.0; foreach ($vb as $k=>$tf){ $w=$idf[$k]??1.0; $wt=$tf*$w; $mb += $wt*$wt; }
        $den = sqrt($ma)*sqrt($mb); if ($den<=0) return 0.0;

        $dot=0.0;
        $small = count($va) < count($vb) ? $va : $vb;
        foreach ($small as $k=>$_){
            if (isset($va[$k], $vb[$k])){
                $w = $idf[$k] ?? 1.0;
                $dot += ($va[$k]*$w) * ($vb[$k]*$w);
            }
        }
        $c = $dot/$den;
        return $c<0?0.0:($c>1?1.0:$c);
    }

    private function jaccardFiltered(array $A, array $B, array $commonFlag): float
    {
        if (empty($A) || empty($B)) return 0.0;
        
        $fa=[]; foreach($A as $g){ if(!isset($commonFlag[$g])) $fa[]=$g; }
        $fb=[]; foreach($B as $g){ if(!isset($commonFlag[$g])) $fb[]=$g; }
        if (empty($fa) || empty($fb)) return 0.0;

        sort($fa); sort($fb);
        $i=$j=0; $inter=0; $union=0;
        while($i<count($fa)&&$j<count($fb)){
            if($fa[$i]===$fb[$j]){ $inter++; $union++; $i++; $j++; }
            elseif($fa[$i]<$fb[$j]){ $union++; $i++; }
            else { $union++; $j++; }
        }
        $union += (count($fa)-$i)+(count($fb)-$j);
        return $union ? $inter/$union : 0.0;
    }

    /** ---------- Quality Checking ---------- */

    private function isQualityMatch(array $match): bool
    {
        // Skip very short matches
        if (mb_strlen($match['your_excerpt']) < 60) return false;
        
        // Skip matches that are mostly common academic phrases
        $excerptLower = mb_strtolower($match['your_excerpt']);
        foreach (self::$COMMON_PHRASES as $phrase) {
            if (str_contains($excerptLower, $phrase)) {
                // If more than 30% of the match is common phrases, skip it
                $phraseLength = mb_strlen($phrase);
                $excerptLength = mb_strlen($excerptLower);
                if ($phraseLength > $excerptLength * 0.3) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /** ---------- Chunking ---------- */

    private function makeChunks(string $text): array
    {
        $words = $this->splitWords($text);
        $n = count($words);
        if ($n < self::MIN_CHUNK_WORDS) return [];

        $chunks = [];
        for ($i=0; $i<$n; $i+=self::STRIDE_WORDS){
            $slice = array_slice($words, $i, self::WINDOW_WORDS);
            if (count($slice) < self::MIN_CHUNK_WORDS) break;

            $chunkText = implode(' ', $slice);

            $normTokens = $this->normalizeTokens($slice);
            $tf  = $this->termFreq($normTokens);
            $ngr = $this->ngrams($slice, self::NGRAM_N);

            $chunks[] = [
                'text'   => $chunkText,
                'tf'     => $tf,
                'ngrams' => $ngr,
            ];
        }
        return $chunks;
    }

    private function splitWords(string $s): array
    {
        $s = preg_replace('/\s+/u', ' ', trim($s));
        if ($s==='') return [];
        preg_match_all('/[\p{L}\p{M}\p{N}’\'-]+/u', $s, $m);
        return array_values(array_filter($m[0] ?? []));
    }

    private function normalizeTokens(array $tokens): array
    {
        $stem = $this->getStemmer();
        $out=[];
        
        // Keep important academic terms
        $academicTerms = ['method', 'result', 'analysis', 'study', 'research', 'data', 
                         'experiment', 'hypothesis', 'conclusion', 'finding', 'theory'];
        
        foreach($tokens as $t){
            $t = mb_strtolower($t,'UTF-8');
            $isAcademic = in_array($t, $academicTerms);
            
            // Less aggressive punctuation removal
            $t = preg_replace('/[^\p{L}\p{M}\p{N}\'-]+/u','',$t);
            
            if ($t==='' || (isset(self::$STOP[$t]) && !$isAcademic)) continue;
            if ($stem) $t = $stem($t);
            $out[] = $t;
        }
        return $out;
    }

    private function ngrams(array $tokens, int $n): array
    {
        $N=count($tokens); if ($N<$n) return [];
        $grams=[];
        for($i=0;$i<=$N-$n;$i++){
            $g=[];
            for($k=0;$k<$n;$k++){
                $w = mb_strtolower($tokens[$i+$k],'UTF-8');
                $w = preg_replace('/[^\p{L}\p{M}\p{N}’\'-]+/u','',$w);
                $g[]=$w;
            }
            $grams[] = implode(' ',$g);
        }
        return array_values(array_unique($grams));
    }

    private function termFreq(array $tokens): array
    {
        $f=[]; foreach($tokens as $t){ $f[$t]=($f[$t]??0)+1; } return $f;
    }

    /** ---------- Candidates ---------- */

    private function candidateChunks(Document $document): array
    {
        $latestRp = ResearchPaper::query()->max('updated_at');
        $rpStamp  = $latestRp ? (string)$latestRp : 'none';

        $cacheKey = 'plag:candidates:v4:title:' . $document->title_id . ':rp:' . $rpStamp;

        return $this->rememberSafe($cacheKey, self::CACHE_MINUTES, function () use ($document) {
            $out = [];

            // --- Titles (final documents) ---
            $titles = Title::query()
                ->where('id', '!=', $document->title_id)
                ->where('status', 'submitted')
                ->whereNotNull('final_document_id')
                ->with(['finalDocument:id,title_id,chapter,content'])
                ->get(['id','title','final_document_id']);

            foreach ($titles as $t) {
                $final = $t->finalDocument ?: Document::find($t->final_document_id);
                if (!$final || empty($final->content)) continue;

                $src = $this->stripBoilerplate($this->htmlToCleanText($final->content));
                $chunks = $this->makeChunks($src);
                if (self::MAX_CHUNKS_PER_SOURCE > 0 && count($chunks) > self::MAX_CHUNKS_PER_SOURCE) {
                    $chunks = array_slice($chunks, 0, self::MAX_CHUNKS_PER_SOURCE);
                }
                foreach ($chunks as $c) {
                    $excerpt = mb_strlen($c['text']) > self::MAX_EXCERPT_CHARS
                        ? (mb_substr($c['text'], 0, self::MAX_EXCERPT_CHARS) . '…')
                        : $c['text'];

                    $out[] = [
                        'document_id'    => $final->id,
                        'source_title'   => $t->title ?? 'Untitled',
                        'source_chapter' => $final->chapter ?? 'Final',
                        'text'           => $excerpt,
                        'tf'             => $c['tf'],
                        'ngrams'         => $c['ngrams'],
                    ];
                }
            }

            // --- PDFs (ResearchPaper) ---
            $papers = ResearchPaper::query()
                ->whereNotNull('extracted_text')
                ->whereRaw("TRIM(extracted_text) <> ''")
                ->latest('updated_at')
                ->limit(self::MAX_PDFS)
                ->get(['id','title','year','authors','extracted_text']);

            foreach ($papers as $rp) {
                $txt = $this->stripBoilerplate((string)$rp->extracted_text);
                if ($txt === '') continue;

                $label = trim(
                    ($rp->title ?? 'Untitled')
                    . (isset($rp->year) ? " ({$rp->year})" : '')
                    . (isset($rp->authors) && $rp->authors !== '' ? " — {$rp->authors}" : '')
                );

                $chunks = $this->makeChunks($txt);
                if (self::MAX_CHUNKS_PER_SOURCE > 0 && count($chunks) > self::MAX_CHUNKS_PER_SOURCE) {
                    $chunks = array_slice($chunks, 0, self::MAX_CHUNKS_PER_SOURCE);
                }
                foreach ($chunks as $c) {
                    $excerpt = mb_strlen($c['text']) > self::MAX_EXCERPT_CHARS
                        ? (mb_substr($c['text'], 0, self::MAX_EXCERPT_CHARS) . '…')
                        : $c['text'];

                    $out[] = [
                        'document_id'    => 'RP:' . $rp->id,
                        'source_title'   => $label,
                        'source_chapter' => 'PDF',
                        'text'           => $excerpt,
                        'tf'             => $c['tf'],
                        'ngrams'         => $c['ngrams'],
                    ];
                }
            }

            return $out;
        });
    }

    /** ---------- Cleaning & Boilerplate ---------- */

    public function htmlToCleanText(string $htmlOrText): string
    {
        $s = preg_replace('/<img[^>]+src="data:image\/[^"]+"[^>]*>/i','',$htmlOrText);
        $s = preg_replace('/<div class="ck[^"]*"[^>]*>.*?<\/div>/si','',$s);
        $s = strip_tags($s);
        $s = html_entity_decode($s, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        $s = preg_replace('/\s+/u',' ',$s);
        return trim($s);
    }

    public function stripBoilerplate(string $text): string
    {
        $t = $this->startFromBody($text);

        // Less aggressive reference detection - only cut if near the end
        $refPatterns = [
            '/\b(?:references|bibliography|works\s+cited)\b/i',
            '/\b(?:appendix|appendices)\b/i',
            '/\b(?:acknowledg?ments?)\b/i',
        ];
        
        foreach ($refPatterns as $rx) {
            if (preg_match($rx, $t, $m, PREG_OFFSET_CAPTURE)) {
                $pos = $m[0][1];
                // Only cut if reference section is in last 30% of text
                if ($pos > mb_strlen($t) * 0.7) {
                    $t = trim(mb_substr($t, 0, $pos));
                    break;
                }
            }
        }

        // Less aggressive citation removal
        $t = preg_replace('/\(([A-Z][A-Za-z\'-]+)(?:\s*,\s*\d{4})?\)/u', ' ', $t);
        
        // Only remove numeric reference ranges, not single citations
        $t = preg_replace('/\[\s*\d+(?:\s*[-,]\s*\d+)+\s*\]/u', ' ', $t);
        
        // Remove URLs & DOIs
        $t = preg_replace('#https?://\S+#i', ' ', $t);
        $t = preg_replace('/\b10\.\d{4,9}\/[-._;()\/:A-Za-z0-9]+\b/', ' ', $t);

        return preg_replace('/\s+/u', ' ', trim($t));
    }

    private function startFromBody(string $text): string
    {
        $t = ltrim($text);
        if (preg_match('/\bchapter\s*(?:1|i|one)\b/iu', $t, $m, PREG_OFFSET_CAPTURE)) {
            return ltrim(mb_substr($t, $m[0][1]));
        }
        if (preg_match('/\bintroduction\b/iu', $t, $m2, PREG_OFFSET_CAPTURE)) {
            $pos = $m2[0][1];
            if ($pos < (int)(mb_strlen($t)*0.25)) return ltrim(mb_substr($t, $pos));
        }
        return $t;
    }

    /** ---------- Corpus Stats ---------- */

    private function ensureCorpusStats(): void
    {
        if (!empty($this->idf)) return;

        $latestRp = ResearchPaper::query()->max('updated_at');
        $rpStamp  = $latestRp ? (string)$latestRp : 'none';

        $stats = $this->rememberSafe('plag:stats:v3:rp:' . $rpStamp, self::CACHE_MINUTES, function () {
            $docCount = 0;
            $tokenDF  = [];
            $ngDF     = [];

            // --- Titles (submitted finals) ---
            $titles = Title::query()
                ->whereNotNull('final_document_id')
                ->where('status','submitted')
                ->with(['finalDocument:id,title_id,content'])
                ->get(['id','final_document_id']);

            foreach ($titles as $t) {
                $final = $t->finalDocument;
                if (!$final || empty($final->content)) continue;

                $docCount++;
                $txt   = $this->stripBoilerplate($this->htmlToCleanText($final->content));
                $words = $this->splitWords($txt);

                $tokens  = array_unique($this->normalizeTokens($words));
                $ngrams5 = array_unique($this->ngrams($words, self::NGRAM_N));

                foreach ($tokens as $tok) { $tokenDF[$tok] = ($tokenDF[$tok] ?? 0) + 1; }
                foreach ($ngrams5 as $g) { $ngDF[$g]      = ($ngDF[$g] ?? 0) + 1; }
            }

            // --- ResearchPaper PDFs ---
            $papers = ResearchPaper::query()
                ->whereNotNull('extracted_text')
                ->whereRaw("TRIM(extracted_text) <> ''")
                ->latest('updated_at')
                ->limit(self::MAX_PDFS)
                ->get(['id','extracted_text']);

            foreach ($papers as $rp) {
                $docCount++;
                $txt   = $this->stripBoilerplate((string)$rp->extracted_text);
                $words = $this->splitWords($txt);

                $tokens  = array_unique($this->normalizeTokens($words));
                $ngrams5 = array_unique($this->ngrams($words, self::NGRAM_N));

                foreach ($tokens as $tok) { $tokenDF[$tok] = ($tokenDF[$tok] ?? 0) + 1; }
                foreach ($ngrams5 as $g) { $ngDF[$g]      = ($ngDF[$g] ?? 0) + 1; }
            }

            $idf = [];
            $N   = max(1, $docCount);
            foreach ($tokenDF as $tok => $df) {
                $idf[$tok] = log((1 + $N) / (1 + $df)) + 1.0;
            }

            $common  = [];
            $hardMin = 3;
            $ratio   = 0.35;  // reduced from 0.40
            $minDF   = max($hardMin, (int)ceil($N * $ratio));
            foreach ($ngDF as $g => $df) {
                if ($df >= $minDF) $common[$g] = true;
            }

            return ['idf' => $idf, 'common' => $common];
        });

        $this->idf          = $stats['idf'] ?? [];
        $this->commonNgrams = $stats['common'] ?? [];
    }

    /** ---------- Utility Methods ---------- */

    private function rememberSafe(string $key, int $minutes, \Closure $compute)
    {
        $driver = config('cache.default');
        if ($driver === 'database') {
            return $compute();
        }
        try {
            return Cache::remember($key, now()->addMinutes($minutes), $compute);
        } catch (\Throwable $e) {
            return $compute();
        }
    }

    private function getStemmer(): ?\Closure
    {
        if (class_exists(\Wamania\Snowball\English::class)) {
            $stem = new \Wamania\Snowball\English();
            return fn(string $w) => $stem->stem($w);
        }
        return null;
    }

    /** ---------- Test Method ---------- */
    public function testPlagiarismSettings(string $sampleText, Document $document): array
    {
        $result = $this->detailedMatches($sampleText, $document);
        
        return [
            'total_matches' => count($result['matches']),
            'max_score' => $result['score'],
            'candidates_checked' => $result['meta']['candidates'],
            'match_samples' => array_slice($result['matches'], 0, 3),
            'settings' => [
                'window_size' => self::WINDOW_WORDS,
                'min_similarity' => self::MIN_SIMILARITY,
                'quality_filtering' => 'enabled'
            ]
        ];
    }
}