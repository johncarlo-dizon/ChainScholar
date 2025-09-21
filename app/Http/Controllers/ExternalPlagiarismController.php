<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ExternalPlagiarismMatch;
use App\Models\ExternalPlagiarismScan;
use App\Services\CopyleaksClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExternalPlagiarismController extends Controller
{
    /* ------------------------ logging ------------------------ */

    private function clog(string $level, string $message, array $context = []): void
    {
        if (isset($context['payload'])) {
            $json = json_encode($context['payload'], JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
            $context['payload'] = mb_strimwidth($json ?? '', 0, 200_000, '…');
        }
        Log::channel('copyleaks')->log($level, $message, $context);
    }

    /* ------------------------ start scan ------------------------ */

    public function start(Request $req, CopyleaksClient $copyleaks)
    {
        $req->validate([
            'document_id'  => 'required|integer|exists:documents,id',
            'content_html' => 'required|string|min:40',
        ]);

        $doc     = Document::findOrFail($req->document_id);
        $scanId  = (string) Str::uuid();
        $webhook = (string) config('services.copyleaks.webhook');

        $text = $this->htmlToPlainText((string) $req->content_html);
        if (mb_strlen($text, 'UTF-8') < 40) {
            return response()->json(['ok'=>false,'message'=>'Insufficient content after cleaning.'], 422);
        }

        $scan = ExternalPlagiarismScan::create([
            'document_id'  => $doc->id,
            'scan_id'      => $scanId,
            'status'       => 'queued',
            'score'        => 0,
            'credits_used' => null,
            'sandbox'      => (bool) config('services.copyleaks.sandbox', true),
        ]);

        try {
            $token = $copyleaks->getAccessToken();
            $copyleaks->submitTextScan($token, $scanId, $text, $webhook);
            return response()->json(['ok'=>true,'scan_id'=>$scanId], 202);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body   = $e->response?->body();
            $this->clog('error', 'Submit failed', [
                'scan_id'=>$scanId,'status'=>$status,'body'=>Str::limit($body ?? '',2000)
            ]);
            $scan->update(['status'=>'error','error_message'=>"HTTP {$status}: ".Str::limit($body ?? 'Unknown',900)]);
            return response()->json(['ok'=>false,'message'=>'Copyleaks submit failed'],502);
        } catch (\Throwable $e) {
            $this->clog('error', 'Submit failed (Throwable)', ['scan_id'=>$scanId,'err'=>$e->getMessage()]);
            $scan->update(['status'=>'error','error_message'=>Str::limit($e->getMessage(),900)]);
            return response()->json(['ok'=>false,'message'=>'Failed to submit to Copyleaks.'],502);
        }
    }

    /* ------------------------ webhook ------------------------ */

    public function webhook(Request $req, CopyleaksClient $copyleaks, string $status = null)
    {
        $expected = (string) config('services.copyleaks.signing_secret');
        if (!$expected || $req->header('Authentication') !== $expected) {
            $this->clog('warning','Webhook rejected: bad Authentication header');
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $payload = $req->json()->all();
        $this->clog('info','Webhook hit',['payload'=>$payload]);

        $scanId = $payload['developerPayload'] ?? $payload['scanId'] ?? ($payload['data']['scanId'] ?? null);
        if (!$scanId) return response()->json(['ok'=>true]);

        $scan = ExternalPlagiarismScan::where('scan_id',$scanId)->first();
        if (!$scan) {
            $this->clog('warning','Webhook for unknown scanId',['scanId'=>$scanId]);
            return response()->json(['ok'=>true]);
        }

        $scan->update(['raw_payload'=>$payload]);

        $event = $payload['event'] ?? $payload['type'] ?? $payload['status'] ?? ($payload['data']['status'] ?? null);
        $event = is_string($event) ? strtolower($event) : null;

        $inProgress = ['starting','processing','running','queued','scanning','in_progress','indexing','new-result'];
        $done       = ['completed','finished','done','success','finished_successfully','scan.completed'];

        // normalize buckets
        $results = $payload['results'] ?? ($payload['data']['results'] ?? []);
        $resultNode = [
            'internet'     => $results['internet']     ?? ($payload['internet']     ?? []),
            'database'     => $results['database']     ?? ($payload['database']     ?? []),
            'repositories' => $results['repositories'] ?? ($payload['repositories'] ?? []),
            'batch'        => $results['batch']        ?? ($payload['batch']        ?? []),
        ];
        $hasResults = collect($resultNode)->flatten(1)->isNotEmpty();

        $scannedDoc    = $payload['scannedDocument'] ?? ($payload['data']['scannedDocument'] ?? null);
        $hasScannedDoc = is_array($scannedDoc);







        // Normalize "status: 0" to a completed event
        $statusCode = $payload['status'] ?? ($payload['data']['status'] ?? null);
        if ($statusCode === 0 || $statusCode === '0') {
            $event = 'completed';
        }

        // If the scanned doc is present and ALL result buckets are empty,
        // treat this as a completed scan with zero matches.
        $allBucketsEmpty = empty($resultNode['internet'])
            && empty($resultNode['database'])
            && empty($resultNode['repositories'])
            && empty($resultNode['batch']);

        if ($hasScannedDoc && $allBucketsEmpty) {
            $credits       = (int)($payload['scannedDocument']['credits'] ?? 0);
            $aggregated    = (int) $this->extractAggregatedScore($payload); // should be 0 here

            $scan->update([
                'status'       => 'completed',
                'score'        => $aggregated, // 0
                'credits_used' => $credits ?: null,
            ]);

            // No export necessary (no result IDs).
            return response()->json(['ok' => true]);
              
        }


        try {
            DB::transaction(function () use ($payload,$event,$inProgress,$done,$hasResults,$hasScannedDoc,$scannedDoc,$resultNode,$scan,$copyleaks) {

                if ($event && in_array($event,$inProgress,true)) {
                    $scan->update(['status'=>'running']);
                    return;
                }

                if ($event && in_array($event,['error','canceled','expired','not_found','failed'],true)) {
                    $msg = $this->extractErrorMessage($payload) ?: 'External scan failed.';
                    $scan->update(['status'=>'error','error_message'=>Str::limit($msg,900)]);
                    return;
                }

                $isFinal = ($event && in_array($event,$done,true)) || ($hasResults && $hasScannedDoc);
                if (!$isFinal) {
                    $scan->update(['status'=>'running']);
                    return;
                }

                $agg           = $this->extractAggregatedScore($payload);
                $credits       = (int)($scannedDoc['credits'] ?? ($payload['credits'] ?? 0));
                $docTotalWords = (int)($scannedDoc['totalWords'] ?? 0);

                ExternalPlagiarismMatch::where('scan_id_fk',$scan->id)->delete();
                $matches    = $this->collectMatches($resultNode, $docTotalWords ?: null);
                $maxPercent = (int) $agg;

                foreach ($matches as $m) {
                    ExternalPlagiarismMatch::create([
                        'scan_id_fk'     => $scan->id,
                        'document_id'    => $scan->document_id,
                        'percent'        => (int)($m['percent'] ?? 0),
                        'source_title'   => $m['source_title'] ?? 'External source',
                        'source_url'     => $m['source_url'] ?? null,
                        'your_excerpt'   => $m['your_excerpt'] ?? null,
                    ]);
                    $maxPercent = max($maxPercent, (int)($m['percent'] ?? 0));
                }

                $scan->update([
                    'status'       => 'completed',
                    'score'        => $maxPercent,
                    'credits_used' => $credits ?: null
                ]);

                // schedule export once
                $resultIds = $this->collectResultIds($resultNode);
                if ($resultIds && $hasScannedDoc) {
                    $cacheKey = 'copyleaks:export-scheduled:'.$scan->scan_id;
                    if (!Cache::add($cacheKey, 1, now()->addHours(24))) {
                        $this->clog('info','Export already scheduled',['scan_id'=>$scan->scan_id]);
                        return;
                    }

                    try {
                        $token = $copyleaks->getAccessToken();

                        $exportBase = (string) config('services.copyleaks.export_base');
                        if (!$exportBase) {
                            $statusUrl = (string) config('services.copyleaks.webhook');
                            $u = parse_url($statusUrl);
                            $origin = $u && !empty($u['scheme']) && !empty($u['host'])
                                ? $u['scheme'].'://'.$u['host'].(isset($u['port'])?':'.$u['port']:'')
                                : rtrim((string) config('app.url'), '/');
                            $exportBase = $origin;
                        }
                        $cleanBase = rtrim($exportBase, '/');

                        $completionEndpoint = $cleanBase . "/webhooks/copyleaks/export/completed/{$scan->scan_id}/__AUTO__";

                        $exportId = $copyleaks->requestExport(
                            $token,
                            $scan->scan_id,
                            $resultIds,
                            $cleanBase,
                            $completionEndpoint
                        );

                        $this->clog('info', 'Export scheduled', [
                            'scan_id'      => $scan->scan_id,
                            'export_id'    => $exportId,
                            'results'      => $resultIds,
                            'result_posts' => collect($resultIds)->map(
                                fn($rid) => $cleanBase."/webhooks/copyleaks/export/result/{$scan->scan_id}/{$rid}"
                            )->values(),
                            'completion'   => $cleanBase."/webhooks/copyleaks/export/completed/{$scan->scan_id}/{$exportId}",
                        ]);
                    } catch (\Throwable $e) {
                        Cache::forget($cacheKey);
                        $this->clog('warning','Export request failed',['scan_id'=>$scan->scan_id,'err'=>$e->getMessage()]);
                    }
                }
            });
        } catch (\Throwable $e) {
            $this->clog('error','Webhook transaction failed',['scan_id'=>$scanId,'err'=>$e->getMessage()]);
            $scan->update(['status'=>'error','error_message'=>Str::limit($e->getMessage(),900)]);
        }

        return response()->json(['ok'=>true]);
    }

    /* ------------------------ export per result ------------------------ */

    public function exportResult(Request $req, string $scanId, string $resultId)
    {
        $expected = (string) config('services.copyleaks.signing_secret');
        if (!$expected || $req->header('Authentication') !== $expected) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $payload = $req->json()->all();
        Log::channel('copyleaks')->info('Export payload', [
            'scanId'=>$scanId,'resultId'=>$resultId,
            'has'=>[
                'html'=>isset($payload['html']['value']),
                'text'=>isset($payload['text']['value']),
                'cmp'=>isset($payload['html']['comparison']) || isset($payload['text']['comparison']),
            ]
        ]);

        $scan = ExternalPlagiarismScan::where('scan_id', $scanId)->first();
        if (!$scan) return response()->json(['ok'=>true]);

        // If crawled (your original) isn't cached yet, queue and return fast
        $crawled = Cache::get("copyleaks:crawled:{$scanId}");
        if (!$crawled || (!($crawled['html'] ?? '') && !($crawled['text'] ?? ''))) {
            $this->pendResult($scanId, $resultId, $payload);
            return response()->json(['ok'=>true,'queued'=>true]);
        }

        // Otherwise process immediately
        $this->processExportResult($scan, $payload, $resultId);
        return response()->json(['ok'=>true]);
    }

    private function processExportResult(\App\Models\ExternalPlagiarismScan $scan, array $payload, string $resultId): void
    {
        // ----- idempotency key (based on core fields we actually care about) -----
        $rawForHash = json_encode([
            'text'  => $payload['text']['value']  ?? null,
            'html'  => $payload['html']['value']  ?? null,
            'score' => $payload['score']['aggregatedScore'] ?? null,
            'stats' => $payload['statistics'] ?? null,
        ], JSON_UNESCAPED_UNICODE);

        $exportKey = 'rid:' . $resultId . '|sha1:' . sha1($rawForHash ?? '');
        if (ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)->where('export_key', $exportKey)->exists()) {
            return; // already processed this exact payload
        }

        // ----- compute % score for this specific source -----
        $pct = 0;
        if (isset($payload['score']['aggregatedScore']) && is_numeric($payload['score']['aggregatedScore'])) {
            $pct = (int) round((float) $payload['score']['aggregatedScore']);
        } elseif (isset($payload['matchedWords'], $payload['totalWords']) && (int) $payload['totalWords'] > 0) {
            $pct = (int) round(((float) $payload['matchedWords'] * 100.0) / (float) $payload['totalWords']);
        } else {
            // fallback: derive from doc total words if present in original webhook
            $raw = $scan->raw_payload;
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?: [];
            }
            $docTotal = (int) ($raw['scannedDocument']['totalWords'] ?? 0);
            $mw = (int) (
                ($payload['statistics']['identical'] ?? 0) +
                ($payload['statistics']['minorChanges'] ?? 0) +
                ($payload['statistics']['relatedMeaning'] ?? 0)
            );
            if ($docTotal > 0 && $mw > 0) {
                $pct = (int) round(($mw * 100.0) / max(1, $docTotal));
            }
        }

        if ($pct <= 0) {
            return; // ignore absolute zeros
        }

        // ----- load your crawled version (guaranteed present by caller) -----
        $crawled     = Cache::get("copyleaks:crawled:{$scan->scan_id}", ['html' => '', 'text' => '']);
        $htmlFullYou = (string) ($crawled['html'] ?? '');
        $textFullYou = (string) ($crawled['text'] ?? '');

        // ----- figure out the best "your excerpt" (no source excerpt stored) -----
        $cats = ['identical', 'minorChanges', 'relatedMeaning'];

        $yourRangeHtml = $this->bestRangeFromAny(array_map(
            fn($c) => $payload['html']['comparison'][$c]['suspected']['chars']
                ?? $payload['html']['comparison'][$c]['target']['chars']
                ?? $payload['html']['comparison'][$c]['document']['chars']
                ?? null,
            $cats
        ));
        $yourRangeText = $this->bestRangeFromAny(array_map(
            fn($c) => $payload['text']['comparison'][$c]['suspected']['chars']
                ?? $payload['text']['comparison'][$c]['target']['chars']
                ?? $payload['text']['comparison'][$c]['document']['chars']
                ?? null,
            $cats
        ));

        $yourExcerpt = '';
        if ($yourRangeHtml && $htmlFullYou !== '') {
            $yourExcerpt = $this->cleanHtmlSnippet($this->sliceByRange($htmlFullYou, $yourRangeHtml, 100));
        } elseif ($yourRangeText && $textFullYou !== '') {
            $yourExcerpt = $this->textSanitize($this->sliceByRange($textFullYou, $yourRangeText, 100));
        } else {
            // last resort: fragment-style fallbacks if present in payload
            [$fragYour] = $this->findBestFragmentPair($payload);
            if ($fragYour) {
                $yourExcerpt = $fragYour;
            }
        }
        $yourExcerpt = \Illuminate\Support\Str::limit($this->textSanitize($yourExcerpt ?? ''), 400);

        // Require a meaningful snippet
        if ($yourExcerpt === '') {
            return;
        }

        // ----- noise gate: drop tiny/boilerplate matches unless the quote is long enough -----
        $minPct     = (int) (config('services.copyleaks.min_percent', 20)); // default 20%
        $minWordsOK = 18; // allow low % if contiguous quote is long
        $longEnough = str_word_count($yourExcerpt) >= $minWordsOK;

        if ($pct < $minPct && !$longEnough) {
            if ($this->isBoilerplate($yourExcerpt)) {
                return;
            }
        }

        // ----- build source URL/title (for display only; no source excerpt) -----
        $htmlFullSrc  = (string) ($payload['html']['value'] ?? '');
        $sourceUrlRaw = $payload['source']['url'] ?? ($payload['url'] ?? null);
        if (!$sourceUrlRaw) {
            $sourceUrlRaw = $this->inferUrlFromHtml($htmlFullSrc);
        }
        $sourceUrl = $this->normalizeUrl($sourceUrlRaw);

        $sourceTitleRaw = $payload['source']['title'] ?? ($payload['title'] ?? null);
        if (!is_string($sourceTitleRaw) || trim($sourceTitleRaw) === '') {
            $sourceTitleRaw = $this->extractTitleFromHtml($htmlFullSrc) ?: $this->titleFromUrl($sourceUrl);
        }
        $sourceTitle = $this->tidyTitle($sourceTitleRaw, $htmlFullSrc, $sourceUrl);

        // ----- upsert: merge by normalized URL (keep the highest percent) -----
        $updated = false;
        $sourceUrlNorm = $this->normalizeUrl($sourceUrl);

        if ($sourceUrlNorm) {
            $existing = ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)
                ->whereRaw('COALESCE(source_url, "") <> ""')
                ->get()
                ->first(function ($r) use ($sourceUrlNorm) {
                    return $this->normalizeUrl($r->source_url) === $sourceUrlNorm;
                });

            if ($existing) {
                $existing->update([
                    'percent'      => max((int) $existing->percent, (int) $pct),
                    'source_title' => $sourceTitle ?: ($existing->source_title ?: 'External source'),
                    'your_excerpt' => $yourExcerpt ?: $existing->your_excerpt,
                    'export_key'   => $exportKey,
                ]);
                $updated = true;
            }
        }

        if (!$updated) {
            ExternalPlagiarismMatch::create([
                'scan_id_fk'   => $scan->id,
                'document_id'  => $scan->document_id,
                'percent'      => (int) $pct,
                'source_title' => $sourceTitle ?: 'External source',
                'source_url'   => $sourceUrl ?: null,
                'your_excerpt' => $yourExcerpt ?: null,
                'export_key'   => $exportKey,
            ]);
        }

        // ----- bump scan score & status -----
        if ($pct > (int) $scan->score) {
            $scan->update(['score' => $pct]);
        }
        if (in_array($scan->status, ['completed', 'running', 'queued'], true)) {
            $scan->update(['status' => 'exported']);
        }
    }

    /* ------------------------ export completed ------------------------ */

    public function exportCompleted(Request $req, string $scanId, string $exportId)
    {
        $expected = (string) config('services.copyleaks.signing_secret');
        if (!$expected || $req->header('Authentication') !== $expected) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        Log::info('Copyleaks export completed webhook', [
            'scanId'=>$scanId,'exportId'=>$exportId, 'payload'=>$req->json()->all()
        ]);

        $scan = ExternalPlagiarismScan::where('scan_id', $scanId)->first();
        if ($scan && $scan->status !== 'error') {
            $scan->update(['status' => 'exported']);
        }

        return response()->json(['ok'=>true]);
    }


    // Insert BELOW this line: "return response()->json(['ok'=>true]);" of exportCompleted() OR anywhere in the class
    public function resync(Request $req, CopyleaksClient $copyleaks)
    {
        $req->validate([
            'scan_id'     => 'required|string',
            'document_id' => 'nullable|integer|exists:documents,id',
        ]);

        $scan = \App\Models\ExternalPlagiarismScan::where('scan_id', $req->scan_id)->first();
        if (!$scan) {
            return response()->json(['ok'=>false,'message'=>'Unknown scan id.'], 404);
        }

        try {
            $token = $copyleaks->getAccessToken();

            // 1) Ask Copyleaks to resend the final status webhook, in case we missed it.
            $copyleaks->resendWebhook($token, $scan->scan_id);

            // 2) If we already have a completed payload with result IDs but never exported,
            //    (rare race) we can re-ensure export is scheduled.
            $raw = $scan->raw_payload;
            if (is_string($raw)) $raw = json_decode($raw, true) ?: [];
            $resultsNode = $raw['results'] ?? ($raw['data']['results'] ?? []);
            $resultIds   = [];
            foreach (['internet','database','repositories','batch'] as $b) {
                if (!empty($resultsNode[$b])) {
                    foreach ($resultsNode[$b] as $r) {
                        if (!empty($r['id'])) $resultIds[] = (string) $r['id'];
                    }
                }
            }
            $resultIds = array_values(array_unique($resultIds));

            $hasScannedDoc = isset($raw['scannedDocument']) || isset(($raw['data'] ?? [])['scannedDocument']);
            $isCompleted   = in_array(strtolower((string)($raw['event'] ?? $raw['type'] ?? $raw['status'] ?? ($raw['data']['status'] ?? ''))),
                            ['completed','finished','done','success','finished_successfully','scan.completed'], true);

            if ($isCompleted && $hasScannedDoc && $resultIds) {
                $cacheKey = 'copyleaks:export-scheduled:'.$scan->scan_id;
                if (\Illuminate\Support\Facades\Cache::add($cacheKey, 1, now()->addHours(24))) {
                    try {
                        $exportBase = (string) config('services.copyleaks.export_base') ?: rtrim((string) config('app.url'), '/');
                        $completionEndpoint = rtrim($exportBase,'/')
                        . "/webhooks/copyleaks/export/completed/{$scan->scan_id}/__AUTO__";
                        $copyleaks->requestExport($token, $scan->scan_id, $resultIds, rtrim($exportBase,'/'), $completionEndpoint);
                        $this->clog('info','Resync: export re-scheduled',['scan_id'=>$scan->scan_id,'results'=>$resultIds]);
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Cache::forget($cacheKey);
                        $this->clog('warning','Resync: export schedule failed',['scan_id'=>$scan->scan_id,'err'=>$e->getMessage()]);
                    }
                }
            }

            return response()->json(['ok'=>true]);
        } catch (\Throwable $e) {
            $this->clog('warning','Resync failed',['scan_id'=>$scan->scan_id,'err'=>$e->getMessage()]);
            return response()->json(['ok'=>false,'message'=>'Resync failed.'], 502);
        }
    }


    /* ------------------------ status (collapsed per domain) ------------------------ */

    public function status(Request $req, Document $document)
    {
        $scanId = $req->query('scan_id');
        $q = ExternalPlagiarismScan::where('document_id', $document->id);
        $scan = $scanId
            ? $q->where('scan_id', $scanId)->first()
            : $q->orderByDesc('updated_at')->orderByDesc('id')->first();

        if (!$scan) return response()->json(['status' => 'none']);

// NOTE: we only keep/stabilize your_excerpt; no source excerpt processing.
        $matchesQ = ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)->orderByDesc('percent');

        // collapse: best per normalized host (www/m/amp stripped; mirrors collapsed)
        $collapsed = [];
        if (in_array($scan->status, ['completed','exported','running','queued'])) {
            foreach ((clone $matchesQ)->limit(250)->get() as $r) {
                $url  = $this->normalizeUrl($r->source_url);
                $host = $this->normalizedHostFromUrl($url) ?: '__no_host__';

                // sanitize before storing
                $r->source_title   = $this->tidyTitle($r->source_title, null, $url);
                $r->your_excerpt   = $this->textSanitize($r->your_excerpt ?? '');

                // skip completely empty cards
                if ($r->your_excerpt === '') continue;

                if (!isset($collapsed[$host]) || (int)$r->percent > (int)$collapsed[$host]->percent) {
                    $r->source_url = $url;
                    $collapsed[$host] = $r;
                }
            }
        }

        $list = collect($collapsed)->sortByDesc(fn($r) => (int)$r->percent)->values()->take(20);

        $matches = $list->map(fn($r) => [
            'percent'      => (int) $r->percent,
            'source_title' => $r->source_title ?: ($this->titleFromUrl($r->source_url) ?: 'External source'),
            'source_url'   => $r->source_url,
            'your_excerpt' => $r->your_excerpt,
        ])->values();

        $sourceMax = (int) (ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)->max('percent') ?? 0);
        $raw = $scan->raw_payload; if (is_string($raw)) $raw = json_decode($raw, true) ?: [];
        $docAgg = (int) $this->extractAggregatedScore((array) $raw);
        $totalMatches = ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)->count();

        return response()->json([
            'status'          => $scan->status,
            'score'           => (int) $scan->score,
            'source_max'      => $sourceMax,
            'doc_aggregated'  => $docAgg,
            'credits_used'    => (int) ($scan->credits_used ?? 0),
            'sandbox'         => (bool) $scan->sandbox,
            'matches'         => $matches,
            'matches_count'   => $totalMatches,
            'error'           => $scan->error_message,
            'scan_id'         => $scan->scan_id,
            'updated_at_iso'  => optional($scan->updated_at)->toIso8601String(),
        ]);
    }

    /* ------------------------ helpers ------------------------ */

    private function extractErrorMessage(array $payload): ?string
    {
        $paths = [
            ['error','message'], ['data','error','message'], ['message'],
            ['data','message'], ['error'], ['data','error'],
        ];
        foreach ($paths as $p) {
            $node = $payload;
            foreach ($p as $k) { $node = is_array($node) ? ($node[$k] ?? null) : null; }
            if (is_string($node) && trim($node) !== '') return $node;
        }
        return null;
    }

    private function extractAggregatedScore(array $payload): int
    {
        $paths = [
            ['results','score','aggregatedScore'],
            ['results','aggregatedScore'],
            ['data','results','score','aggregatedScore'],
            ['data','results','aggregatedScore'],
            ['similarityScore'],
            ['score'],
        ];
        foreach ($paths as $p) {
            $node = $payload;
            foreach ($p as $k) { $node = is_array($node) ? ($node[$k] ?? null) : null; }
            if (is_numeric($node)) return (int) round((float) $node);
        }
        return 0;
    }

    private function collectResultIds(?array $resultNode): array
    {
        if (!$resultNode) return [];
        $ids = [];
        foreach (['internet','database','repositories','batch'] as $bucket) {
            if (!empty($resultNode[$bucket]) && is_array($resultNode[$bucket])) {
                foreach ($resultNode[$bucket] as $r) {
                    if (!empty($r['id'])) $ids[] = (string) $r['id'];
                }
            }
        }
        return array_values(array_unique($ids));
    }

    private function collectMatches(array $resultNode, ?int $docTotalWords = null): array
    {
        $rows = [];

        foreach (['internet','database','repositories','batch'] as $bucket) {
            $arr = $resultNode[$bucket] ?? null;
            if (!is_array($arr)) continue;

            foreach ($arr as $m) {
                // percent (prefer score → your doc words → source words)
                if (isset($m['score']['aggregatedScore'])) {
                    $pct = (float) $m['score']['aggregatedScore'];
                } elseif ($docTotalWords) {
                    $mw  = $m['identicalWords'] ?? $m['matchedWords'] ?? 0;
                    $pct = ((float)$mw * 100.0) / max(1.0, (float)$docTotalWords);
                } elseif (isset($m['totalWords'], $m['matchedWords']) && (int)$m['totalWords'] > 0) {
                    $pct = ((float)$m['matchedWords'] * 100.0) / (float)$m['totalWords'];
                } else {
                    $pct = 0.0;
                }
                $pct = max(0, (int) round($pct));

                /** keep only meaningful matches at this stage (no source excerpt work) **/
                $MIN_PCT = (int) config('services.copyleaks.min_percent', 20);
                if ($pct <= 0) continue;
                if ($pct < $MIN_PCT) continue;

                // URL + title (unproxy)
                $urlRaw = $m['url'] ?? ($m['metadata']['finalUrl'] ?? ($m['source']['url'] ?? null));
                $url    = $this->normalizeUrl($urlRaw);

                $titleRaw = $m['title'] ?? ($m['source']['title'] ?? ($url ?: 'External source'));
                $title    = is_string($titleRaw) ? $this->tidyTitle($titleRaw, null, $url) : 'External source';

                // Your preview only (drop source preview entirely)
                $yourPreview = $this->textSanitize($m['text']['value']  ?? ($m['preview']['text']  ?? null));

                // REMOVED: source preview building, intro/boilerplate checks based on source excerpt

                if ($yourPreview === '') continue;

                $rows[] = [
                    'percent'        => $pct,
                    'source_title'   => Str::limit($title, 300),
                    'source_url'     => $url,
                    'your_excerpt'   => $yourPreview,
                ];
            }
        }

        // De-dupe by URL (highest %)
        $by = [];
        foreach ($rows as $r) {
            $key = $r['source_url'] ?: md5(($r['source_title'] ?? ''));
            if (!isset($by[$key]) || $r['percent'] > $by[$key]['percent']) $by[$key] = $r;
        }

        return array_values($by);
    }

    /* ------------------------ cleaning / parsing utils ------------------------ */

    private function unwrapCopyleaksUrl(?string $url): ?string
    {
        if (!$url) return null;
        $p = @parse_url($url);
        if (!$p || empty($p['host'])) return $url;
        $host = strtolower($p['host']);
        if (str_ends_with($host, 'copyleaks.com') && !empty($p['query'])) {
            parse_str($p['query'], $q);
            if (!empty($q['url'])) return $q['url'];
        }
        return $url;
    }

    private function normalizeUrl(?string $url): ?string
    {
        if (!$url) return null;
        $url = $this->unwrapCopyleaksUrl($url);
        $p = @parse_url($url);
        if (!$p || empty($p['host'])) return null;
        $scheme = ($p['scheme'] ?? 'https');
        $host   = strtolower($p['host']);
        $path   = isset($p['path']) ? rtrim($p['path'],'/') : '';
        return $scheme.'://'.$host.$path; // drop query/fragment
    }

    private function normalizedHostFromUrl(?string $url): ?string
    {
        if (!$url) return null;
        $p = @parse_url($url);
        if (!$p || empty($p['host'])) return null;
        $h = strtolower($p['host']);
        $h = preg_replace('/^(www|m|amp)\./i', '', $h); // collapse subdomain variants

        // collapse common UVA metaphors mirrors to a single bucket
        if (preg_match('/\b(?:iath|lib)\.virginia\.edu$/i', $h)) {
            $h = 'virginia.edu';
        }
        return $h;
    }

    private function inferUrlFromHtml(string $html): ?string
    {
        if (preg_match('#<link[^>]+rel=["\']canonical["\'][^>]*href=["\']([^"\']+)#i', $html, $m)) {
            return $this->normalizeUrl(html_entity_decode($m[1], ENT_QUOTES|ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('#<meta[^>]+property=["\']og:url["\'][^>]*content=["\']([^"\']+)#i', $html, $m)) {
            return $this->normalizeUrl(html_entity_decode($m[1], ENT_QUOTES|ENT_HTML5, 'UTF-8'));
        }
        if (preg_match('#<base[^>]+href=["\']([^"\']+)#i', $html, $m)) {
            return $this->normalizeUrl(html_entity_decode($m[1], ENT_QUOTES|ENT_HTML5, 'UTF-8'));
        }
        return null;
    }

    private function isBoilerplate(string $s): bool
    {
        $t = trim($s);
        if ($t === '') return true;
        if (preg_match('/^(skip to content|menu|login|sign in|sign up|«?\s*back to search)\b/i', $t)) return true;
        if (preg_match('/max-(?:snippet|video-preview|image-preview)|index,\s*follow/i', $t)) return true;

        // Strong symbol bias = likely code/nav
        $letters = preg_match_all('/\p{L}/u', $t);
        $symbols = preg_match_all('/[{};:$#@()<>\[\]\|]/u', $t);
        if ($letters < 12 || $symbols > $letters) return true;

        return false;
    }

    private function findBestFragmentPair(array $payload): array
    {
        // scans for fragment-style arrays and picks the longest YOUR fragment only
        $paths = [
            ['comparison','matches'],
            ['comparison','similarities'],
            ['comparison','fragments'],
        ];
        $bestLen = 0; $bestYour = null;

        foreach ($paths as $p) {
            $node = $payload;
            foreach ($p as $k) { $node = is_array($node) ? ($node[$k] ?? null) : null; }
            if (!is_array($node)) continue;

            foreach ($node as $frag) {
                $y = $frag['text']['value']  ?? $frag['text']  ?? null;
                $len = mb_strlen((string)$y, 'UTF-8');
                if ($len > $bestLen) { $bestLen = $len; $bestYour = $y; }
            }
        }

        // Return [yourFragment, null] to preserve call-site destructuring
        return [$bestYour ? $this->textSanitize($bestYour) : null, null];
    }

    private function bestRange($chars): ?array
    {
        if (!is_array($chars)) return null;
        $starts  = $chars['starts']  ?? null;
        $lengths = $chars['lengths'] ?? null;
        if (!is_array($starts) || !is_array($lengths)) return null;
        $best = null;
        for ($i = 0, $n = min(count($starts), count($lengths)); $i < $n; $i++) {
            $l = (int)$lengths[$i]; if ($l <= 0) continue;
            $s = (int)$starts[$i];
            if ($best === null || $l > $best['length']) $best = ['start'=>max(0,$s), 'length'=>$l];
        }
        return $best;
    }

    private function bestRangeFromAny(array $candidates): ?array
    {
        $best = null;
        foreach ($candidates as $c) {
            $r = $this->bestRange($c);
            if ($r && ($best === null || $r['length'] > $best['length'])) $best = $r;
        }
        return $best;
    }

    private function sliceByRange(string $s, array $range, int $context = 60): string
    {
        $lenAll = mb_strlen($s, 'UTF-8');
        if ($lenAll === 0) return '';
        $start = max(0, (int)$range['start']);
        $len   = max(0, (int)$range['length']);
        $pre   = max(0, $start - $context);
        $post  = min($lenAll, $start + $len + $context);
        $frag  = mb_substr($s, $pre, $post - $pre, 'UTF-8');
        if ($pre  > 0) $frag  = '…'.$frag;
        if ($post < $lenAll) $frag .= '…';
        return $frag;
    }

    private function cleanHtmlSnippet(string $html): string
    {
        $html = preg_replace('#<(script|style|noscript)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $txt  = strip_tags($html);
        return $this->textSanitize($txt);
    }

    private function textSanitize(?string $txt): string
    {
        $txt = html_entity_decode((string)$txt, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        $lines = preg_split("/\r\n|\r|\n/u", $txt) ?: [$txt];
        $kill  = [
            'home','sign in','sign up','login','register','my books','genres','privacy','terms','about',
            'contact','copyright','menu','browse','community','account','join','help','categories',
            'tags','subscribe','rss','open in app','search','cookie','preferences','ad preferences'
        ];
        $kept = [];
        foreach ($lines as $line) {
            $l = trim(preg_replace('/\s{2,}/u', ' ', $line));
            if ($l === '') continue;
            $lower = mb_strtolower($l, 'UTF-8');
            $isShort = str_word_count($l) <= 4;
            $killHit = false;
            foreach ($kill as $kw) {
                if (mb_strpos($lower, $kw) !== false) { $killHit = true; break; }
            }
            if ($killHit && $isShort) continue;
            if (preg_match('#^\s*(home|menu|about|contact)\b#i', $l)) continue;
            $kept[] = $l;
        }
        $out = implode("\n", $kept);
        $out = preg_replace("/[ \t]{2,}/u", ' ', $out) ?? $out;
        $out = preg_replace("/\n{3,}/u", "\n\n", $out) ?? $out;
        $out = trim($out);
        if ($this->isBoilerplate($out)) return '';
        return $out;
    }

    // REMOVED: private function firstMeaningfulParagraph(string $html): string

    private function extractTitleFromHtml(?string $html): ?string
    {
        if (!$html) return null;

        // <title>
        if (preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m)) {
            $t = $this->textSanitize($m[1] ?? '');
            if ($t !== '') return $this->tidyTitle($t, null, null);
        }

        // OG / Twitter
        if (preg_match('#<meta[^>]+property=["\']og:title["\'][^>]*content=["\']([^"\']+)#i', $html, $m)) {
            $t = $this->textSanitize($m[1] ?? '');
            if ($t !== '') return $this->tidyTitle($t, null, null);
        }
        if (preg_match('#<meta[^>]+name=["\']twitter:title["\'][^>]*content=["\']([^"\']+)#i', $html, $m)) {
            $t = $this->textSanitize($m[1] ?? '');
            if ($t !== '') return $this->tidyTitle($t, null, null);
        }

        // First H1/H2
        if (preg_match('#<(h1|h2)[^>]*>(.*?)</\1>#is', $html, $m)) {
            $t = $this->textSanitize($m[2] ?? '');
            if ($t !== '') return $this->tidyTitle($t, null, null);
        }
        return null;
    }

    private function titleFromUrl(?string $url): ?string
    {
        if (!$url) return null;
        $p = @parse_url($url);
        if (!$p || empty($p['host'])) return null;
        $host = strtolower($p['host']);
        $path = trim((string) ($p['path'] ?? ''), '/');
        if ($path !== '') {
            $parts = array_values(array_filter(explode('/', $path)));
            $leaf  = preg_replace('#[-_]+#', ' ', end($parts));
            $leaf  = preg_replace('#\.html?$#i', '', $leaf);
            $leaf  = ucwords($leaf);
            return $leaf . ' – ' . $host;
        }
        return $host;
    }

    private function tidyTitle(?string $title, ?string $html = null, ?string $url = null): string
    {
        $t = $this->textSanitize((string)$title);
        $bad = ['home','login','sign in','sign up','quotes','community','profile','news & interviews'];
        if (mb_strlen($t, 'UTF-8') < 4 || in_array(mb_strtolower($t, 'UTF-8'), $bad, true)) {
            $t = '';
        }
        $t = preg_replace('/\s{2,}/u', ' ', $t) ?? $t;
        $t = trim($t, " \t\n\r\0\x0B-|»");
        if ($t === '' && $html) $t = (string) $this->extractTitleFromHtml($html);
        if ($t === '' && $url)  $t = (string) $this->titleFromUrl($url);
        if ($t === '') $t = 'External source';
        return Str::limit($t, 180);
    }

    private function htmlToPlainText(string $html): string
    {
        $html = preg_replace('/<img[^>]+src=["\']data:[^"\']+["\'][^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('#<(script|style|noscript)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
        $html = preg_replace('#<(?:p|div|section|article|header|footer|h[1-6]|li|br|hr)\b[^>]*>#i', "\n", $html) ?? $html;
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\r\n|\r|\n/u", "\n", $text) ?? $text;
        $text = preg_replace("/[^\P{C}\t\n]+/u", '', $text) ?? $text;
        $text = preg_replace("/[ \t]{2,}/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;
        $text = trim($text);

        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        $MAX = 1_200_000;
        if (mb_strlen($text, 'UTF-8') > $MAX) {
            $text = mb_substr($text, 0, $MAX, 'UTF-8');
        }
        return $text;
    }

    public function exportCrawled(Request $req, string $scanId)
    {
        $expected = (string) config('services.copyleaks.signing_secret');
        if (!$expected || $req->header('Authentication') !== $expected) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $payload = $req->json()->all();
        Log::channel('copyleaks')->info('Export crawledVersion payload', [
            'scanId'  => $scanId,
            'summary' => [
                'has_text' => isset($payload['text']['value']),
                'has_html' => isset($payload['html']['value']),
            ],
        ]);

        Cache::put(
            "copyleaks:crawled:{$scanId}",
            [
                'html'      => (string) ($payload['html']['value'] ?? ''),
                'text'      => (string) ($payload['text']['value'] ?? ''),
                'cached_at' => now()->toIso8601String(),
            ],
            now()->addHours(6)
        );

        // Try to replay pending result payloads (if any)
        $scan = \App\Models\ExternalPlagiarismScan::where('scan_id', $scanId)->first();
        if ($scan) {
            foreach ($this->drainPending($scanId) as $item) {
                try {
                    $this->processExportResult($scan, $item['payload'], $item['rid']);
                } catch (\Throwable $e) {
                    Log::channel('copyleaks')->warning('Replay failed', [
                        'scanId'=>$scanId,'rid'=>$item['rid'],'err'=>$e->getMessage()
                    ]);
                }
            }
            if ($scan->status !== 'error') $scan->update(['status' => 'exported']);
        }

        return response()->json(['ok' => true]);
    }

    // --- pending queue helpers (cache-backed) ---
    private function pendResult(string $scanId, string $resultId, array $payload): void {
        $listKey = "copyleaks:pending:list:{$scanId}";
        $itemKey = "copyleaks:pending:item:{$scanId}:{$resultId}";
        // maintain a small set of resultIds
        $list = Cache::get($listKey, []);
        if (!in_array($resultId, $list, true)) {
            $list[] = $resultId;
            Cache::put($listKey, $list, now()->addHours(6));
        }
        Cache::put($itemKey, $payload, now()->addHours(6));
    }

    private function drainPending(string $scanId): array {
        $listKey = "copyleaks:pending:list:{$scanId}";
        $list = Cache::pull($listKey, []);
        $items = [];
        foreach ($list as $rid) {
            $itemKey = "copyleaks:pending:item:{$scanId}:{$rid}";
            $p = Cache::pull($itemKey);
            if ($p) $items[] = ['rid'=>$rid, 'payload'=>$p];
        }
        return $items;
    }
}
