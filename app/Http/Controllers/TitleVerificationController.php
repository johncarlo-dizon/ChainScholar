<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TitleVerificationController extends Controller
{
    /**
     * POST /titles/suggest
     * Uses Gemini to suggest improved titles and scores each against
     * internal titles and the web (Semantic Scholar).
     */
    public function suggestTitlesGemini(Request $request)
    {
        $request->validate([
            'draft_title'     => 'required|string|min:5',
            'existing_titles' => 'array',
            'context'         => 'array',
        ]);

        $draft    = trim($request->input('draft_title'));
        $existing = $request->input('existing_titles', []);
        $context  = $request->input('context', []);

        $apiKey   = config('services.gemini.key');
        $model    = config('services.gemini.model', 'gemini-1.5-pro');
        $endpoint = rtrim(config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta'), '/');

        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key missing'], 500);
        }

        $cacheKey = 'gemini_title_enhance_v2:' . md5($draft . json_encode(array_slice($existing, 0, 50)) . json_encode($context));
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $rules = implode("\n", [
            "Act as an Academic Research Title Enhancement Assistant.",
            "Input is a REJECTED academic title. Task: rewrite to produce improved titles that:",
            "- Stay relevant and connected to the original topic.",
            "- Address a clear, significant, and specific problem in the field.",
            "- Integrate latest trends/innovations/emerging concepts relevant to the field.",
            "- Are concise, professional, and suitable for academic approval.",
            "- Sound innovative, impactful, and aligned with modern developments.",
            "Return STRICT JSON with EXACTLY this schema:",
            "{ \"suggestions\": [",
            "  {\"title\":\"...\",\"why\":\"(1–2 sentences explaining the improvement)\"},",
            "  {\"title\":\"...\",\"why\":\"...\"},",
            "  {\"title\":\"...\",\"why\":\"...\"}",
            "] }",
            "Constraints:",
            "- Exactly 3 suggestions.",
            "- Each title ≤ 16 words; avoid colon stacking and buzzword soup.",
            "- Keep close to the original scope (do not change the topic entirely).",
        ]);

        $payloadBlock = [
            "draft_title"  => $draft,
            "context"      => $context,
            "avoid_titles" => array_values($existing),
        ];

        try {
            $res = Http::timeout(20)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$endpoint}/models/{$model}:generateContent?key={$apiKey}", [
                    "generationConfig" => [
                        "temperature" => 0.5,
                        "topK" => 40,
                        "topP" => 0.9,
                        "maxOutputTokens" => 512,
                        "response_mime_type" => "application/json",
                    ],
                    "contents" => [
                        [ "role" => "user", "parts" => [[ "text" => $rules ]] ],
                        [ "role" => "user", "parts" => [[ "text" => json_encode($payloadBlock, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ]] ],
                    ],
                ]);

            if (!$res->ok()) {
                return response()->json(['error' => 'Gemini request failed', 'meta' => $res->json()], 502);
            }

            $raw    = $res->json();
            $jsonStr = data_get($raw, 'candidates.0.content.parts.0.text');
            if (!$jsonStr) {
                $parts = data_get($raw, 'candidates.0.content.parts', []);
                foreach ($parts as $p) {
                    if (isset($p['inlineData']['mimeType']) && str_contains($p['inlineData']['mimeType'], 'json')) {
                        $jsonStr = base64_decode($p['inlineData']['data'] ?? '');
                        break;
                    }
                }
            }
            if (!$jsonStr) {
                return response()->json([
                    'error' => 'Gemini returned no candidates (possibly safety blocked).',
                    'feedback' => data_get($raw, 'promptFeedback', []),
                    'raw' => $raw,
                ], 500);
            }

            $parsed = json_decode($jsonStr, true);
            if (!is_array($parsed) || !isset($parsed['suggestions'])) {
                if (preg_match('/\{.*\}/s', $jsonStr, $m)) {
                    $parsed = json_decode($m[0], true);
                }
            }
            if (!is_array($parsed) || !isset($parsed['suggestions']) || !is_array($parsed['suggestions'])) {
                $parsed = ['suggestions' => []];
            }

            $rawSug = collect($parsed['suggestions'])
                ->map(fn ($s) => ['title' => trim((string)($s['title'] ?? '')), 'why' => trim((string)($s['why'] ?? ''))])
                ->filter(fn ($s) => $s['title'] !== '')
                ->take(6)
                ->values();

            $scored   = $this->scoreSuggestions($rawSug->all(), $existing);
            $approved = array_values(array_filter($scored, fn($x) => $x['internal_pct'] < 30 && $x['web_pct'] < 30));
            $final    = $approved;

            if (count($final) < 3 && count($scored)) {
                usort($scored, fn($a,$b)=> ($a['internal_pct'] + $a['web_pct']) <=> ($b['internal_pct'] + $b['web_pct']));
                foreach ($scored as $cand) {
                    if (count($final) >= 3) break;
                    if (!in_array($cand, $final, true)) $final[] = $cand;
                }
            }

            $final = array_slice($final, 0, 3);
            $out   = ['suggestions' => $final];

            Cache::put($cacheKey, $out, now()->addMinutes(30));
            return response()->json($out);

        } catch (\Throwable $e) {
            return response()->json(['error' => 'Gemini error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /titles/check-internal
     * Internal similarity against existing titles in DB.
     *
     * TIP: Your old code compared against Document titles.
     * If you truly want document titles, switch Title::all() to Document::all()
     * and map to the correct field.
     */
    public function checkTitleSimilarity(Request $request)
    {
        $inputTitle = (string) $request->input('title', '');
        $excludeTitleId = $request->input('exclude_title_id'); // optional

        // Compare against existing Titles (recommended)
        $titles = Title::query()
            ->when($excludeTitleId, fn($q) => $q->where('id', '!=', $excludeTitleId))
            ->pluck('title')
            ->all();

        $similarities = [];
        foreach ($titles as $existing) {
            $score = self::cosineSimilarity($inputTitle, $existing);
            $similarities[] = [
                'existing_title' => $existing,
                'similarity'     => round($score * 100, 2),
            ];
        }

        usort($similarities, fn($a,$b) => $b['similarity'] <=> $a['similarity']);
        $maxSimilarity = $similarities[0]['similarity'] ?? 0;

        return response()->json([
            'max_similarity' => $maxSimilarity,
            'similarities'   => $similarities,
            'approved'       => $maxSimilarity < 30,
        ]);
    }

    /**
     * POST /titles/check-web
     * Web similarity using Semantic Scholar.
     */
    public function checkWebTitleSimilarity(Request $request)
    {
        $inputTitle = strtolower($request->input('title', ''));
        $attempt    = (int) $request->input('attempt', 1);

        $cacheKey = 'web_similarity_' . md5($inputTitle);

        if ($attempt === 1 && Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $response = Http::retry(2, 200)->get('https://api.semanticscholar.org/graph/v1/paper/search', [
            'query'  => $inputTitle,
            'fields' => 'title',
            'limit'  => 2,
        ]);

        $items = $response->ok() ? $response->json('data', []) : [];
        $similarities = [];

        foreach ($items as $item) {
            $webTitle = strtolower($item['title'] ?? '');
            $score    = self::cosineSimilarity($inputTitle, $webTitle);
            $similarities[] = [
                'title'      => $item['title'] ?? '',
                'similarity' => round($score * 100, 2),
            ];
        }

        usort($similarities, fn($a,$b) => $b['similarity'] <=> $a['similarity']);
        $max = $similarities[0]['similarity'] ?? 0;

        $data = [
            'max_similarity' => $max,
            'approved'       => $max < 30,
            'results'        => $similarities,
        ];

        if (!empty($similarities)) {
            Cache::put($cacheKey, $data, now()->addMinutes(60));
        }

        return response()->json($data);
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
        $stopWords = [
            'a','an','and','are','as','at','be','by','for','from','has','he','in','is','it','its',
            'of','on','that','the','to','was','were','will','with', // generic
            // academic filler
            'study','research','paper','report','project','capstone','case','review','investigation',
            'analysis','approach','effect','impact','model','method','methods','design','development',
            'evaluation','implementation','system','application','framework','prototype','solution',
            'tool','tools','technology','technologies','process','exploration','assessment'
        ];
        $words = preg_split('/\W+/', strtolower($text));
        return array_values(array_filter($words, fn($w) => !in_array($w, $stopWords, true) && strlen($w) > 1));
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
