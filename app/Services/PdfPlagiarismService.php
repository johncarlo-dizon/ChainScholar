<?php

namespace App\Services;

use App\Models\ResearchPaper;
use Illuminate\Support\Facades\Cache;

/**
 * Balanced PDF Plagiarism Service
 * - Good accuracy with reasonable speed for PDFs
 * - Balanced parameters for PDF-only comparison
 */
class PdfPlagiarismService
{
    /** -------- Balanced Tunables -------- */
    private const WINDOW_WORDS        = 30;
    private const STRIDE_WORDS        = 20;
    private const MIN_CHUNK_WORDS     = 15;
    private const NGRAM_N             = 6;
    private const RETURN_TOP_MATCHES  = 15;
    private const SCORE_SCALE         = 100.0;
    private const CACHE_MINUTES       = 30;
    private const MIN_SIMILARITY      = 0.20;

    private const USE_WEIGHTED = true;
    private const COS_W        = 0.7;
    private const JAC_W        = 0.3;

    // Stopwords and common phrases (same as before)
    private static array $STOP = [
        'a'=>1,'an'=>1,'the'=>1,'and'=>1,'or'=>1,'but'=>1,'if'=>1,'while'=>1,'at'=>1,'by'=>1,'for'=>1,'with'=>1,'about'=>1,'against'=>1,'between'=>1,'into'=>1,'through'=>1,'during'=>1,'before'=>1,'after'=>1,'above'=>1,'below'=>1,'to'=>1,'from'=>1,'up'=>1,'down'=>1,'in'=>1,'out'=>1,'on'=>1,'off'=>1,'over'=>1,'under'=>1,'again'=>1,'further'=>1,'then'=>1,'once'=>1,'here'=>1,'there'=>1,'when'=>1,'where'=>1,'why'=>1,'how'=>1,'all'=>1,'any'=>1,'both'=>1,'each'=>1,'few'=>1,'more'=>1,'most'=>1,'other'=>1,'some'=>1,'such'=>1,'no'=>1,'nor'=>1,'not'=>1,'only'=>1,'own'=>1,'same'=>1,'so'=>1,'than'=>1,'too'=>1,'very'=>1,'can'=>1,'will'=>1,'just'=>1,'don'=>1,'should'=>1,'now'=>1,'is'=>1,'am'=>1,'are'=>1,'was'=>1,'were'=>1,'be'=>1,'been'=>1,'being'=>1,'of'=>1,'as'=>1,'it'=>1,'its'=>1,'this'=>1,'that'=>1,'these'=>1,'those'=>1,'which'=>1,'who'=>1,'whom'=>1,'what'=>1,'via'=>1,
        'however'=>1,'therefore'=>1,'moreover'=>1,'furthermore'=>1,'consequently'=>1,'nevertheless'=>1,'thus'=>1,'hence'=>1,'accordingly'=>1,'meanwhile'=>1,'additionally'=>1,'likewise'=>1,'otherwise'=>1,'instead'=>1,'similarly'=>1,'indeed'=>1,'certainly'=>1,'probably'=>1,'perhaps'=>1,'maybe'=>1,'almost'=>1,'quite'=>1,'rather'=>1,'much'=>1,'many'=>1,'several'=>1,'various'=>1,'different'=>1,'important'=>1,'significant'=>1,'major'=>1,'minor'=>1,'higher'=>1,'lower'=>1,'better'=>1,'worse'=>1,'large'=>1,'small'=>1,'high'=>1,'low'=>1,'great'=>1,'good'=>1,'bad'=>1,'new'=>1,'old'=>1,'first'=>1,'last'=>1,'next'=>1,'previous'=>1,'current'=>1,'recent'=>1,'early'=>1,'late'=>1,'long'=>1,'short'=>1,
    ];

    private static array $COMMON_PHRASES = [
        // ... (same common phrases as before)
    ];

    private array $idf = [];
    private array $commonNgrams = [];

    // Balanced limiters for PDF-only
    private const MAX_PDFS                  = 200;
    private const MAX_TOTAL_CHUNKS          = 800;
    private const MAX_EXCERPT_CHARS         = 250;

    // Balanced chunk limits for PDFs
    private const MAX_YOUR_CHUNKS_COMPARE   = 35;
    private const MAX_CAND_CHUNKS_COMPARE   = 60;
    private const MAX_CHUNKS_PER_PDF        = 12;
    private const MAX_CHUNKS_PER_DOCUMENT   = 35;

    /** ---------- Public API ---------- */

    public function quickScoreFromText(string $plainText): float
    {
        $cacheKey = 'plag:pdf:quick:' . md5($plainText);
        
        return Cache::remember($cacheKey, 5, function () use ($plainText) {
            $clean = $this->stripBoilerplate($plainText);
            $your  = $this->makeChunks($clean);

            $cands = $this->getCandidateChunksCorpus();
            if (empty($your) || empty($cands)) return 0.0;

            $this->ensureCorpusStats();

            $max = 0.0;
            $checked = 0;
            $maxChecks = 120; // Balanced limit for PDF quick score
            
            foreach ($your as $yc) {
                foreach ($cands as $cc) {
                    $cc = $this->enrichCandidate($cc);
                    $sim = $this->combinedSimilarity($yc, $cc);
                    if ($sim > $max) $max = $sim;
                    
                    $checked++;
                    if ($checked >= $maxChecks) break 2;
                }
            }
            
            $score = $max * self::SCORE_SCALE;
            return $score < 10 ? 0.0 : round($score, 2);
        });
    }

    public function detailedMatchesFromText(string $plainText, int $minPercent = 0): array
    {
        $startTime = microtime(true);
        
        $txt    = $this->stripBoilerplate($plainText);
        $your   = $this->makeChunks($txt);
        $cands  = $this->getCandidateChunksCorpus();
        $minSim = max($minPercent / self::SCORE_SCALE, self::MIN_SIMILARITY);

        $this->ensureCorpusStats();

        $matches = [];
        $overall = 0.0;
        $seenYour = [];

        // Balanced comparison limits for PDFs
        $maxYourChunks = min(count($your), self::MAX_YOUR_CHUNKS_COMPARE);
        $maxCandChunks = min(count($cands), self::MAX_CAND_CHUNKS_COMPARE);
        
        $your = array_slice($your, 0, $maxYourChunks);
        $cands = array_slice($cands, 0, $maxCandChunks);

        foreach ($your as $yc) {
            $best = null;
            foreach ($cands as $cc) {
                $cc = $this->enrichCandidate($cc);
                $sim = $this->combinedSimilarity($yc, $cc);
                $simPct = $sim * self::SCORE_SCALE;
                
                if ($sim < $minSim) continue;
                if (!$this->isSubstantialMatch($yc['text'], $cc['text'])) continue;
                if ($this->isCommonPhrase($yc['text'])) continue;
                
                if ($simPct > $overall) $overall = $simPct;

                $m = [
                    'percent'        => round($simPct, 2),
                    'your_excerpt'   => $yc['text'],
                    'source_excerpt' => $cc['text'],
                    'source_title'   => $cc['source_title'],
                    'source_chapter' => $cc['source_chapter'],
                    'document_id'    => $cc['document_id'],
                    'source_type'    => $cc['source_type'],
                ];
                
                if ($best === null || $m['percent'] > $best['percent']) $best = $m;
            }
            
            if ($best && $best['percent'] >= 20) {
                $h = substr(md5(mb_strtolower($best['your_excerpt'])), 0, 16);
                if (!isset($seenYour[$h])) {
                    $seenYour[$h] = true;
                    $matches[] = $best;
                }
            }
        }

        usort($matches, fn($a,$b)=>$b['percent'] <=> $a['percent']);
        $matches = array_slice($matches, 0, self::RETURN_TOP_MATCHES);

        $finalScore = $overall >= 20 ? round($overall, 2) : 0.0;

        return [
            'score'     => $finalScore,
            'matches'   => $matches,
            'meta'      => [
                'window' => self::WINDOW_WORDS,
                'stride' => self::STRIDE_WORDS,
                'ngram' => self::NGRAM_N,
                'candidates' => count($cands),
                'your_chunks_used' => $maxYourChunks,
                'cand_chunks_used' => $maxCandChunks,
                'processing_time' => round(microtime(true) - $startTime, 2),
                'balanced_mode' => true,
                'note' => 'PDF-only balanced algorithm'
            ],
        ];
    }

    /** ---------- Balanced Candidate Management (PDF-only) ---------- */

    private function getCandidateChunksCorpus(): array
    {
        $cacheKey = 'plag:pdf:candidates:balanced:v2';
        
        return Cache::remember($cacheKey, self::CACHE_MINUTES, function () {
            $chunks = [];

            // Get PDFs with balanced sampling
            $papers = ResearchPaper::query()
                ->whereNotNull('extracted_text')
                ->whereRaw("LENGTH(TRIM(extracted_text)) > 300") // Only substantial PDFs
                ->where('created_at', '>', now()->subMonths(24)) // Longer period for academic PDFs
                ->orderBy('created_at', 'desc')
                ->limit(self::MAX_PDFS)
                ->get(['id','title','year','authors','extracted_text']);

            foreach ($papers as $rp) {
                $txt = $this->stripBoilerplate((string)$rp->extracted_text);
                if (mb_strlen($txt) < 400) continue; // Skip short PDF texts

                $paperChunks = $this->makeChunks($txt);
                
                // Balanced sampling for PDFs
                if (count($paperChunks) > self::MAX_CHUNKS_PER_PDF) {
                    $paperChunks = $this->sampleChunksBalanced($paperChunks, self::MAX_CHUNKS_PER_PDF);
                }
                
                foreach ($paperChunks as $c) {
                    $chunks[] = [
                        'document_id'    => $rp->id,
                        'source_title'   => $this->formatPaperTitle($rp),
                        'source_chapter' => 'PDF',
                        'source_type'    => 'ResearchPaper',
                        'text'           => $this->trimExcerpt($c['text']),
                        // tf and ngrams computed on-demand via enrichCandidate
                    ];
                    
                    if (count($chunks) >= self::MAX_TOTAL_CHUNKS) break 2;
                }
            }

            return $chunks;
        });
    }

    private function sampleChunksBalanced(array $chunks, int $sampleSize): array
    {
        if (count($chunks) <= $sampleSize) {
            return $chunks;
        }
        
        $sampled = [];
        $totalChunks = count($chunks);
        
        // Take balanced samples from different parts of the PDF
        $step = max(1, floor($totalChunks / $sampleSize));
        
        // Always include beginning, middle, and end
        $keyPositions = [
            0, // beginning
            (int)($totalChunks * 0.33),
            (int)($totalChunks * 0.66),
            $totalChunks - 1 // end
        ];
        
        foreach ($keyPositions as $pos) {
            if ($pos < $totalChunks && !in_array($chunks[$pos], $sampled)) {
                $sampled[] = $chunks[$pos];
            }
        }
        
        // Fill remaining slots with stepped samples
        for ($i = 0; $i < $totalChunks && count($sampled) < $sampleSize; $i += $step) {
            if (!in_array($chunks[$i], $sampled)) {
                $sampled[] = $chunks[$i];
            }
        }
        
        return array_slice($sampled, 0, $sampleSize);
    }

    private function trimExcerpt(string $text): string
    {
        return mb_strlen($text) > self::MAX_EXCERPT_CHARS
            ? (mb_substr($text, 0, self::MAX_EXCERPT_CHARS) . '…')
            : $text;
    }

    private function formatPaperTitle($paper): string
    {
        $title = $paper->title ?? 'Untitled';
        $year = $paper->year ? " ({$paper->year})" : '';
        $authors = $paper->authors ? " — {$paper->authors}" : '';
        
        return trim($title . $year . $authors);
    }

    /** ---------- Similarity Methods (Same algorithm) ---------- */

    private function combinedSimilarity(array $a, array $b): float
    {
        if (count($a['tf']) < 3 || count($b['tf']) < 3) {
            return 0.0;
        }

        $cos = $this->cosineTfidf($a['tf'], $b['tf'], $this->idf);
        $jac = $this->jaccardFiltered($a['ngrams'], $b['ngrams'], $this->commonNgrams);
        
        $combined = (self::COS_W * $cos) + (self::JAC_W * $jac);
        
        if ($combined < self::MIN_SIMILARITY) return 0.0;
        if ($jac > 0.7 && $cos < 0.2) return 0.0;
        
        return min($combined, 0.95);
    }

    private function cosineTfidf(array $va, array $vb, array $idf): float
    {
        if (count($va) < 3 || count($vb) < 3) return 0.0;
        
        $ma=0.0; $mb=0.0; $dot=0.0;
        
        foreach ($va as $k=>$tf){ 
            $w=$idf[$k]??0.1;
            $wt=$tf*$w; 
            $ma += $wt*$wt; 
        }
        foreach ($vb as $k=>$tf){ 
            $w=$idf[$k]??0.1; 
            $wt=$tf*$w; 
            $mb += $wt*$wt; 
        }
        
        $den = sqrt($ma)*sqrt($mb); 
        if ($den <= 0.001) return 0.0;

        foreach ($va as $k=>$v){
            if (isset($vb[$k])){
                $w = $idf[$k] ?? 0.1;
                $dot += ($v*$w) * ($vb[$k]*$w);
            }
        }
        
        $c = $dot/$den;
        return max(0.0, min(1.0, $c));
    }

    private function jaccardFiltered(array $A, array $B, array $commonFlag): float
    {
        if (empty($A) || empty($B) || count($A) < 2 || count($B) < 2) return 0.0;
        
        $fa=[]; foreach($A as $g){ if(!isset($commonFlag[$g])) $fa[]=$g; }
        $fb=[]; foreach($B as $g){ if(!isset($commonFlag[$g])) $fb[]=$g; }
        
        if (empty($fa) || empty($fb)) return 0.0;

        $inter = count(array_intersect($fa, $fb));
        $union = count($fa) + count($fb) - $inter;
        
        return $union > 0 ? ($inter / $union) : 0.0;
    }

    private function isSubstantialMatch(string $textA, string $textB): bool
    {
        return mb_strlen($textA) >= 80 && mb_strlen($textB) >= 80;
    }

    private function isCommonPhrase(string $text): bool
    {
        $textLower = mb_strtolower(trim($text));
        
        foreach (self::$COMMON_PHRASES as $phrase) {
            if (str_contains($textLower, $phrase)) {
                $phraseRatio = mb_strlen($phrase) / mb_strlen($textLower);
                if ($phraseRatio > 0.5) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /** ---------- Balanced Chunking ---------- */

    private function makeChunks(string $text): array
    {
        $words = $this->splitWords($text);
        $n = count($words);
        if ($n < self::MIN_CHUNK_WORDS) return [];

        $chunks = [];
        
        for ($i=0; $i<$n && count($chunks) < self::MAX_CHUNKS_PER_DOCUMENT; $i+=self::STRIDE_WORDS){
            $slice = array_slice($words, $i, self::WINDOW_WORDS);
            if (count($slice) < self::MIN_CHUNK_WORDS) break;

            $chunkText = implode(' ', $slice);
            
            if ($this->isCommonPhrase($chunkText)) continue;

            $normTokens = $this->normalizeTokens($slice);
            if (count($normTokens) < 5) continue;

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
        $out=[];
        foreach($tokens as $t){
            $t = mb_strtolower($t,'UTF-8');
            $t = preg_replace('/[^\p{L}\p{M}\p{N}]+/u','',$t);
            if ($t==='' || isset(self::$STOP[$t])) continue;
            $out[] = $t;
        }
        return $out;
    }

    private function ngrams(array $tokens, int $n): array
    {
        $N=count($tokens); 
        if ($N<$n) return [];
        
        $grams=[];
        $maxGrams = 20;
        
        for($i=0; $i<=$N-$n && count($grams) < $maxGrams; $i+=2){
            $g=[];
            for($k=0;$k<$n;$k++){
                $w = mb_strtolower($tokens[$i+$k],'UTF-8');
                $w = preg_replace('/[^\p{L}\p{M}\p{N}]+/u','',$w);
                if ($w === '' || isset(self::$STOP[$w])) {
                    continue 2;
                }
                $g[]=$w;
            }
            if (count($g) === $n) {
                $grams[] = implode(' ',$g);
            }
        }
        return array_values(array_unique($grams));
    }

    private function termFreq(array $tokens): array
    {
        $f=[]; 
        foreach($tokens as $t){ 
            if (!isset(self::$STOP[$t]) && mb_strlen($t) > 2) {
                $f[$t]=($f[$t]??0)+1; 
            }
        } 
        return $f;
    }

    /** ---------- Balanced Corpus Stats (PDF-only) ---------- */

    private function ensureCorpusStats(): void
    {
        if (!empty($this->idf)) return;

        $cacheKey = 'plag:pdf:stats:balanced:v2';
        
        $stats = Cache::remember($cacheKey, 60, function() {
            $docCount = 0;
            $tokenDF  = [];
            $ngDF     = [];

            // Balanced sample of PDFs for stats
            $papers = ResearchPaper::query()
                ->whereNotNull('extracted_text')
                ->whereRaw("LENGTH(TRIM(extracted_text)) > 800")
                ->where('created_at', '>', now()->subMonths(36)) // Longer period for PDF stats
                ->orderBy('created_at', 'desc')
                ->limit(50) // Balanced sample size
                ->get(['id','extracted_text']);

            foreach ($papers as $rp) {
                $docCount++;
                $txt   = $this->stripBoilerplate((string)$rp->extracted_text);
                $words = $this->splitWords($txt);

                $tokens  = array_unique($this->normalizeTokens($words));
                $ngrams5 = array_unique($this->ngrams($words, 5));

                foreach ($tokens as $tok) { $tokenDF[$tok] = ($tokenDF[$tok] ?? 0) + 1; }
                foreach ($ngrams5 as $g) { $ngDF[$g]      = ($ngDF[$g] ?? 0) + 1; }
            }

            $idf = [];
            $N   = max(1, $docCount);
            foreach ($tokenDF as $tok => $df) {
                $idf[$tok] = log((1 + $N) / (1 + $df)) + 1.0;
            }

            $common  = [];
            $minDF   = max(2, (int)ceil($N * 0.25)); // Slightly lower for PDF diversity
            foreach ($ngDF as $g => $df) {
                if ($df >= $minDF) $common[$g] = true;
            }

            return ['idf' => $idf, 'common' => $common];
        });

        $this->idf          = $stats['idf'] ?? [];
        $this->commonNgrams = $stats['common'] ?? [];
    }

    /** ---------- Utility Methods ---------- */

    private function enrichCandidate(array $c): array
    {
        if (isset($c['tf']) && isset($c['ngrams'])) return $c;

        $words = $this->splitWords($c['text'] ?? '');
        $c['tf'] = $this->termFreq($this->normalizeTokens($words));
        $c['ngrams'] = $this->ngrams($words, self::NGRAM_N);
        return $c;
    }

    public function stripBoilerplate(string $text): string
    {
        $t = $text;

        if (preg_match('/\b(?:references|bibliography)\b/i', $t, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            if ($pos > mb_strlen($t) * 0.8) {
                $t = trim(mb_substr($t, 0, $pos));
            }
        }

        $t = preg_replace('#https?://\S+#i', ' ', $t);
        
        return preg_replace('/\s+/u', ' ', trim($t));
    }

    /** ---------- Test Method ---------- */
    public function testPdfPerformance(string $sampleText): array
    {
        $startTime = microtime(true);
        $result = $this->detailedMatchesFromText($sampleText);
        $processingTime = round(microtime(true) - $startTime, 2);
        
        return [
            'total_matches' => count($result['matches']),
            'max_score' => $result['score'],
            'candidates_checked' => $result['meta']['candidates'],
            'processing_time' => $processingTime,
            'settings_note' => 'BALANCED PDF: Good accuracy with reasonable speed',
            'performance' => $processingTime < 8 ? 'Good' : ($processingTime < 15 ? 'Acceptable' : 'Slow'),
            'match_samples' => array_slice($result['matches'], 0, 3)
        ];
    }
}