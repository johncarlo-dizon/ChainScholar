<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Title;
use Illuminate\Support\Facades\Cache;

/**
 * Robust plagiarism checker:
 * - Sliding windows over text
 * - TF-IDF cosine + 5-gram Jaccard (filtered common boilerplate)
 * - Boilerplate stripping (front matter, refs, URLs, DOIs, APA inline cites)
 * - De-duped matches and per-source aggregation (returned in payload)
 */
class PlagiarismService
{
    /** -------- Tunables -------- */
    private const WINDOW_WORDS        = 60;   // sliding window size
    private const STRIDE_WORDS        = 20;   // step between windows
    private const MIN_CHUNK_WORDS     = 12;   // ignore tiny slices
    private const NGRAM_N             = 5;    // 5-gram shingles for near-exact copy
    private const RETURN_TOP_MATCHES  = 30;   // cap cards shown
    private const SCORE_SCALE         = 100.0;
    private const CACHE_MINUTES       = 10;

    // If true, blend cosine & jaccard; if false, take max()
    private const USE_WEIGHTED = false;
    private const COS_W        = 0.6;
    private const JAC_W        = 0.4;

    // stopword list (compact)
    private static array $STOP = [
        'a'=>1,'an'=>1,'the'=>1,'and'=>1,'or'=>1,'but'=>1,'if'=>1,'while'=>1,'at'=>1,'by'=>1,'for'=>1,'with'=>1,'about'=>1,'against'=>1,'between'=>1,'into'=>1,'through'=>1,'during'=>1,'before'=>1,'after'=>1,'above'=>1,'below'=>1,'to'=>1,'from'=>1,'up'=>1,'down'=>1,'in'=>1,'out'=>1,'on'=>1,'off'=>1,'over'=>1,'under'=>1,'again'=>1,'further'=>1,'then'=>1,'once'=>1,'here'=>1,'there'=>1,'when'=>1,'where'=>1,'why'=>1,'how'=>1,'all'=>1,'any'=>1,'both'=>1,'each'=>1,'few'=>1,'more'=>1,'most'=>1,'other'=>1,'some'=>1,'such'=>1,'no'=>1,'nor'=>1,'not'=>1,'only'=>1,'own'=>1,'same'=>1,'so'=>1,'than'=>1,'too'=>1,'very'=>1,'can'=>1,'will'=>1,'just'=>1,'don'=>1,'should'=>1,'now'=>1,'is'=>1,'am'=>1,'are'=>1,'was'=>1,'were'=>1,'be'=>1,'been'=>1,'being'=>1,'of'=>1,'as'=>1,'it'=>1,'its'=>1,'this'=>1,'that'=>1,'these'=>1,'those'=>1,'which'=>1,'who'=>1,'whom'=>1,'what'=>1,'via'=>1
    ];

    /** Cached corpus stats */
    private array $idf = [];           // token => idf weight
    private array $commonNgrams = [];  // 5-grams flagged as boilerplate

    /** ---------- Public API ---------- */

    /** Quick overall score = max similarity across windows */
    public function quickScore(string $rawHtmlOrText, Document $document): float
    {
        $clean = $this->stripBoilerplate($this->htmlToCleanText($rawHtmlOrText));
        $your  = $this->makeChunks($clean);

        $cands = $this->candidateChunks($document);
        if (empty($your) || empty($cands)) return 0.0;

        $this->ensureCorpusStats(); // load IDF + common n-grams

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
        $txt    = $this->stripBoilerplate($this->htmlToCleanText($html));
        $your   = $this->makeChunks($txt);
        $cands  = $this->candidateChunks($document);
        $minSim = max(0, $minPercent);

        $this->ensureCorpusStats();

        $matches = [];
        $overall = 0.0;
        $seenYour = [];  // de-dup by your excerpt hash
        $bySource = [];  // aggregate max per source doc

        foreach ($your as $yc) {
            $best = null;
            foreach ($cands as $cc) {
                $simPct = $this->combinedSimilarity($yc, $cc) * self::SCORE_SCALE;
                if ($simPct > $overall) $overall = $simPct;
                if ($simPct < $minSim) continue;

                $m = [
                    'percent'        => round($simPct, 2),
                    'your_excerpt'   => $yc['text'],
                    'source_excerpt' => $cc['text'],
                    'source_title'   => $cc['source_title'],
                    'source_chapter' => $cc['source_chapter'],
                    'document_id'    => $cc['document_id'],
                ];
                if ($best === null || $m['percent'] > $best['percent']) $best = $m;
            }
            if ($best) {
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
            'aggregate' => array_values($bySource), // optional for UI
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
        if (self::USE_WEIGHTED) return (self::COS_W * $cos) + (self::JAC_W * $jac);
        return max($cos, $jac);
    }

    private function cosineTfidf(array $va, array $vb, array $idf): float
    {
        // compute weighted magnitudes
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
        // remove boilerplate grams that appear in many docs
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

            // normalized tokens (with optional stemming)
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
        $stem = $this->getStemmer(); // may be null
        $out=[];
        foreach($tokens as $t){
            $t = mb_strtolower($t,'UTF-8');
            $t = preg_replace('/[^\p{L}\p{M}\p{N}]+/u','',$t);
            if ($t==='' || isset(self::$STOP[$t])) continue;
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
        $cacheKey = 'plag:candidates:v2:title:'.$document->title_id;
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_MINUTES), function () use ($document) {
            $titles = Title::query()
                ->where('id','!=',$document->title_id)
                ->where('status','submitted')
                ->whereNotNull('final_document_id')
                ->with(['finalDocument:id,title_id,chapter,content'])
                ->get(['id','title','final_document_id']);

            $out=[];
            foreach($titles as $t){
                $final = $t->finalDocument ?: Document::find($t->final_document_id);
                if (!$final || empty($final->content)) continue;

                $src = $this->stripBoilerplate($this->htmlToCleanText($final->content));
                foreach($this->makeChunks($src) as $c){
                    $out[] = [
                        'document_id'    => $final->id,
                        'source_title'   => $t->title ?? 'Untitled',
                        'source_chapter' => $final->chapter ?? 'Final',
                        'text'           => $c['text'],
                        'tf'             => $c['tf'],
                        'ngrams'         => $c['ngrams'],
                    ];
                }
            }
            return $out;
        });
    }

    /** ---------- Cleaning & boilerplate ---------- */

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

        // Cut tail sections (References, Bibliography, Appendix, Acknowledgements…)
        foreach ([
            '/\b(?:references|bibliography|works\s+cited|appendix|appendices)\b/i',
            '/\b(?:acknowledg?ments?)\b/i',
        ] as $rx) {
            if (preg_match($rx, $t, $m, PREG_OFFSET_CAPTURE)) {
                $t = trim(mb_substr($t, 0, $m[0][1]));
                break;
            }
        }

        // Remove (Author, 2020) style citations
        $t = preg_replace('/\((?:[A-Z][A-Za-z\'-]+(?:\s*&\s*[A-Z][A-Za-z\'-]+)?(?:\s*,\s*\d{4})?(?:\s*;\s*)?)+\)/u',' ',$t);
        // Remove [1], [2-5], [12, 13]
        $t = preg_replace('/\[\s*\d+(?:\s*[-,]\s*\d+)*\s*\]/u',' ',$t);
        // Remove URLs & DOIs
        $t = preg_replace('#https?://\S+#i',' ',$t);
        $t = preg_replace('/\b10\.\d{4,9}\/[-._;()\/:A-Za-z0-9]+\b/',' ',$t);

        return preg_replace('/\s+/u',' ', trim($t));
    }

    private function startFromBody(string $text): string
    {
        $t = ltrim($text);
        if (preg_match('/\bchapter\s*(?:1|i|one)\b/iu',$t,$m, PREG_OFFSET_CAPTURE)) {
            return ltrim(mb_substr($t, $m[0][1]));
        }
        if (preg_match('/\bintroduction\b/iu',$t,$m2,PREG_OFFSET_CAPTURE)) {
            $pos = $m2[0][1];
            if ($pos < (int)(mb_strlen($t)*0.25)) return ltrim(mb_substr($t,$pos));
        }
        return $t;
    }

    /** ---------- Corpus stats (IDF + common 5-grams) ---------- */

    private function ensureCorpusStats(): void
    {
        if (!empty($this->idf)) return;

        $stats = Cache::remember('plag:stats:v1', now()->addMinutes(self::CACHE_MINUTES), function () {
            $titles = Title::query()
                ->whereNotNull('final_document_id')
                ->where('status','submitted')
                ->with(['finalDocument:id,title_id,content'])
                ->get(['id','final_document_id']);

            $docCount=0; $tokenDF=[]; $ngDF=[];
            foreach($titles as $t){
                $final = $t->finalDocument;
                if (!$final || empty($final->content)) continue;

                $docCount++;
                $txt = $this->stripBoilerplate($this->htmlToCleanText($final->content));
                $words = $this->splitWords($txt);

                $tokens  = array_unique($this->normalizeTokens($words));
                $ngrams5 = array_unique($this->ngrams($words, self::NGRAM_N));

                foreach($tokens as $tok){ $tokenDF[$tok]=($tokenDF[$tok]??0)+1; }
                foreach($ngrams5 as $g){ $ngDF[$g]=($ngDF[$g]??0)+1; }
            }

            $idf=[]; $N=max(1,$docCount);
            foreach($tokenDF as $tok=>$df){
                $idf[$tok] = log((1+$N)/(1+$df)) + 1.0; // smoothed
            }

            // Flag boilerplate 5-grams: in ≥3 docs or ≥40% of corpus
            $common=[]; $hardMin=3; $ratio=0.40;
            $minDF=max($hardMin, (int)ceil($N*$ratio));
            foreach($ngDF as $g=>$df){ if ($df >= $minDF) $common[$g]=true; }

            return ['idf'=>$idf, 'common'=>$common];
        });

        $this->idf = $stats['idf'] ?? [];
        $this->commonNgrams = $stats['common'] ?? [];
    }

    /** ---------- Optional stemming ---------- */
    private function getStemmer(): ?\Closure
    {
        // composer require wamania/snowball (optional)
        if (class_exists(\Wamania\Snowball\English::class)) {
            $stem = new \Wamania\Snowball\English();
            return fn(string $w) => $stem->stem($w);
        }
        return null;
    }
}
