<?php

namespace App\Services;

use App\Models\ResearchPaper;
use Illuminate\Support\Facades\Cache;

/**
 * Conservative PDF Plagiarism Service
 * - Uses SAME conservative algorithm as PlagiarismService
 * - Only searches against ResearchPaper PDFs
 */
class PdfPlagiarismService
{
    /** -------- SAME Conservative Tunables as PlagiarismService -------- */
    private const WINDOW_WORDS        = 25;
    private const STRIDE_WORDS        = 15;
    private const MIN_CHUNK_WORDS     = 20;
    private const NGRAM_N             = 8;
    private const RETURN_TOP_MATCHES  = 10;
    private const SCORE_SCALE         = 100.0;
    private const CACHE_MINUTES       = 10;
    private const MIN_SIMILARITY      = 0.25;
    private const MIN_MATCH_LENGTH    = 100;

    private const USE_WEIGHTED = true;
    private const COS_W        = 0.8;
    private const JAC_W        = 0.2;

    private static array $STOP = [
        'a'=>1,'an'=>1,'the'=>1,'and'=>1,'or'=>1,'but'=>1,'if'=>1,'while'=>1,'at'=>1,'by'=>1,'for'=>1,'with'=>1,'about'=>1,'against'=>1,'between'=>1,'into'=>1,'through'=>1,'during'=>1,'before'=>1,'after'=>1,'above'=>1,'below'=>1,'to'=>1,'from'=>1,'up'=>1,'down'=>1,'in'=>1,'out'=>1,'on'=>1,'off'=>1,'over'=>1,'under'=>1,'again'=>1,'further'=>1,'then'=>1,'once'=>1,'here'=>1,'there'=>1,'when'=>1,'where'=>1,'why'=>1,'how'=>1,'all'=>1,'any'=>1,'both'=>1,'each'=>1,'few'=>1,'more'=>1,'most'=>1,'other'=>1,'some'=>1,'such'=>1,'no'=>1,'nor'=>1,'not'=>1,'only'=>1,'own'=>1,'same'=>1,'so'=>1,'than'=>1,'too'=>1,'very'=>1,'can'=>1,'will'=>1,'just'=>1,'don'=>1,'should'=>1,'now'=>1,'is'=>1,'am'=>1,'are'=>1,'was'=>1,'were'=>1,'be'=>1,'been'=>1,'being'=>1,'of'=>1,'as'=>1,'it'=>1,'its'=>1,'this'=>1,'that'=>1,'these'=>1,'those'=>1,'which'=>1,'who'=>1,'whom'=>1,'what'=>1,'via'=>1,
        'however'=>1,'therefore'=>1,'moreover'=>1,'furthermore'=>1,'consequently'=>1,'nevertheless'=>1,'thus'=>1,'hence'=>1,'accordingly'=>1,'meanwhile'=>1,'additionally'=>1,'likewise'=>1,'otherwise'=>1,'instead'=>1,'similarly'=>1,'indeed'=>1,'certainly'=>1,'probably'=>1,'perhaps'=>1,'maybe'=>1,'almost'=>1,'quite'=>1,'rather'=>1,'much'=>1,'many'=>1,'several'=>1,'various'=>1,'different'=>1,'important'=>1,'significant'=>1,'major'=>1,'minor'=>1,'higher'=>1,'lower'=>1,'better'=>1,'worse'=>1,'large'=>1,'small'=>1,'high'=>1,'low'=>1,'great'=>1,'good'=>1,'bad'=>1,'new'=>1,'old'=>1,'first'=>1,'last'=>1,'next'=>1,'previous'=>1,'current'=>1,'recent'=>1,'early'=>1,'late'=>1,'long'=>1,'short'=>1,
    ];

    private static array $COMMON_PHRASES = [
        'this study shows that', 'in this paper we', 'the results indicate that', 'as shown in table', 'it can be seen that', 'in conclusion we can say', 'the purpose of this', 'this research examines', 'the data suggest that', 'previous research has', 'literature review shows', 'methodology section describes', 'findings of this study', 'limitations of this study', 'future research should', 'according to the results', 'the analysis reveals that', 'in summary we can say', 'the main objective is', 'as previously mentioned', 'based on the findings', 'the study found that', 'research has shown that', 'it is important to note', 'the results show that', 'the data indicate that', 'this suggests that', 'it was found that', 'the author concludes that', 'this paper presents', 'the aim of this study', 'the objective of this research', 'this chapter discusses', 'the following section describes', 'as can be seen from', 'figure one shows', 'table two presents', 'the graph illustrates', 'the chart demonstrates', 'statistical analysis shows', 'significant difference was', 'no significant difference', 'correlation was found', 'regression analysis showed', 'anova results indicated', 'the hypothesis was', 'null hypothesis was', 'alternative hypothesis was', 'confidence interval was', 'standard deviation was', 'mean value was', 'median value was', 'standard error was', 'p value was', 'r squared value', 'the sample size was', 'participants were asked', 'subjects completed the', 'materials and methods', 'procedure was followed', 'experiment was conducted', 'survey was administered', 'questionnaire was used', 'interview was conducted', 'data was collected', 'data were analyzed', 'results are presented', 'discussion of results', 'implications of findings', 'recommendations for practice', 'suggestions for future', 'contribution to knowledge', 'theoretical implications', 'practical implications', 'study limitations include', 'strengths of this study', 'weaknesses of this study', 'further research is needed', 'additional studies should', 'in future research', 'subsequent investigations', 'later studies may', 'research questions were', 'research objectives were', 'the problem statement', 'background of the study', 'significance of the study', 'scope and limitations', 'definition of terms', 'theoretical framework', 'conceptual framework', 'review of literature', 'summary of literature', 'gaps in literature', 'research methodology', 'research design', 'data collection methods', 'data analysis methods', 'ethical considerations', 'informed consent was', 'approval was obtained', 'the institution review board', 'protection of human subjects'
    ];

    private array $idf = [];
    private array $commonNgrams = [];

    private const MAX_PDFS                  = 300;
    private const MAX_CHUNKS_PER_SOURCE     = 50;
    private const MAX_EXCERPT_CHARS         = 300;

    public function quickScoreFromText(string $plainText): float
    {
        $clean = $this->stripBoilerplate($plainText);
        $your  = $this->makeChunks($clean);

        $cands = $this->candidateChunksCorpus();
        if (empty($your) || empty($cands)) return 0.0;

        $this->ensureCorpusStats();

        $max = 0.0;
        foreach ($your as $yc) {
            foreach ($cands as $cc) {
                $cc = $this->enrichCandidate($cc);
                $sim = $this->combinedSimilarity($yc, $cc);
                if ($sim > $max) $max = $sim;
            }
        }
        
        $score = $max * self::SCORE_SCALE;
        return $score < 10 ? 0.0 : round($score, 2);
    }

    public function detailedMatchesFromText(string $plainText, int $minPercent = 0): array
    {
        $txt    = $this->stripBoilerplate($plainText);
        $your   = $this->makeChunks($txt);
        $cands  = $this->candidateChunksCorpus();
        $minSim = max($minPercent / self::SCORE_SCALE, self::MIN_SIMILARITY);

        $this->ensureCorpusStats();

        $matches = [];
        $overall = 0.0;
        $seenYour = [];

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
            
            if ($best && $best['percent'] >= 25) {
                $h = substr(md5(mb_strtolower($best['your_excerpt'])), 0, 16);
                if (!isset($seenYour[$h])) {
                    $seenYour[$h] = true;
                    $matches[] = $best;
                }
            }
        }

        usort($matches, fn($a,$b)=>$b['percent'] <=> $a['percent']);
        $matches = array_slice($matches, 0, self::RETURN_TOP_MATCHES);

        $finalScore = $overall >= 25 ? round($overall, 2) : 0.0;

        return [
            'score'     => $finalScore,
            'matches'   => $matches,
            'meta'      => [
                'window'     => self::WINDOW_WORDS,
                'stride'     => self::STRIDE_WORDS,
                'ngram'      => self::NGRAM_N,
                'candidates' => count($cands),
                'note'       => 'Same conservative algorithm as document checker'
            ],
        ];
    }

    private function combinedSimilarity(array $a, array $b): float
    {
        $cos = $this->cosineTfidf($a['tf'], $b['tf'], $this->idf);
        $jac = $this->jaccardFiltered($a['ngrams'], $b['ngrams'], $this->commonNgrams);
        
        $combined = (self::COS_W * $cos) + (self::JAC_W * $jac);
        
        if ($combined < self::MIN_SIMILARITY) return 0.0;
        if ($jac > 0.8 && $cos < 0.3) return 0.0;
        
        return min($combined, 0.95);
    }

    private function cosineTfidf(array $va, array $vb, array $idf): float
    {
        if (count($va) < 5 || count($vb) < 5) return 0.0;
        
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
        if (empty($A) || empty($B) || count($A) < 3 || count($B) < 3) return 0.0;
        
        $fa=[]; foreach($A as $g){ if(!isset($commonFlag[$g])) $fa[]=$g; }
        $fb=[]; foreach($B as $g){ if(!isset($commonFlag[$g])) $fb[]=$g; }
        
        if (empty($fa) || empty($fb)) return 0.0;

        $inter = count(array_intersect($fa, $fb));
        $union = count($fa) + count($fb) - $inter;
        
        return $union > 0 ? ($inter / $union) : 0.0;
    }

    private function isSubstantialMatch(string $textA, string $textB): bool
    {
        if (mb_strlen($textA) < self::MIN_MATCH_LENGTH || mb_strlen($textB) < self::MIN_MATCH_LENGTH) {
            return false;
        }

        $uniqueWordsA = $this->countUniqueWords($textA);
        $uniqueWordsB = $this->countUniqueWords($textB);
        
        if ($uniqueWordsA < 8 || $uniqueWordsB < 8) return false;

        return true;
    }

    private function isCommonPhrase(string $text): bool
    {
        $textLower = mb_strtolower(trim($text));
        
        foreach (self::$COMMON_PHRASES as $phrase) {
            if (str_contains($textLower, $phrase)) {
                $phraseRatio = mb_strlen($phrase) / mb_strlen($textLower);
                if ($phraseRatio > 0.4) {
                    return true;
                }
            }
        }
        
        $wordCount = str_word_count($text);
        $stopwordCount = 0;
        $words = str_word_count($text, 1);
        foreach ($words as $word) {
            if (isset(self::$STOP[strtolower($word)])) {
                $stopwordCount++;
            }
        }
        
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

    private function candidateChunksCorpus(): array
    {
        $ver = $this->corpusVersion();
        $cacheKey = "plag:candidates:pdf:conservative:{$ver}";
        $store = $this->cacheStore();

        $cached = $store->get($cacheKey);
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $out = [];

        $papers = ResearchPaper::query()
            ->whereNotNull('extracted_text')
            ->where('created_at', '>', now()->subMonths(24))
            ->latest('updated_at')
            ->limit(self::MAX_PDFS)
            ->get(['id', 'title', 'year', 'authors', 'extracted_text']);

        foreach ($papers as $p) {
            $src = $this->stripBoilerplate((string) $p->extracted_text);
            $chunks = $this->makeChunks($src);
            
            $chunks = array_slice($chunks, 0, self::MAX_CHUNKS_PER_SOURCE);
            
            foreach ($chunks as $c) {
                $excerpt = mb_strlen($c['text']) > self::MAX_EXCERPT_CHARS
                    ? (mb_substr($c['text'], 0, self::MAX_EXCERPT_CHARS) . '…')
                    : $c['text'];

                $out[] = [
                    'document_id'    => $p->id,
                    'source_title'   => $this->formatPaperTitle($p),
                    'source_chapter' => 'PDF',
                    'source_type'    => 'ResearchPaper',
                    'text'           => $excerpt,
                ];
            }
        }

        $store->put($cacheKey, $out, now()->addMinutes(self::CACHE_MINUTES * 2));
        return $out;
    }

    private function formatPaperTitle($paper): string
    {
        $title = $paper->title ?? 'Untitled';
        $year = $paper->year ? " ({$paper->year})" : '';
        $authors = $paper->authors ? " — {$paper->authors}" : '';
        
        return trim($title . $year . $authors);
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

    private function ensureCorpusStats(): void
    {
        if (!empty($this->idf)) return;

        $store = $this->cacheStore();
        $ver = $this->corpusVersion();
        $statsKey = "plag:stats:pdf:conservative:{$ver}";

        $cached = $store->get($statsKey);
        if (is_array($cached) && isset($cached['idf'], $cached['common'])) {
            $this->idf = $cached['idf'];
            $this->commonNgrams = $cached['common'];
            return;
        }

        $texts = [];
        $papers = ResearchPaper::query()
            ->whereNotNull('extracted_text')
            ->where('created_at', '>', now()->subMonths(24))
            ->limit(100)
            ->get(['id', 'extracted_text']);

        foreach ($papers as $p) {
            $txt = $this->stripBoilerplate((string)$p->extracted_text);
            if ($txt) $texts[] = $txt;
        }

        $docCount = 0; $tokenDF = []; $ngDF = [];
        foreach ($texts as $txt) {
            $docCount++;
            $words   = $this->splitWords($txt);
            $tokens  = array_unique($this->normalizeTokens($words));
            $ngrams5 = array_unique($this->ngrams($words, self::NGRAM_N));

            foreach ($tokens as $tok) { $tokenDF[$tok] = ($tokenDF[$tok] ?? 0) + 1; }
            foreach ($ngrams5 as $g) { $ngDF[$g] = ($ngDF[$g] ?? 0) + 1; }
        }

        $idf = []; $N = max(1, $docCount);
        foreach ($tokenDF as $tok => $df) {
            $idf[$tok] = log((1 + $N) / (1 + $df)) + 1.0;
        }

        $common = [];
        $minDF = max(2, (int)ceil($N * 0.3));
        foreach ($ngDF as $g => $df) {
            if ($df >= $minDF) $common[$g] = true;
        }

        $stats = ['idf' => $idf, 'common' => $common];
        $store->put($statsKey, $stats, now()->addMinutes(self::CACHE_MINUTES * 2));

        $this->idf = $idf;
        $this->commonNgrams = $common;
    }

    private function cacheStore()
    {
        $default = config('cache.default');
        if (in_array($default, ['array', 'database'], true)) {
            return Cache::store('file');
        }
        return Cache::store($default);
    }

    private function enrichCandidate(array $c): array
    {
        if (isset($c['tf']) && isset($c['ngrams'])) return $c;

        $words = $this->splitWords($c['text'] ?? '');
        $c['tf'] = $this->termFreq($this->normalizeTokens($words));
        $c['ngrams'] = $this->ngrams($words, self::NGRAM_N);
        return $c;
    }

    private function corpusVersion(): string
    {
        $pMaxRaw = ResearchPaper::query()
            ->whereNotNull('extracted_text')
            ->max('updated_at');
        $pCnt = ResearchPaper::query()
            ->whereNotNull('extracted_text')
            ->count();

        return "conservative:p{$pCnt}-" . ($pMaxRaw ? \Illuminate\Support\Carbon::parse($pMaxRaw)->timestamp : 0);
    }

    public function testPdfConservatism(string $sampleText): array
    {
        $result = $this->detailedMatchesFromText($sampleText);
        
        return [
            'total_matches' => count($result['matches']),
            'max_score' => $result['score'],
            'candidates_checked' => $result['meta']['candidates'],
            'settings_note' => 'CONSERVATIVE PDF: Same algorithm as document checker',
            'match_samples' => array_slice($result['matches'], 0, 3)
        ];
    }
}