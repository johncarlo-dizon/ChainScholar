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
        if (\App\Models\ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)->where('export_key', $exportKey)->exists()) {
            return; // already processed this exact payload
        }

        // ----- compute % score for this specific source -----
        $pct = 0;
        if (isset($payload['score']['aggregatedScore']) && is_numeric($payload['score']['aggregatedScore'])) {
            $pct = (int) round((float) $payload['score']['aggregatedScore']);
        } elseif (isset($payload['matchedWords'], $payload['totalWords']) && (int) $payload['totalWords'] > 0) {
            $pct = (int) round(((float) $payload['matchedWords'] * 100.0) / (float) $payload['totalWords']);
        } else {
            $raw = $scan->raw_payload;
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?: [];
            }
            $docTotal = (int) ($raw['scannedDocument']['totalWords'] ?? 0);

            $ident   = (int)($payload['statistics']['identical']          ?? $payload['statistics']['identicalWords']       ?? 0);
            $minor   = (int)($payload['statistics']['minorChanges']        ?? $payload['statistics']['minorChangedWords']    ?? 0);
            $related = (int)($payload['statistics']['relatedMeaning']      ?? $payload['statistics']['relatedMeaningWords']  ?? 0);
            $mw      = $ident + $minor + $related;

            if ($docTotal > 0 && $mw > 0) {
                $pct = (int) round(($mw * 100.0) / max(1, $docTotal));
            }
        }
        if ($pct <= 0) return;

        // ----- load your crawled version (for excerpt generation) -----
        $crawled     = \Illuminate\Support\Facades\Cache::get("copyleaks:crawled:{$scan->scan_id}", ['html' => '', 'text' => '']);
        $htmlFullYou = (string) ($crawled['html'] ?? '');
        $textFullYou = (string) ($crawled['text'] ?? '');

        // ----- choose an excerpt from "your" doc -----
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
            [$fragYour] = $this->findBestFragmentPair($payload);
            if ($fragYour) $yourExcerpt = $fragYour;
        }
        $yourExcerpt = \Illuminate\Support\Str::limit($this->textSanitize($yourExcerpt ?? ''), 400);
        if ($yourExcerpt === '') {
            $fallback = $textFullYou !== '' ? $textFullYou : strip_tags($htmlFullYou);
            $fallback = $this->textSanitize(mb_substr($fallback, 0, 400, 'UTF-8'));
            $yourExcerpt = $fallback ?: '(excerpt unavailable)';
        }

        // ----- robust URL/title resolution (use raw webhook by resultId if export payload is sparse) -----
        $htmlFullSrc    = (string) ($payload['html']['value'] ?? '');
        $sourceUrlRaw   = $payload['source']['url']   ?? ($payload['url']   ?? null);
        $sourceTitleRaw = $payload['source']['title'] ?? ($payload['title'] ?? null);

        if (!$sourceUrlRaw || !$sourceTitleRaw) {
            $meta = $this->lookupRawResultMeta($scan, $resultId); // ← pulls from scan->raw_payload by id
            $sourceUrlRaw   = $sourceUrlRaw   ?: ($meta['url']   ?? null);
            $sourceTitleRaw = $sourceTitleRaw ?: ($meta['title'] ?? null);
        }
        if (!$sourceUrlRaw)  $sourceUrlRaw  = $this->inferUrlFromHtml($htmlFullSrc);
        $normUrl = $this->normalizeUrl($sourceUrlRaw);
        $useUrl  = $normUrl ?: $this->safeRawUrl($sourceUrlRaw);

        if (!is_string($sourceTitleRaw) || trim($sourceTitleRaw) === '') {
            $sourceTitleRaw = $this->extractTitleFromHtml($htmlFullSrc) ?: $this->titleFromUrl($useUrl);
        }
        $sourceTitle = $this->tidyTitle($sourceTitleRaw, $htmlFullSrc, $useUrl);

        // ----- merging precedence: (1) same resultId → (2) same URL → (3) same title -----
        $updated = false;

        // (1) Merge any pre-existing row that has the same resultId in export_key
        $existingByRid = \App\Models\ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)
            ->where('export_key', 'like', 'rid:' . $resultId . '|%')
            ->first();

        if ($existingByRid) {
            $existingByRid->update([
                'percent'      => max((int) $existingByRid->percent, (int) $pct),
                'source_title' => $sourceTitle ?: ($existingByRid->source_title ?: 'External source'),
                'source_url'   => $useUrl ?: $existingByRid->source_url,
                'your_excerpt' => $yourExcerpt ?: $existingByRid->your_excerpt,
                'export_key'   => $exportKey,
            ]);
            $updated = true;
        }

        // (2) Merge by normalized URL
        if (!$updated && $normUrl) {
            $existing = \App\Models\ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)
                ->get()
                ->first(function ($r) use ($normUrl) {
                    $n = $this->normalizeUrl($r->source_url);
                    return $n && $n === $normUrl;
                });
            if ($existing) {
                $existing->update([
                    'percent'      => max((int) $existing->percent, (int) $pct),
                    'source_title' => $sourceTitle ?: ($existing->source_title ?: 'External source'),
                    'source_url'   => $useUrl ?: $existing->source_url,
                    'your_excerpt' => $yourExcerpt ?: $existing->your_excerpt,
                    'export_key'   => $exportKey,
                ]);
                $updated = true;
            }
        }

        // (3) Merge by non-generic title
        if (!$updated) {
            $normTitle = mb_strtolower($this->tidyTitle($sourceTitle, null, $useUrl), 'UTF-8');
            if ($normTitle !== 'external source') {
                $existing = \App\Models\ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)
                    ->get()
                    ->first(function ($r) use ($normTitle) {
                        $t = mb_strtolower($this->tidyTitle($r->source_title ?? '', null, $r->source_url ?? null), 'UTF-8');
                        return $t !== 'external source' && $t === $normTitle;
                    });
                if ($existing) {
                    $existing->update([
                        'percent'      => max((int) $existing->percent, (int) $pct),
                        'source_title' => $sourceTitle ?: $existing->source_title,
                        'source_url'   => $useUrl ?: $existing->source_url,
                        'your_excerpt' => $yourExcerpt ?: $existing->your_excerpt,
                        'export_key'   => $exportKey,
                    ]);
                    $updated = true;
                }
            }
        }

        // Create if nothing matched
        if (!$updated) {
            \App\Models\ExternalPlagiarismMatch::create([
                'scan_id_fk'   => $scan->id,
                'document_id'  => $scan->document_id,
                'percent'      => (int) $pct,
                'source_title' => $sourceTitle ?: 'External source',
                'source_url'   => $useUrl ?: null,
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


    private function resultIdFromExportKey(?string $ek): ?string
    {
        if (!is_string($ek)) return null;
        if (preg_match('/^rid:([^|]+)\|/i', $ek, $m)) {
            return (string) $m[1];
        }
        return null;
    }



    private function lookupRawResultMeta(ExternalPlagiarismScan $scan, string $resultId): array
    {
        $raw = $scan->raw_payload;
        if (is_string($raw)) $raw = json_decode($raw, true) ?: [];
        $buckets = $raw['results'] ?? ($raw['data']['results'] ?? []);
        foreach (['internet','database','repositories','batch'] as $bucket) {
            foreach (($buckets[$bucket] ?? []) as $r) {
                if (!empty($r['id']) && (string)$r['id'] === (string)$resultId) {
                    $url = $r['url'] ?? ($r['metadata']['finalUrl'] ?? ($r['source']['url'] ?? null));
                    $title = $r['title'] ?? ($r['source']['title'] ?? null);
                    return [
                        'url'   => $this->normalizeUrl($url) ?: $this->safeRawUrl($url),
                        'title' => $this->tidyTitle($title ?? '', null, $url),
                    ];
                }
            }
        }
        return [];
    }


    /* ------------------------ export completed ------------------------ */

    public function exportCompleted(Request $req, string $scanId, string $exportId)
    {
        $expected = (string) config('services.copyleaks.signing_secret');
        if (!$expected || $req->header('Authentication') !== $expected) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        Log::channel('copyleaks')->info('Copyleaks export completed webhook', [
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

    public function status(Request $req, \App\Models\Document $document)
{
    $scanId = $req->query('scan_id');
    $q = \App\Models\ExternalPlagiarismScan::where('document_id', $document->id);

    $scan = $scanId
        ? (clone $q)->where('scan_id', $scanId)->first()
        : (function() use ($q) {
            $latestRunning = (clone $q)->whereIn('status', ['queued','running'])
                ->orderByDesc('updated_at')->orderByDesc('id')->first();
            $latestTerminal = (clone $q)->whereIn('status', ['completed','exported'])
                ->orderByDesc('updated_at')->orderByDesc('id')->first();
            $latestAny = (clone $q)->orderByDesc('updated_at')->orderByDesc('id')->first();
            $isFreshRunning = $latestRunning
                ? optional($latestRunning->updated_at)->gt(now()->subMinutes(10))
                : false;
            if ($isFreshRunning) return $latestRunning;
            if ($latestTerminal) return $latestTerminal;
            return $latestAny;
        })();

    if (!$scan) return response()->json(['status' => 'none']);

    // Decode raw once for backfill lookups
    $raw = $scan->raw_payload;
    if (is_string($raw)) $raw = json_decode($raw, true) ?: [];

    // Pull all rows, then repair any that are missing URL/title using export_key (resultId)
    $rows = \App\Models\ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)
        ->orderByDesc('percent')
        ->limit(800)
        ->get();

    foreach ($rows as $r) {
        $urlNorm = $this->normalizeUrl($r->source_url) ?: null;
        $titleClean = $this->tidyTitle($r->source_title ?? '', null, $urlNorm);

        $needsUrl   = !$urlNorm;
        $needsTitle = ($titleClean === '' || mb_strtolower($titleClean, 'UTF-8') === 'external source');

        if ($needsUrl || $needsTitle) {
            // Prefer exact backfill by resultId from export_key
            $rid = $this->resultIdFromExportKey($r->export_key ?? null);
            $meta = $rid ? $this->lookupRawResultMeta($scan, $rid) : [];

            $newUrl   = $urlNorm ?: ($meta['url']   ?? null);
            $newTitle = (!$needsTitle ? $titleClean : ($meta['title'] ?? ''));
            if (!$newTitle || mb_strtolower($newTitle,'UTF-8') === 'external source') {
                // Secondary fallback: find by title in raw (if we had some title at all)
                $fb = $this->fallbackUrlFromRawByTitle((array)$raw, $titleClean);
                $newUrl = $newUrl ?: $fb;
                if (!$newTitle && $newUrl) $newTitle = $this->tidyTitle('', null, $newUrl);
            }

            // Persist best-effort backfill to DB (so future loads don't need to repair again)
            $toUpdate = [];
            if ($newUrl && !$urlNorm) {
                $toUpdate['source_url'] = $newUrl;
                $urlNorm = $newUrl;
            }
            if ($newTitle && ($needsTitle || $r->source_title === null)) {
                $toUpdate['source_title'] = $newTitle;
                $titleClean = $newTitle;
            }
            if ($toUpdate) {
                try { $r->update($toUpdate); } catch (\Throwable $e) {}
            }
        }
    }

    // Now build the deduped list (by normalized URL, else by normalized non-generic title)
    $byKey = [];
    foreach ($rows as $r) {
        $url = $this->normalizeUrl($r->source_url) ?: null;
        $titleClean = $this->tidyTitle($r->source_title ?? '', null, $url);

        $key = $url
            ? ('u:' . $url)
            : (mb_strtolower($titleClean,'UTF-8')!=='external source'
                ? ('t:' . mb_strtolower($titleClean,'UTF-8'))
                : ('x:' . ($r->id ?? spl_object_id($r))));

        if (!isset($byKey[$key]) || (int)$r->percent > (int)$byKey[$key]['percent']) {
            $byKey[$key] = [
                'percent'      => (int)$r->percent,
                'source_title' => $titleClean ?: 'External source',
                'source_url'   => $url,
            ];
        }
    }

    $list = collect(array_values($byKey))
        ->sortByDesc(fn($x) => (int)$x['percent'])
        ->values();

    // metrics + top excerpt
    $docAgg    = (int) $this->extractAggregatedScore((array) $raw);
    $sourceMax = (int) (\App\Models\ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)->max('percent') ?? 0);

    $topRow = \App\Models\ExternalPlagiarismMatch::where('scan_id_fk', $scan->id)
        ->orderByDesc('percent')->first();

    $topExcerpt = $topRow?->your_excerpt ? $this->textSanitize($topRow->your_excerpt) : null;
    $topMeta = $topRow ? [
        'percent'      => (int) $topRow->percent,
        'source_title' => $this->tidyTitle($topRow->source_title ?? '', null, $topRow->source_url ?? null),
        'source_url'   => $topRow->source_url,
    ] : null;

    $docTotalWords = (int)($raw['scannedDocument']['totalWords']
        ?? ($raw['data']['scannedDocument']['totalWords'] ?? 0));

    return response()->json([
        'status'              => $scan->status,
        'scan_id'             => $scan->scan_id,
        'updated_at_iso'      => optional($scan->updated_at)->toIso8601String(),
        'sandbox'             => (bool) $scan->sandbox,
        'credits_used'        => (int) ($scan->credits_used ?? 0),

        'source_max'          => $sourceMax,
        'doc_aggregated'      => $docAgg,

        'plagiarized_excerpt' => $topExcerpt,
        'top_source'          => $topMeta,

        'matches'             => $list,                  // ← all, with backfilled titles/links
        'matches_count'       => $list->count(),

        'doc_total_words'     => $docTotalWords,
        'error'               => $scan->error_message,
    ]);
}






    private function fallbackUrlFromRawByTitle(array $raw, string $title): ?string
    {
        // Robust title normalizer with suffix trimming (e.g., " | Request PDF")
        $norm = function ($s) {
            $s = html_entity_decode((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $s = preg_replace('/\s+/u', ' ', trim($s)) ?? $s;

            // Trim obvious site/CTA suffixes after separators
            $parts = preg_split('/\s*(?:\||–|—|-|•)\s*/u', $s);
            if ($parts && count($parts) > 1) {
                $last = mb_strtolower(end($parts), 'UTF-8');
                if (preg_match('/(request\s*pdf|download|researchgate|semantic\s*scholar|springer|elsevier|mdpi|sciencedirect)/u', $last)) {
                    $s = $parts[0];
                }
            }

            // Lowercase and strip punctuation for fuzzy compare
            $s = mb_strtolower($s, 'UTF-8');
            $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s) ?? $s;
            $s = preg_replace('/\s+/u', ' ', trim($s)) ?? $s;
            return $s;
        };

        $want = $norm($title);
        if ($want === '') return null;

        $buckets = $raw['results'] ?? ($raw['data']['results'] ?? []);
        foreach (['internet','database','repositories','batch'] as $bucket) {
            $arr = $buckets[$bucket] ?? [];
            if (!is_array($arr)) continue;

            foreach ($arr as $r) {
                $t = $r['title'] ?? ($r['source']['title'] ?? null);
                $urlRaw =
                    $r['url']
                    ?? ($r['metadata']['finalUrl'] ?? null)
                    ?? ($r['source']['finalUrl'] ?? null)
                    ?? ($r['source']['url'] ?? null);

                if (!$t || !$urlRaw) continue;

                $cand = $norm($t);
                if ($cand === '') continue;

                $match =
                    $cand === $want ||
                    str_contains($cand, $want) || str_contains($want, $cand);

                if (!$match) {
                    // similarity fallback (≈ Levenshtein-based percentage)
                    $p = 0.0; similar_text($cand, $want, $p);
                    $match = ($p >= 85.0);
                }

                if ($match) {
                    // Prefer keeping full raw (incl. query) when safe; else normalized
                    $u = $this->safeRawUrl($urlRaw) ?: $this->normalizeUrl($urlRaw);
                    if ($u) return $u;
                }
            }
        }
        return null;
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

                // keep zeros out only
                if ($pct <= 0) continue;

                // URL + title (unproxy). Prefer normalized; fall back to safe raw.
                $urlRaw  = $m['url'] ?? ($m['metadata']['finalUrl'] ?? ($m['source']['url'] ?? null));
                $normUrl = $this->normalizeUrl($urlRaw);
                $useUrl  = $normUrl ?: $this->safeRawUrl($urlRaw);

                $titleRaw = $m['title'] ?? ($m['source']['title'] ?? ($useUrl ?: 'External source'));
                $title    = is_string($titleRaw) ? $this->tidyTitle($titleRaw, null, $useUrl) : 'External source';

                // Your preview only (no source excerpt)
                $yourPreview = $this->textSanitize($m['text']['value'] ?? ($m['preview']['text'] ?? null));

                $rows[] = [
                    'percent'      => $pct,
                    'source_title' => \Illuminate\Support\Str::limit($title, 300),
                    'source_url'   => $useUrl ?: null,
                    'your_excerpt' => $yourPreview,
                ];
            }
        }

        // De-dupe by URL (highest % wins). If no URL, de-dupe by title hash.
        $by = [];
        foreach ($rows as $r) {
            $key = $r['source_url'] ?: ('t:' . md5((string)($r['source_title'] ?? '')));
            if (!isset($by[$key]) || $r['percent'] > $by[$key]['percent']) {
                $by[$key] = $r;
            }
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

        if (str_ends_with($host, 'copyleaks.com')) {
            // If viewer has a real ?url= param, unwrap it
            if (!empty($p['query'])) {
                parse_str($p['query'], $q);
                if (!empty($q['url'])) {
                    return $q['url'];
                }
            }
            // If it’s ONLY the sandbox viewer with no target → drop
            return null;
        }

        // For everything else (ResearchGate, SMEOR, etc.) → keep as-is
        return $url;
    }



   private function normalizeUrl(?string $url): ?string
{
    if (!$url) return null;

    $url = $this->unwrapCopyleaksUrl($url);
    if ($url === null) return null; // only null for sandbox viewer

    $url = html_entity_decode(trim((string)$url), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (str_starts_with($url, '//')) {
        $url = 'https:' . $url;
    }
    if (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }

    $p = @parse_url($url);
    if (!$p || empty($p['host'])) {
        return null;
    }

    $scheme = strtolower($p['scheme'] ?? 'https');
    $host   = strtolower($p['host']);
    $path   = isset($p['path']) ? rtrim($p['path'], '/') : '';

    return $scheme . '://' . $host . $path;
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

    private function getCrawledTextFromScan(\App\Models\ExternalPlagiarismScan $scan): ?string
    {
        // 1) Prefer cache populated by exportCrawled()
        $cached = \Illuminate\Support\Facades\Cache::get("copyleaks:crawled:{$scan->scan_id}");
        $html   = is_array($cached) ? (string)($cached['html'] ?? '') : '';
        $text   = is_array($cached) ? (string)($cached['text'] ?? '') : '';

        // 2) Fallback to raw webhook payload (various shapes)
        if ($text === '' && $html === '') {
            $raw = $scan->raw_payload;
            if (is_string($raw)) $raw = json_decode($raw, true) ?: [];

            // Try text first
            $text = (string) (
                $raw['text']['value']
                ?? ($raw['data']['text']['value'] ?? '')
                ?? ($raw['scannedDocument']['text']['value'] ?? '')
                ?? ($raw['data']['scannedDocument']['text']['value'] ?? '')
            );

            // Try html if still empty
            if ($text === '') {
                $html = (string) (
                    $raw['html']['value']
                    ?? ($raw['data']['html']['value'] ?? '')
                    ?? ($raw['scannedDocument']['html']['value'] ?? '')
                    ?? ($raw['data']['scannedDocument']['html']['value'] ?? '')
                );
            }
        }

        // If only HTML present, strip it to text
        if ($text === '' && $html !== '') {
            $html = preg_replace('#<(script|style|noscript)\b[^>]*>.*?</\1>#is', ' ', $html) ?? $html;
            $text = strip_tags($html);
        }

        // Clean
        $text = html_entity_decode($text ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim($text);
        if ($text === '') return null;

        // Normalize whitespace & kill obvious boilerplate via your sanitizer
        $text = preg_replace("/\r\n|\r|\n/u", "\n", $text) ?? $text;
        $text = preg_replace("/[ \t]{2,}/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;
        $text = $this->textSanitize($text);

        // Cap size defensively
        $MAX = 2000; // keep this small – just for status preview fallback
        if (mb_strlen($text, 'UTF-8') > $MAX) {
            $text = mb_substr($text, 0, $MAX, 'UTF-8') . '…';
        }

        // Ensure valid UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        return $text !== '' ? $text : null;
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

    private function safeRawUrl(?string $url): ?string
    {
        if (!$url) return null;

        // Unwrap Copyleaks viewer first. If it’s a pure viewer (no target), returns null.
        $u = $this->unwrapCopyleaksUrl($url);
        if ($u === null) return null;

        // Trim/HTML-decode and accept as-is if it has a host.
        $u = html_entity_decode(trim((string)$u), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Support protocol-relative //host/path
        if (str_starts_with($u, '//')) $u = 'https:' . $u;

        // Add scheme if it looks host/path only
        if (!preg_match('~^[a-z][a-z0-9+.-]*://~i', $u)) {
            $u = 'https://' . ltrim($u, '/');
        }

        $p = @parse_url($u);
        if (!$p || empty($p['host'])) return null;

        // Block Copyleaks viewer domains at this stage too.
        $host = strtolower($p['host']);
        if (str_ends_with($host, 'copyleaks.com')) return null;

        return $u; // keep raw (may include query/fragment) — good for “Request PDF” pages
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
    // Start from what we have
    $t = $this->textSanitize((string) $title);

    // If empty, try the HTML <title> / og:title
    if ($t === '' && $html) {
        $t = (string) $this->extractTitleFromHtml($html);
    }

    // Strip noisy prefixes/suffixes like "(PDF) " and " | Request PDF" / " – ResearchGate"
    $t = $this->stripTitleAffixes($t);

    // If still empty, derive from URL path
    if ($t === '' && $url) {
        $t = (string) $this->titleFromUrl($url);
    }

    // Guard rails
    if ($t === '' || mb_strlen($t, 'UTF-8') < 4) {
        $t = 'External source';
    }

    return \Illuminate\Support\Str::limit($t, 180);
}

/**
 * Remove common provider/CTA affixes from titles, and leading markers like "(PDF)".
 */
private function stripTitleAffixes(string $t): string
{
    if ($t === '') return '';
    $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $t = preg_replace('/\s+/u', ' ', trim($t)) ?? $t;

    // Drop trivial boilerplate titles entirely
    $bad = ['home','login','sign in','sign up','quotes','community','profile','news & interviews'];
    if (in_array(mb_strtolower($t, 'UTF-8'), $bad, true)) return '';

    // Remove leading markers like "(PDF) ", "(Article) – ", etc.
    $t = preg_replace('/^\s*\(?(?:pdf|article|preprint|chapter|book|thesis)\)?\s*(?:[:\-–—])?\s*/iu', '', $t) ?? $t;

    // Remove trailing provider / CTA suffixes. Repeat until stable.
    $suffixRe = '/\s*(?:[\|\-–—•]\s*)?'
        .'(?:request\s*pdf|researchgate|springer(?:link)?|springer\s*nature|elsevier|'
        .'science(?:direct)?|mdpi|wiley(?:\s*online\s*library)?|taylor\s*&\s*francis(?:\s*online)?|'
        .'sage\s*journals|ieee(?:\s*xplore)?|acm(?:\s*digital\s*library)?|jstor|ssrn|academia\.edu|'
        .'semantic\s*scholar|scopus|web\s*of\s*science|frontiers|hindawi|pubmed|ncbi)'
        .'\s*$/iu';
    $prev = null;
    while ($prev !== $t) {
        $prev = $t;
        $t = preg_replace($suffixRe, '', $t) ?? $t;
    }

    // Trim leftover separators at the ends
    $t = trim($t, " \t\n\r\0\x0B-|•–—");
    $t = preg_replace('/\s{2,}/u', ' ', $t) ?? $t;

    return trim($t);
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
