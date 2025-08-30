<?php

namespace App\Http\Controllers;

use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class TitleVerificationController extends Controller
{
    // ===== Tunables (you can tweak) =====
    private const MAX_LIKE_TOKENS       = 8;     // prefilter tokens (rare first)
    private const SIM_PASS_THRESHOLD    = 0.30;  // 30% -> approve if below
    private const CORE_MIN_RESULTS      = 10;    // fall back to scan more if few hits
    private const IDF_CACHE_MINUTES     = 360;   // 6 hours
    private const WEB_CACHE_MINUTES     = 60;

    // Composite weights
    private const W_COSINE = 0.60;
    private const W_BIGRAM = 0.30;
    private const W_RARE   = 0.10;

    // Domain buckets to reduce false positives when topics diverge
    private static array $DOMAIN_BUCKETS = [
        'research' => ['research','scholar','scholarly','academic','originality','authorship','ownership','plagiarism','detection','similarity','citation','paper','thesis','dissertation'],
        'land'     => ['land','real','estate','realestate','property','parcel','cadastre','cadastral','deed','title','registry','lot','zoning','survey'],
        'health'   => ['patient','hospital','health','clinic','medical','diagnosis','disease','treatment'],
        'finance'  => ['bank','loan','credit','financial','portfolio','trading','investment'],
        'edu'      => ['student','teacher','school','classroom','learning','curriculum'],
    ];

    // Additional stopwords / boilerplate that often inflate scores
    private static array $EXTRA_STOP = [
        'powered','using','based','driven','ai','smart','digital','online','web','mobile','management','monitoring','system','application','framework','platform','model','method','prototype','solution'
    ];

    // Canonical replacements / synonym flattening (tiny map; expand as needed)
    private static array $CANON = [
        'ai-driven' => 'ai', 'a.i.' => 'ai', 'artificial' => 'ai', 'intelligence' => 'ai',
        'real-estate' => 'realestate', 'e-government' => 'egov', 'e-gov' => 'egov',
        'detection' => 'detect', 'management' => 'manage', 'ownership' => 'owner',
        'plagiarism' => 'plagiar', 'land-title' => 'title',
        'blockchain-powered' => 'blockchain',
    ];

    // ================== MAIN: Internal DB similarity ==================
    public function checkTitleSimilarity(Request $request)
    {
        $inputTitle     = trim((string) $request->input('title', ''));
        $excludeTitleId = $request->input('exclude_title_id'); // optional
        $limit          = (int) ($request->input('limit', 10) ?: 10);

        if ($inputTitle === '') {
            return response()->json([
                'max_similarity' => 0,
                'approved'       => true,
                'results'        => [],
            ]);
        }

        // Build/cached IDF from Titles corpus
        [$idf, $docCount] = $this->getIdfFromCorpus();

        // Tokenize & choose rare/informative tokens for DB prefilter
        $allTokens   = self::tokenize($inputTitle);
        $ranked      = $this->rankTokensByIdf($allTokens, $idf);
        $likeTokens  = array_slice($ranked, 0, self::MAX_LIKE_TOKENS);

        // Prefilter likely matches (OR LIKE on rare tokens)
        $q = Title::query()
            ->when($excludeTitleId, fn($qq) => $qq->where('id', '!=', $excludeTitleId))
            ->when(!empty($likeTokens), function ($qq) use ($likeTokens) {
                $qq->where(function ($qb) use ($likeTokens) {
                    foreach ($likeTokens as $t) {
                        $qb->orWhere('title', 'like', '%' . $t . '%');
                    }
                });
            })
            ->select('id','title','authors','submitted_at')
            ->limit(200); // safety cap

        $rows = $q->get();

        // If too few prefilter hits, widen search
        if ($rows->count() < self::CORE_MIN_RESULTS) {
            $rows = Title::query()
                ->when($excludeTitleId, fn($qq) => $qq->where('id', '!=', $excludeTitleId))
                ->select('id','title','authors','submitted_at')
                ->limit(500)
                ->get();
        }

        $results = [];
        $inputNorm = self::normalize($inputTitle);

        foreach ($rows as $row) {
            $cand = (string) $row->title;
            if ($cand === '') continue;

            $candNorm = self::normalize($cand);

            // Composite score in [0..1]
            $score = $this->compositeSimilarity($inputNorm, $candNorm, $idf);

            // Explain bits (optional; helps tuning)
            [$cosSim, $bigJ, $rareOverlap] = $this->explainComponents($inputNorm, $candNorm, $idf);

            // Keep plausible matches only (cheap cut)
            if ($score < 0.15 && ($cosSim < 0.15 || $bigJ < 0.08)) continue;

            $authors = $this->splitAuthors((string)($row->authors ?? ''));
            $year    = $this->extractYear($row->submitted_at);

            $results[] = [
                'id'          => (int)$row->id,
                'title'       => $cand,
                'authors'     => $authors,
                'year'        => $year,
                'similarity'  => round($score * 100, 2),
                'components'  => [
                    'cosine_tf_idf' => round($cosSim * 100, 2),
                    'bigram_jaccard'=> round($bigJ * 100, 2),
                    'rare_overlap'  => round($rareOverlap * 100, 2),
                ],
            ];
        }

        // Sort by similarity, tie-break by cosine component
        usort($results, function ($a, $b) {
            if ($a['similarity'] === $b['similarity']) {
                return ($b['components']['cosine_tf_idf'] ?? 0) <=> ($a['components']['cosine_tf_idf'] ?? 0);
            }
            return $b['similarity'] <=> $a['similarity'];
        });

        $results = array_slice($results, 0, $limit);
        $max = $results[0]['similarity'] ?? 0;

        return response()->json([
            'max_similarity' => $max,
            'approved'       => $max < (self::SIM_PASS_THRESHOLD * 100),
            'results'        => $results,
        ]);
    }

    // ================== WEB CHECK (OpenAlex + Crossref) ==================
    public function checkWebTitleSimilarity(Request $request)
    {
        $inputTitle = trim((string) $request->input('title', ''));
        if ($inputTitle === '') {
            return response()->json([
                'max_similarity' => 0,
                'approved'       => true,
                'results'        => [],
            ]);
        }

        $cacheKey = 'web_sim_v2:' . md5($inputTitle);
        if (Cache::has($cacheKey) && (int)$request->input('attempt', 1) === 1) {
            return response()->json(Cache::get($cacheKey));
        }

        [$idf, $_] = $this->getIdfFromCorpus();
        $normInput = self::normalize($inputTitle);

        $openAlex  = $this->queryOpenAlex($inputTitle);
        $crossref  = $this->queryCrossref($inputTitle);

        $merged = array_merge($openAlex, $crossref);
        $merged = array_values(array_filter($merged, fn($r) => !empty($r['title'])));

        // Unique by normalized title
        $seen = []; $unique = [];
        foreach ($merged as $r) {
            $k = mb_strtolower(trim($r['title']));
            if (isset($seen[$k])) continue;
            $seen[$k] = true;

            $candNorm = self::normalize($r['title']);
            $score    = $this->compositeSimilarity($normInput, $candNorm, $idf);
            [$cosSim, $bigJ, $rareOverlap] = $this->explainComponents($normInput, $candNorm, $idf);

            $r['similarity'] = round($score * 100, 2);
            $r['components'] = [
                'cosine_tf_idf' => round($cosSim * 100, 2),
                'bigram_jaccard'=> round($bigJ * 100, 2),
                'rare_overlap'  => round($rareOverlap * 100, 2),
            ];
            $unique[] = $r;
        }

        usort($unique, fn($a,$b) => $b['similarity'] <=> $a['similarity']);

        $max = $unique[0]['similarity'] ?? 0;
        $out = [
            'max_similarity' => $max,
            'approved'       => $max < (self::SIM_PASS_THRESHOLD * 100),
            'results'        => $unique,
        ];

        if (!empty($unique)) {
            Cache::put($cacheKey, $out, now()->addMinutes(self::WEB_CACHE_MINUTES));
        }

        return response()->json($out);
    }

    // ================== Helper: OpenAlex ==================
    private function queryOpenAlex(string $query): array
    {
        try {
            $res = Http::timeout(12)->get('https://api.openalex.org/works', [
                'search'   => $query,
                'per_page' => 5,
            ]);
            if (!$res->ok()) return [];

            $out = [];
            foreach ($res->json('results', []) as $w) {
                $title = (string)($w['title'] ?? '');
                if ($title === '') continue;

                $year  = $w['publication_year'] ?? ($w['from_publication_date'] ?? null);
                $auths = [];
                foreach (($w['authorships'] ?? []) as $au) {
                    $name = $au['author']['display_name'] ?? null;
                    if ($name) $auths[] = $name;
                }

                // Best available link
                $link = $w['primary_location']['landing_page_url']
                    ?? $w['primary_location']['pdf_url']
                    ?? $w['primary_location']['source']['host_organization_url']
                    ?? $w['primary_location']['source']['homepage_url']
                    ?? ($w['doi'] ?? null);

                if ($link && str_starts_with($link, '10.')) {
                    $link = 'https://doi.org/' . $link;
                }

                $out[] = [
                    'source'  => 'openalex',
                    'title'   => $title,
                    'authors' => $auths,
                    'year'    => $year ? (int)$year : null,
                    'link'    => $link,
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ================== Helper: Crossref ==================
    private function queryCrossref(string $query): array
    {
        try {
            $res = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'ChainScholar/1.0 (mailto:youremail@example.com)'])
                ->get('https://api.crossref.org/works', [
                    'query.title' => $query,
                    'rows'        => 5,
                    'select'      => 'title,author,issued,URL,DOI',
                ]);

            if (!$res->ok()) return [];

            $out = [];
            foreach ($res->json('message.items', []) as $it) {
                $titleArr = $it['title'] ?? [];
                $title    = is_array($titleArr) ? (string)($titleArr[0] ?? '') : (string)$titleArr;
                if ($title === '') continue;

                $auths = [];
                foreach (($it['author'] ?? []) as $au) {
                    $parts = [];
                    if (!empty($au['given']))  $parts[] = $au['given'];
                    if (!empty($au['family'])) $parts[] = $au['family'];
                    if ($parts) $auths[] = implode(' ', $parts);
                }

                $year = null;
                if (!empty($it['issued']['date-parts'][0][0])) {
                    $year = (int)$it['issued']['date-parts'][0][0];
                }

                $link = $it['URL'] ?? null;
                if (empty($link) && !empty($it['DOI'])) {
                    $link = 'https://doi.org/' . $it['DOI'];
                }

                $out[] = [
                    'source'  => 'crossref',
                    'title'   => $title,
                    'authors' => $auths,
                    'year'    => $year,
                    'link'    => $link,
                ];
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ================== Scoring (Composite) ==================
    private function compositeSimilarity(string $aNorm, string $bNorm, array $idf): float
    {
        $tokensA = self::tokenize($aNorm);
        $tokensB = self::tokenize($bNorm);

        // Weighted cosine (TF-IDF)
        $cos = $this->cosineTfIdf($tokensA, $tokensB, $idf);

        // Bigram Jaccard
        $bigramsA = $this->ngrams($tokensA, 2);
        $bigramsB = $this->ngrams($tokensB, 2);
        $bigJ = $this->jaccard($bigramsA, $bigramsB);

        // Rare keyword overlap (top-K rare tokens)
        $rareA = $this->topRareTokens($tokensA, $idf, 5);
        $rareB = $this->topRareTokens($tokensB, $idf, 5);
        $rareOverlap = $this->overlapRatio($rareA, $rareB);

        $score = self::W_COSINE * $cos + self::W_BIGRAM * $bigJ + self::W_RARE * $rareOverlap;

        // Domain penalty: if dominant domains differ -> penalize
        $domA = $this->dominantDomain($tokensA);
        $domB = $this->dominantDomain($tokensB);
        if ($domA && $domB && $domA !== $domB) {
            $score *= 0.75; // 25% penalty for divergent topics
        }

        return max(0.0, min(1.0, $score));
    }

    private function explainComponents(string $aNorm, string $bNorm, array $idf): array
    {
        $A = self::tokenize($aNorm);
        $B = self::tokenize($bNorm);
        $cos = $this->cosineTfIdf($A, $B, $idf);
        $big = $this->jaccard($this->ngrams($A,2), $this->ngrams($B,2));
        $rareOverlap = $this->overlapRatio(
            $this->topRareTokens($A,$idf,5),
            $this->topRareTokens($B,$idf,5)
        );
        return [$cos, $big, $rareOverlap];
    }

    // ================== Tokenization / Normalization ==================
    private static function normalize(string $text): string
    {
        $t = mb_strtolower($text, 'UTF-8');
        $t = str_replace(['-', '—', '–', '/'], ' ', $t);
        // Apply small canonical map
        foreach (self::$CANON as $k => $v) {
            $t = str_replace($k, $v, $t);
        }
        // Remove non-letter/number
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $t);
        // Collapse spaces
        $t = preg_replace('/\s+/u', ' ', $t);
        return trim($t);
    }

    private static function tokenize(string $text): array
    {
        $t = self::normalize($text);
        if ($t === '') return [];

        // Base stopwords + extra boilerplate
        static $BASE_STOP = null;
        if ($BASE_STOP === null) {
            $generic = [
                'a','an','and','are','as','at','be','by','for','from','has','he','in','is','it','its',
                'of','on','that','the','to','was','were','will','with','using'
            ];
            $academic = [
                'study','research','paper','report','project','capstone','case','review','investigation',
                'analysis','approach','effect','impact','model','method','methods','design','development',
                'evaluation','implementation','framework','prototype','solution','tool','tools','technology',
                'process','assessment','system','application','platform'
            ];
            $BASE_STOP = array_fill_keys(array_unique(array_merge($generic, $academic, self::$EXTRA_STOP)), true);
        }

        $words = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
        $out = [];
        foreach ($words as $w) {
            if (isset($BASE_STOP[$w])) continue;
            if (mb_strlen($w, 'UTF-8') <= 2) continue;
            if (preg_match('/^\d+$/', $w)) continue;
            $out[] = $w;
        }
        return $out;
    }

    // ================== IDF & Ranking ==================
    private function getIdfFromCorpus(): array
    {
        return Cache::remember('title_idf_v2', now()->addMinutes(self::IDF_CACHE_MINUTES), function () {
            $df = []; $N = 0;

            Title::select('id','title')->chunkById(500, function ($chunk) use (&$df, &$N) {
                foreach ($chunk as $row) {
                    $N++;
                    $tokens = array_unique(self::tokenize((string)$row->title));
                    foreach ($tokens as $tok) {
                        $df[$tok] = ($df[$tok] ?? 0) + 1;
                    }
                }
            });

            // Smooth IDF: idf = ln((N+1)/(df+1)) + 1
            $idf = [];
            if ($N === 0) return [[], 0];
            foreach ($df as $tok => $d) {
                $idf[$tok] = log(($N + 1) / ($d + 1)) + 1.0;
            }
            return [$idf, $N];
        });
    }

    private function rankTokensByIdf(array $tokens, array $idf): array
    {
        $uniq = array_values(array_unique($tokens));
        usort($uniq, function ($a, $b) use ($idf) {
            $wa = ($idf[$a] ?? 1.0) * max(3, mb_strlen($a));
            $wb = ($idf[$b] ?? 1.0) * max(3, mb_strlen($b));
            return $wb <=> $wa;
        });
        return $uniq;
    }

    private function topRareTokens(array $tokens, array $idf, int $k = 5): array
    {
        $ranked = $this->rankTokensByIdf($tokens, $idf);
        return array_slice($ranked, 0, $k);
    }

    // ================== Similarity primitives ==================
    private function cosineTfIdf(array $A, array $B, array $idf): float
    {
        if (!$A || !$B) return 0.0;

        $tfA = []; foreach ($A as $t) $tfA[$t] = ($tfA[$t] ?? 0) + 1;
        $tfB = []; foreach ($B as $t) $tfB[$t] = ($tfB[$t] ?? 0) + 1;

        $dot = 0.0; $magA = 0.0; $magB = 0.0;

        foreach ($tfA as $t => $f) {
            $w = ($idf[$t] ?? 1.0);
            $magA += ($f * $w) * ($f * $w);
        }
        foreach ($tfB as $t => $f) {
            $w = ($idf[$t] ?? 1.0);
            $magB += ($f * $w) * ($f * $w);
        }

        $keys = array_intersect(array_keys($tfA), array_keys($tfB));
        foreach ($keys as $t) {
            $w = ($idf[$t] ?? 1.0);
            $dot += ($tfA[$t] * $w) * ($tfB[$t] * $w);
        }

        $den = sqrt($magA) * sqrt($magB);
        return ($den == 0.0) ? 0.0 : $dot / $den;
    }

    private function ngrams(array $tokens, int $n): array
    {
        $out = [];
        $len = count($tokens);
        for ($i = 0; $i <= $len - $n; $i++) {
            $slice = array_slice($tokens, $i, $n);
            $out[] = implode(' ', $slice);
        }
        return array_values(array_unique($out));
    }

    private function jaccard(array $A, array $B): float
    {
        if (!$A || !$B) return 0.0;
        $setA = array_fill_keys($A, true);
        $setB = array_fill_keys($B, true);
        $inter = array_intersect_key($setA, $setB);
        $union = $setA + $setB;
        return count($union) ? (count($inter) / count($union)) : 0.0;
    }

    private function overlapRatio(array $A, array $B): float
    {
        if (!$A || !$B) return 0.0;
        $setA = array_fill_keys($A, true);
        $setB = array_fill_keys($B, true);
        $inter = array_intersect_key($setA, $setB);
        $minBase = max(1, min(count($setA), count($setB)));
        return count($inter) / $minBase; // 1.0 if all smaller set overlaps
    }

    // Domain detection (very light)
    private function dominantDomain(array $tokens): ?string
    {
        $scores = [];
        foreach (self::$DOMAIN_BUCKETS as $name => $words) {
            $score = 0;
            $set = array_fill_keys($words, true);
            foreach ($tokens as $t) if (isset($set[$t])) $score++;
            $scores[$name] = $score;
        }
        arsort($scores);
        $top = array_key_first($scores);
        if (!$top) return null;
        return ($scores[$top] >= 2) ? $top : null; // require at least 2 hits to be confident
    }

    // ================== Utilities ==================
    private function splitAuthors(string $authorsRaw): array
    {
        $authors = array_values(array_filter(preg_split('/[;,]+/', $authorsRaw) ?: []));
        return array_map('trim', $authors);
    }

    private function extractYear($submitted_at): ?int
    {
        if (empty($submitted_at)) return null;
        try { return (int) \Carbon\Carbon::parse($submitted_at)->year; } catch (\Throwable $e) { return null; }
    }

    // (kept for compatibility with your other code; not used now)
    private static function termFreqMap($tokens)
    {
        $freq = [];
        foreach ($tokens as $t) $freq[$t] = ($freq[$t] ?? 0) + 1;
        return $freq;
    }
}
