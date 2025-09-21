<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CopyleaksClient
{
    public function getAccessToken(): string
    {
        $res = Http::asJson()
            ->post('https://id.copyleaks.com/v3/account/login/api', [
                'email' => config('services.copyleaks.email'),
                'key'   => config('services.copyleaks.key'),
            ])
            ->throw()
            ->json();

        return $res['access_token'] ?? '';
    }

    public function newScanId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Submit plain text as a base64 file.
     * Uses PUT with explicit {scanId} (idempotent) and includes:
     * - properties.sandbox   (from config)
     * - webhooks[] with custom header for verification
     */
    public function submitTextScan(string $token, string $scanId, string $textUtf8, string $statusWebhook): void
    {
        $sandbox = (bool) config('services.copyleaks.sandbox', true);
        $secret  = (string) config('services.copyleaks.signing_secret');

        $payload = [
            'base64'   => base64_encode($textUtf8),
            'filename' => 'document.txt',
            'properties' => [
                'sandbox'         => $sandbox,
                'includeHtml'     => true,
                // 👇 This ensures webhook payloads carry your scan id even if other fields are missing
                'developerPayload'=> $scanId,
                'webhooks'        => [
                    'status'            => rtrim($statusWebhook, '/') . '/{STATUS}',
                    'statusHeaders'     => [['Authentication', $secret]],
                    // incremental updates without suffix
                    'newResult'         => rtrim($statusWebhook, '/'),
                    'newResultHeaders'  => [['Authentication', $secret]],
                ],
            ],
        ];

        Http::withToken($token)
            ->asJson()
            ->withHeaders(['Accept' => 'application/json'])
            ->put("https://api.copyleaks.com/v3/scans/submit/file/{$scanId}", $payload)
            ->throw();
    }



    /**
     * Optional export step to receive full artifacts via push.
     */
// App/Services/CopyleaksClient.php

public function requestExport(
        string $token,
        string $scanId,
        array $resultIds,
        string $resultEndpointBase,
        string $completionEndpoint // may include "__AUTO__"
    ): string {
        $exportId = (string) Str::uuid();
        $secret   = (string) config('services.copyleaks.signing_secret');

        if ($completionEndpoint === '__AUTO__' || str_contains($completionEndpoint, '/__AUTO__')) {
            $base = rtrim($resultEndpointBase, '/');
            $completionEndpoint = $base . "/webhooks/copyleaks/export/completed/{$scanId}/{$exportId}";
        }

        $results = [];
        foreach ($resultIds as $rid) {
            $results[] = [
                'id'       => (string) $rid,
                'verb'     => 'POST',
                'headers'  => [ ['Authentication', $secret] ],
                'endpoint' => rtrim($resultEndpointBase,'/') . "/webhooks/copyleaks/export/result/{$scanId}/{$rid}",
                // ask for multiple formats so we can compute nicer snippets
                'formats'  => ['comparison'],

            ];
        }

        // 🔥 NEW: ask Copyleaks to also export the crawled version (YOUR original)
        $crawledVersion = [
            'endpoint' => rtrim($resultEndpointBase,'/') . "/webhooks/copyleaks/export/crawled/{$scanId}",
            'verb'     => 'POST',
            'headers'  => [ ['Authentication', $secret] ],
            // formats field is optional in docs; keep minimal payload
        ];

        $payload = [
            'results'                   => $results,
            'crawledVersion'            => $crawledVersion,
            'completionWebhook'         => $completionEndpoint,
            'completionWebhookHeaders'  => [ ['Authentication', $secret] ],
            'maxRetries'                => 3,
        ];

        Http::withToken($token)
            ->asJson()
            ->post("https://api.copyleaks.com/v3/downloads/{$scanId}/export/{$exportId}", $payload)
            ->throw();

        return $exportId;
    }


    // Insert BELOW this line: "return $exportId;" from requestExport() or anywhere inside the class
        public function resendWebhook(string $token, string $scanId): void
        {
            // Ask Copyleaks to resend the *completed* status webhook for this scan.
            // No body required.
            Http::withToken($token)
                ->asJson()
                ->post("https://api.copyleaks.com/v3/scans/resend-webhook/{$scanId}")
                ->throw();
        }



}
