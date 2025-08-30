<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TitleVerificationController extends Controller
{
   
   
public function checkTitleSimilarity(Request $request)
{
    $inputTitle     = trim((string) $request->input('title', ''));
    $excludeTitleId = $request->input('exclude_title_id'); // optional
    $limit          = (int)($request->input('limit', 10));

    // Tunables — start slightly looser; tighten after confirming results
    $SIM_THRESHOLD       = 0.25; // keep if >= 25%
    $SOFT_THRESHOLD      = 0.18; // allow slightly lower if overlap strong
    $MIN_KEYWORD_OVERLAP = 1;    // shared tokens after stopwords
    $MAX_LIKE_TOKENS     = 6;    // number of tokens to OR-like in SQL

    if ($inputTitle === '') {
        return response()->json([
            'max_similarity' => 0,
            'approved'       => true,
            'results'        => [],
        ]);
    }

    // 1) Extract keywords from the input (longer first)
    $tokens = self::tokenize(mb_strtolower($inputTitle));
    usort($tokens, fn($a,$b) => strlen($b) <=> strlen($a));
    $likeTokens = array_slice(array_values(array_unique($tokens)), 0, $MAX_LIKE_TOKENS);

    // 2) Prefilter in DB (avoid scanning everything)
    $rows = Title::query()
        ->when($excludeTitleId, fn($q) => $q->where('id', '!=', $excludeTitleId))
        // If your data reliably uses these, you can re-enable:
        // ->when(auth()->check(), fn($q) => $q->where('owner_id', '!=', auth()->id()))
        // ->where('status', 'submitted')
        ->when(!empty($likeTokens), function ($q) use ($likeTokens) {
            $q->where(function ($qq) use ($likeTokens) {
                foreach ($likeTokens as $t) {
                    $qq->orWhere('title', 'like', "%{$t}%");
                }
            });
        })
        ->select('id', 'title', 'authors', 'submitted_at')
        ->get();

    // If LIKE prefilter yields nothing (short titles, etc.), fall back
    if ($rows->isEmpty()) {
        $rows = Title::query()
            ->when($excludeTitleId, fn($q) => $q->where('id', '!=', $excludeTitleId))
            ->select('id', 'title', 'authors', 'submitted_at')
            ->get();
    }

    // Helper: keyword overlap count
    $kwSet = array_fill_keys(self::tokenize($inputTitle), true);
    $overlapCount = function (string $candidate) use ($kwSet): int {
        $cand = self::tokenize($candidate);
        $cnt = 0; foreach ($cand as $w) if (isset($kwSet[$w])) $cnt++;
        return $cnt;
    };

    // 3) Score + filter
    $results = [];
    foreach ($rows as $row) {
        $candTitle = (string) $row->title;

        $sim = self::cosineSimilarity(
            mb_strtolower($inputTitle),
            mb_strtolower($candTitle)
        );
        $overlap = $overlapCount($candTitle);

        // Keep only relevant ones
        if ($sim < $SIM_THRESHOLD && !($sim >= $SOFT_THRESHOLD && $overlap >= $MIN_KEYWORD_OVERLAP)) {
            continue;
        }

        // authors stored like "Last, First; Last, First"
        $authorsRaw = (string)($row->authors ?? '');
        $authors = array_values(array_filter(preg_split('/[;,]+/', $authorsRaw) ?: []));
        $authors = array_map('trim', $authors);

        $year = null;
        if (!empty($row->submitted_at)) {
            try { $year = (int) \Carbon\Carbon::parse($row->submitted_at)->year; } catch (\Throwable $e) {}
        }

        $results[] = [
            'id'         => (int)$row->id,
            'title'      => $candTitle,
            'authors'    => $authors,
            'year'       => $year,
            'similarity' => round($sim * 100, 2),
            'overlap'    => $overlap,
        ];
    }

    // 4) Sort by similarity, then by overlap
    usort($results, function ($a, $b) {
        if ($a['similarity'] === $b['similarity']) {
            return $b['overlap'] <=> $a['overlap'];
        }
        return $b['similarity'] <=> $a['similarity'];
    });

    // 5) Trim + respond
    $results = array_slice($results, 0, $limit);
    $max = $results[0]['similarity'] ?? 0;

    return response()->json([
        'max_similarity' => $max,
        'approved'       => $max < 30, // your pass/fail rule
        'results'        => $results,
    ]);
}






    /**
     * POST /titles/check-web
     * Web similarity using Semantic Scholar.
     */
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

    $cacheKey = 'web_sim_openalex_crossref:' . md5($inputTitle);
    if (Cache::has($cacheKey) && (int)$request->input('attempt', 1) === 1) {
        return response()->json(Cache::get($cacheKey));
    }

    // ---- Fetch from OpenAlex ----
    // Docs: https://api.openalex.org/works?search=...
    $openAlex = [];
    try {
        $oaRes = Http::timeout(12)
            ->get('https://api.openalex.org/works', [
                'search' => $inputTitle,
                'per_page' => 5,
            ]);

        if ($oaRes->ok()) {
            $data = $oaRes->json('results', []);
            foreach ($data as $w) {
                $title  = (string)($w['title'] ?? '');
                if ($title === '') continue;

                $year   = $w['publication_year'] ?? ($w['from_publication_date'] ?? null);
                $auths  = [];
                foreach (($w['authorships'] ?? []) as $au) {
                    $name = $au['author']['display_name'] ?? null;
                    if ($name) $auths[] = $name;
                }

                // Prefer the best available URL
                $link = $w['primary_location']['source']['host_organization_url'] ?? null;
                $link = $w['primary_location']['landing_page_url'] ?? $link;
                $link = $w['primary_location']['pdf_url'] ?? $link;
                $link = $w['primary_location']['source']['homepage_url'] ?? $link;
                $link = $link ?: ($w['doi'] ?? null);
                if ($link && str_starts_with($link, '10.')) {
                    $link = 'https://doi.org/' . $link; // normalize DOIs
                }

                $sim = self::cosineSimilarity(mb_strtolower($inputTitle), mb_strtolower($title));

                $openAlex[] = [
                    'source'     => 'openalex',
                    'title'      => $title,
                    'authors'    => $auths,
                    'year'       => $year ? (int)$year : null,
                    'link'       => $link,
                    'similarity' => round($sim * 100, 2),
                ];
            }
        }
    } catch (\Throwable $e) {
        // ignore, fall through to crossref
    }

    // ---- Fetch from Crossref ----
    // Docs: https://api.crossref.org/works?query.title=...
    $crossref = [];
    try {
        $crRes = Http::timeout(12)
            ->withHeaders(['User-Agent' => 'ChainScholar/1.0 (mailto:youremail@example.com)'])
            ->get('https://api.crossref.org/works', [
                'query.title' => $inputTitle,
                'rows'        => 5,
                'select'      => 'title,author,issued,URL,DOI',
            ]);

        if ($crRes->ok()) {
            $items = $crRes->json('message.items', []);
            foreach ($items as $it) {
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

                $sim = self::cosineSimilarity(mb_strtolower($inputTitle), mb_strtolower($title));

                $crossref[] = [
                    'source'     => 'crossref',
                    'title'      => $title,
                    'authors'    => $auths,
                    'year'       => $year,
                    'link'       => $link,
                    'similarity' => round($sim * 100, 2),
                ];
            }
        }
    } catch (\Throwable $e) {
        // ignore
    }

    // Merge, sort, unique by title
    $merged = array_merge($openAlex, $crossref);
    // Drop empties and normalize
    $merged = array_values(array_filter($merged, fn($r) => !empty($r['title'])));

    // Unique by normalized title (case-insensitive)
    $seen = [];
    $unique = [];
    foreach ($merged as $r) {
        $k = mb_strtolower($r['title']);
        if (isset($seen[$k])) continue;
        $seen[$k] = true;
        $unique[] = $r;
    }

    usort($unique, fn($a,$b) => $b['similarity'] <=> $a['similarity']);

    $max = $unique[0]['similarity'] ?? 0;
    $out = [
        'max_similarity' => $max,
        'approved'       => $max < 30, // keep your 30% threshold
        'results'        => $unique,
    ];

    // cache if we got anything
    if (!empty($unique)) {
        Cache::put($cacheKey, $out, now()->addMinutes(60));
    }

    return response()->json($out);
}

    /** ---------- Scoring helpers (internal + web) ---------- */

    private function scoreSuggestions(array $suggestions, array $existingTitles): array
    {
        $existing = array_values(array_filter(array_map('strval', $existingTitles)));
        $scored   = [];

        foreach ($suggestions as $s) {
            $t = $s['title'];

            $internalMax = 0.0;
            foreach ($existing as $ex) {
                $c = self::cosineSimilarity($t, $ex);
                if ($c > $internalMax) $internalMax = $c;
            }
            $internalPct = round($internalMax * 100, 2);

            $webPct = $this->computeWebSimilarityPercent($t);

            $scored[] = [
                'title'        => $t,
                'why'          => $s['why'],
                'internal_pct' => $internalPct,
                'web_pct'      => $webPct,
                'approved'     => ($internalPct < 30 && $webPct < 30),
            ];
        }
        return $scored;
    }

    private function computeWebSimilarityPercent(string $title): float
    {
        try {
            $response = Http::retry(2, 200)->get('https://api.semanticscholar.org/graph/v1/paper/search', [
                'query'  => $title,
                'fields' => 'title',
                'limit'  => 5,
            ]);
            if (!$response->ok()) return 0.0;

            $items = $response->json('data', []);
            $max = 0.0;
            foreach ($items as $it) {
                $webTitle = (string)($it['title'] ?? '');
                $score    = self::cosineSimilarity(strtolower($title), strtolower($webTitle));
                $pct      = round($score * 100, 2);
                if ($pct > $max) $max = $pct;
            }
            return $max;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /** ---------- Cosine helpers ---------- */

    private static function cosineSimilarity($textA, $textB)
    {
        $tokensA = self::tokenize($textA);
        $tokensB = self::tokenize($textB);

        $freqA = self::termFreqMap($tokensA);
        $freqB = self::termFreqMap($tokensB);

        $dot = self::dotProduct($freqA, $freqB);
        $mag = self::magnitude($freqA) * self::magnitude($freqB);

        return $mag == 0 ? 0.0 : $dot / $mag;
    }

        private static function tokenize($text)
        {
            // Normalize
            $text = mb_strtolower((string)$text, 'UTF-8');
            // Replace non-letter/number with space (keeps Unicode letters/numbers)
            $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);

            // Base generic stopwords (yours)
            $genericStop = [
                'a','an','and','are','as','at','be','by','for','from','has','he','in','is','it','its',
                'of','on','that','the','to','was','were','will','with',
                // academic filler
                'study','research','paper','report','project','capstone','case','review','investigation',
                'analysis','approach','effect','impact','model','method','methods','design','development',
                'evaluation','implementation','system','application','framework','prototype','solution',
                'tool','tools','technology','technologies','process','exploration','assessment'
            ];

            // Domain/common demographic/context terms that cause false positives
            // (You can extend this list as you see similar “always present” words.)
            $domainStop = [
                'senior','high','school','shs','student','students','learner','learners','pupil','pupils',
                'class','classes','section','sections','grade','grades','level','levels',
                'teacher','teachers','adviser','advisor','advisers','advisors',
                'academic','academics','education','educational','institution','institutions',
                'philippine','philippines','local','community',
                // time/context
                'year','years','semester','semesters','term','terms'
            ];

            // Merge & index for O(1) lookups
            $stop = array_fill_keys(array_unique(array_merge($genericStop, $domainStop)), true);

            // Split on whitespace
            $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

            // Filter:
            // - remove stopwords
            // - remove very short tokens (<=2)
            // - remove pure numbers
            $out = [];
            foreach ($words as $w) {
                if (isset($stop[$w])) continue;
                if (mb_strlen($w, 'UTF-8') <= 2) continue;
                if (preg_match('/^\d+$/', $w)) continue;
                $out[] = $w;
            }

            return array_values($out);
        }



    private static function termFreqMap($tokens)
    {
        $freq = [];
        foreach ($tokens as $t) {
            $freq[$t] = ($freq[$t] ?? 0) + 1;
        }
        return $freq;
    }

    private static function dotProduct($a, $b)
    {
        $dot = 0;
        foreach ($a as $k => $v) {
            if (isset($b[$k])) $dot += $v * $b[$k];
        }
        return $dot;
    }

    private static function magnitude($map)
    {
        $sum = 0;
        foreach ($map as $v) $sum += $v * $v;
        return sqrt($sum);
    }


 

}
