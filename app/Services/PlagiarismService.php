<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Title;
use App\Models\ResearchPaper;
use Illuminate\Support\Facades\Cache;

/**
 * Reasonable plagiarism checker:
 * - Much higher thresholds
 * - Better common phrase filtering
 * - Focus on substantial, meaningful matches only
 */
class PlagiarismService
{
    /** -------- Conservative Tunables -------- */
    private const WINDOW_WORDS        = 25;   // Smaller windows
    private const STRIDE_WORDS        = 15;   // Less overlap
    private const MIN_CHUNK_WORDS     = 20;   // Ignore small chunks
    private const NGRAM_N             = 8;    // Much longer sequences
    private const RETURN_TOP_MATCHES  = 10;   // Fewer results
    private const SCORE_SCALE         = 100.0;
    private const CACHE_MINUTES       = 10;
    
    // Much higher minimum thresholds
    private const MIN_SIMILARITY      = 0.25; // 25% minimum (was 8%)
    private const MIN_MATCH_LENGTH    = 100;  // characters

    // Conservative weighting
    private const USE_WEIGHTED = true;
    private const COS_W        = 0.8;  // Heavy weight to semantic
    private const JAC_W        = 0.2;  // Light weight to exact matches

    // Expanded stopwords
    private static array $STOP = [
        'a'=>1,'an'=>1,'the'=>1,'and'=>1,'or'=>1,'but'=>1,'if'=>1,'while'=>1,'at'=>1,'by'=>1,
        'for'=>1,'with'=>1,'about'=>1,'against'=>1,'between'=>1,'into'=>1,'through'=>1,'during'=>1,
        'before'=>1,'after'=>1,'above'=>1,'below'=>1,'to'=>1,'from'=>1,'up'=>1,'down'=>1,'in'=>1,
        'out'=>1,'on'=>1,'off'=>1,'over'=>1,'under'=>1,'again'=>1,'further'=>1,'then'=>1,'once'=>1,
        'here'=>1,'there'=>1,'when'=>1,'where'=>1,'why'=>1,'how'=>1,'all'=>1,'any'=>1,'both'=>1,
        'each'=>1,'few'=>1,'more'=>1,'most'=>1,'other'=>1,'some'=>1,'such'=>1,'no'=>1,'nor'=>1,
        'not'=>1,'only'=>1,'own'=>1,'same'=>1,'so'=>1,'than'=>1,'too'=>1,'very'=>1,'can'=>1,
        'will'=>1,'just'=>1,'don'=>1,'should'=>1,'now'=>1,'is'=>1,'am'=>1,'are'=>1,'was'=>1,
        'were'=>1,'be'=>1,'been'=>1,'being'=>1,'of'=>1,'as'=>1,'it'=>1,'its'=>1,'this'=>1,
        'that'=>1,'these'=>1,'those'=>1,'which'=>1,'who'=>1,'whom'=>1,'what'=>1,'via'=>1,
        'however'=>1,'therefore'=>1,'moreover'=>1,'furthermore'=>1,'consequently'=>1,'nevertheless'=>1,
        'thus'=>1,'hence'=>1,'accordingly'=>1,'meanwhile'=>1,'additionally'=>1,'likewise'=>1,
        'otherwise'=>1,'instead'=>1,'similarly'=>1,'indeed'=>1,'certainly'=>1,'probably'=>1,
        'perhaps'=>1,'maybe'=>1,'almost'=>1,'quite'=>1,'rather'=>1,'very'=>1,'much'=>1,'many'=>1,
        'several'=>1,'various'=>1,'different'=>1,'important'=>1,'significant'=>1,'major'=>1,
        'minor'=>1,'higher'=>1,'lower'=>1,'better'=>1,'worse'=>1,'large'=>1,'small'=>1,'high'=>1,
        'low'=>1,'great'=>1,'good'=>1,'bad'=>1,'new'=>1,'old'=>1,'first'=>1,'last'=>1,'next'=>1,
        'previous'=>1,'current'=>1,'recent'=>1,'early'=>1,'late'=>1,'long'=>1,'short'=>1,'time'=>1,
        'times'=>1,'year'=>1,'years'=>1,'day'=>1,'days'=>1,'week'=>1,'weeks'=>1,'month'=>1,'months'=>1
    ];

    // Extensive common academic phrases
    private static array $COMMON_PHRASES = [
        'this study shows that', 'in this paper we', 'the results indicate that',
        'as shown in table', 'it can be seen that', 'in conclusion we can say',
        'the purpose of this', 'this research examines', 'the data suggest that',
        'previous research has', 'literature review shows', 'methodology section describes',
        'findings of this study', 'limitations of this study', 'future research should',
        'according to the results', 'the analysis reveals that', 'in summary we can say',
        'the main objective is', 'as previously mentioned', 'based on the findings',
        'the study found that', 'research has shown that', 'it is important to note',
        'the results show that', 'the data indicate that', 'this suggests that',
        'it was found that', 'the author concludes that', 'this paper presents',
        'the aim of this study', 'the objective of this research', 'this chapter discusses',
        'the following section describes', 'as can be seen from', 'figure one shows',
        'table two presents', 'the graph illustrates', 'the chart demonstrates',
        'statistical analysis shows', 'significant difference was', 'no significant difference',
        'correlation was found', 'regression analysis showed', 'anova results indicated',
        'the hypothesis was', 'null hypothesis was', 'alternative hypothesis was',
        'confidence interval was', 'standard deviation was', 'mean value was',
        'median value was', 'standard error was', 'p value was', 'r squared value',
        'the sample size was', 'participants were asked', 'subjects completed the',
        'materials and methods', 'procedure was followed', 'experiment was conducted',
        'survey was administered', 'questionnaire was used', 'interview was conducted',
        'data was collected', 'data were analyzed', 'results are presented',
        'discussion of results', 'implications of findings', 'recommendations for practice',
        'suggestions for future', 'contribution to knowledge', 'theoretical implications',
        'practical implications', 'study limitations include', 'strengths of this study',
        'weaknesses of this study', 'further research is needed', 'additional studies should',
        'in future research', 'subsequent investigations', 'later studies may',
        'research questions were', 'research objectives were', 'the problem statement',
        'background of the study', 'significance of the study', 'scope and limitations',
        'definition of terms', 'theoretical framework', 'conceptual framework',
        'review of literature', 'summary of literature', 'gaps in literature',
        'research methodology', 'research design', 'data collection methods',
        'data analysis methods', 'ethical considerations', 'informed consent was',
        'approval was obtained', 'the institution review board', 'protection of human subjects'
    ];

    /** Cached corpus stats */
    private array $idf = [];
    private array $commonNgrams = [];

    // Conservative limiters
    private const MAX_PDFS                  = 500;   // Reduced
    private const MAX_CHUNKS_PER_SOURCE     = 100;   // Much reduced
    private const MAX_EXCERPT_CHARS         = 300;   // Shorter excerpts

    /** ---------- Public API ---------- */

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
        
        // Apply conservative scaling
        $score = $max * self::SCORE_SCALE;
        return $score < 10 ? 0.0 : round($score, 2); // Ignore scores below 10%
    }

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
                    'note'       => 'No analyzable content.',
                ],
            ];
        }

        $cands  = $this->candidateChunks($document);
        $minSim = max($minPercent / self::SCORE_SCALE, self::MIN_SIMILARITY);

        $this->ensureCorpusStats();

        $matches = [];
        $overall = 0.0;
        $seenYour = [];

        foreach ($your as $yc) {
            $best = null;
            foreach ($cands as $cc) {
                $sim = $this->combinedSimilarity($yc, $cc);
                $simPct = $sim * self::SCORE_SCALE;
                
                // Apply multiple conservative filters
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
                ];
                
                if ($best === null || $m['percent'] > $best['percent']) $best = $m;
            }
            
            if ($best && $best['percent'] >= 25) { // Only keep substantial matches
                $h = substr(md5(mb_strtolower($best['your_excerpt'])), 0, 16);
                if (!isset($seenYour[$h])) {
                    $seenYour[$h] = true;
                    $matches[] = $best;
                }
            }
        }

        usort($matches, fn($a,$b)=>$b['percent'] <=> $a['percent']);
        $matches = array_slice($matches, 0, self::RETURN_TOP_MATCHES);

        // Only return overall score if it's substantial
        $finalScore = $overall >= 25 ? round($overall, 2) : 0.0;

        return [
            'score'     => $finalScore,
            'matches'   => $matches,
            'meta'      => [
                'window'     => self::WINDOW_WORDS,
                'stride'     => self::STRIDE_WORDS,
                'ngram'      => self::NGRAM_N,
                'candidates' => count($cands),
                'note'       => 'Conservative matching applied'
            ],
        ];
    }

    /** ---------- Conservative Similarity ---------- */

    private function combinedSimilarity(array $a, array $b): float
    {
        $cos = $this->cosineTfidf($a['tf'], $b['tf'], $this->idf);
        $jac = $this->jaccardFiltered($a['ngrams'], $b['ngrams'], $this->commonNgrams);
        
        // Very conservative combination
        $combined = (self::COS_W * $cos) + (self::JAC_W * $jac);
        
        // Apply multiple conservative filters
        if ($combined < self::MIN_SIMILARITY) return 0.0;
        if ($jac > 0.8 && $cos < 0.3) return 0.0; // Too exact, not semantic
        
        return min($combined, 0.95); // Cap at 95%
    }

    private function cosineTfidf(array $va, array $vb, array $idf): float
    {
        if (count($va) < 5 || count($vb) < 5) return 0.0; // Too short
        
        $ma=0.0; $mb=0.0; $dot=0.0;
        
        foreach ($va as $k=>$tf){ 
            $w=$idf[$k]??0.1; // Lower default weight
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
        if (empty($A) || empty($B) || count($A) < 3 || count($B) < 3) return 0.0;
        
        // Remove boilerplate and very common ngrams
        $fa=[]; foreach($A as $g){ if(!isset($commonFlag[$g])) $fa[]=$g; }
        $fb=[]; foreach($B as $g){ if(!isset($commonFlag[$g])) $fb[]=$g; }
        
        if (empty($fa) || empty($fb)) return 0.0;

        $inter = count(array_intersect($fa, $fb));
        $union = count($fa) + count($fb) - $inter;
        
        return $union > 0 ? ($inter / $union) : 0.0;
    }

    /** ---------- Conservative Quality Checking ---------- */

    private function isSubstantialMatch(string $textA, string $textB): bool
    {
        // Both texts must be reasonably long
        if (mb_strlen($textA) < self::MIN_MATCH_LENGTH || mb_strlen($textB) < self::MIN_MATCH_LENGTH) {
            return false;
        }

        // Check if it's mostly unique content (not common phrases)
        $uniqueWordsA = $this->countUniqueWords($textA);
        $uniqueWordsB = $this->countUniqueWords($textB);
        
        if ($uniqueWordsA < 10 || $uniqueWordsB < 10) return false;

        return true;
    }

    private function isCommonPhrase(string $text): bool
    {
        $textLower = mb_strtolower(trim($text));
        
        // Check against common academic phrases
        foreach (self::$COMMON_PHRASES as $phrase) {
            if (str_contains($textLower, $phrase)) {
                $phraseRatio = mb_strlen($phrase) / mb_strlen($textLower);
                if ($phraseRatio > 0.4) { // If 40% or more is common phrase
                    return true;
                }
            }
        }
        
        // Check if it's too generic
        $wordCount = str_word_count($text);
        $stopwordCount = 0;
        $words = str_word_count($text, 1);
        foreach ($words as $word) {
            if (isset(self::$STOP[strtolower($word)])) {
                $stopwordCount++;
            }
        }
        
        // If more than 60% stopwords, it's probably not substantial
        if ($wordCount > 0 && ($stopwordCount / $wordCount) > 0.6) {
            return true;
        }
        
        return false;
    }

    private function countUniqueWords(string $text): int
    {
        $words = str_word_count(mb_strtolower($text), 1);
        $unique = [];
        foreach ($words as $word) {
            if (!isset(self::$STOP[$word]) && strlen($word) > 2) {
                $unique[$word] = true;
            }
        }
        return count($unique);
    }

    /** ---------- Conservative Chunking ---------- */

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
            
            // Skip chunks that are too common
            if ($this->isCommonPhrase($chunkText)) continue;

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
        for($i=0;$i<=$N-$n;$i++){
            $g=[];
            for($k=0;$k<$n;$k++){
                $w = mb_strtolower($tokens[$i+$k],'UTF-8');
                $w = preg_replace('/[^\p{L}\p{M}\p{N}]+/u','',$w);
                if ($w === '' || isset(self::$STOP[$w])) {
                    continue 2; // Skip ngram if it contains stopwords
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

    /** ---------- Conservative Candidates ---------- */

    private function candidateChunks(Document $document): array
    {
        $latestRp = ResearchPaper::query()->max('updated_at');
        $rpStamp  = $latestRp ? (string)$latestRp : 'none';

        $cacheKey = 'plag:candidates:conservative:' . $document->title_id . ':rp:' . $rpStamp;

        return $this->rememberSafe($cacheKey, self::CACHE_MINUTES, function () use ($document) {
            $out = [];

            // Only get recent titles
            $titles = Title::query()
                ->where('id', '!=', $document->title_id)
                ->where('status', 'submitted')
                ->whereNotNull('final_document_id')
                ->where('created_at', '>', now()->subMonths(12)) // Only last year
                ->with(['finalDocument:id,title_id,chapter,content'])
                ->get(['id','title','final_document_id']);

            foreach ($titles as $t) {
                $final = $t->finalDocument ?: Document::find($t->final_document_id);
                if (!$final || empty($final->content)) continue;

                $src = $this->stripBoilerplate($this->htmlToCleanText($final->content));
                $chunks = $this->makeChunks($src);
                
                // Take fewer chunks
                $chunks = array_slice($chunks, 0, self::MAX_CHUNKS_PER_SOURCE);
                
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

            // Fewer PDFs
            $papers = ResearchPaper::query()
                ->whereNotNull('extracted_text')
                ->whereRaw("TRIM(extracted_text) <> ''")
                ->where('created_at', '>', now()->subMonths(24)) // Last 2 years only
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
                $chunks = array_slice($chunks, 0, self::MAX_CHUNKS_PER_SOURCE);
                
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

    /** ---------- Conservative Cleaning ---------- */

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
        $t = $text;

        // Only remove references if they're clearly at the end
        if (preg_match('/\b(?:references|bibliography)\b/i', $t, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1];
            if ($pos > mb_strlen($t) * 0.8) { // Only if in last 20%
                $t = trim(mb_substr($t, 0, $pos));
            }
        }

        // Keep citations - they're part of academic writing
        // Remove only obvious URL patterns
        $t = preg_replace('#https?://\S+#i', ' ', $t);
        
        return preg_replace('/\s+/u', ' ', trim($t));
    }

    /** ---------- Conservative Corpus Stats ---------- */

    private function ensureCorpusStats(): void
    {
        if (!empty($this->idf)) return;

        $latestRp = ResearchPaper::query()->max('updated_at');
        $rpStamp  = $latestRp ? (string)$latestRp : 'none';

        $stats = $this->rememberSafe('plag:stats:conservative:' . $rpStamp, self::CACHE_MINUTES, function () {
            $docCount = 0;
            $tokenDF  = [];
            $ngDF     = [];

            // Smaller sample for stats
            $titles = Title::query()
                ->whereNotNull('final_document_id')
                ->where('status','submitted')
                ->where('created_at', '>', now()->subMonths(12))
                ->with(['finalDocument:id,title_id,content'])
                ->limit(100)
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

            $papers = ResearchPaper::query()
                ->whereNotNull('extracted_text')
                ->whereRaw("TRIM(extracted_text) <> ''")
                ->where('created_at', '>', now()->subMonths(24))
                ->limit(50)
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
            $minDF   = max(2, (int)ceil($N * 0.3)); // Higher threshold
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
        try {
            return Cache::remember($key, now()->addMinutes($minutes), $compute);
        } catch (\Throwable $e) {
            return $compute();
        }
    }

    /** ---------- Test Method ---------- */
    public function testConservatism(string $sampleText, Document $document): array
    {
        $result = $this->detailedMatches($sampleText, $document);
        
        return [
            'total_matches' => count($result['matches']),
            'max_score' => $result['score'],
            'candidates_checked' => $result['meta']['candidates'],
            'settings_note' => 'VERY CONSERVATIVE: 25% minimum similarity, extensive filtering',
            'matches' => $result['matches']
        ];
    }
}