<?php

namespace App\Services;

use App\Models\ResearchPaper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChainRegistry
{
    /** Map chainId => RPC endpoint (override via .env) */
    public static function rpcMap(): array
    {
        return [
            80002     => config('services.rpc.80002', env('RPC_80002', 'https://rpc-amoy.polygon.technology')),
            11155111  => config('services.rpc.11155111', env('RPC_SEPOLIA')), // set in .env
            // add more as needed…
        ];
    }

    public function confirm(ResearchPaper $paper): array
    {
        if (!$paper->tx_hash || !$paper->chain_id) {
            return ['ok' => false, 'error' => 'Missing tx_hash/chain_id'];
        }
        $rpc = self::rpcMap()[$paper->chain_id] ?? null;
        if (!$rpc) return ['ok'=>false,'error'=>'Unsupported chain_id'];

        // Basic format checks
        if (!Str::of($paper->tx_hash)->startsWith('0x') || strlen($paper->tx_hash) !== 66) {
            return ['ok'=>false,'error'=>'Invalid tx hash format'];
        }

        // JSON-RPC call: eth_getTransactionReceipt
        $payload = [
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'eth_getTransactionReceipt',
            'params'  => [$paper->tx_hash],
        ];

        $res = Http::withHeaders(['Content-Type'=>'application/json'])
            ->timeout(15)
            ->post($rpc, $payload);

        if (!$res->ok()) return ['ok'=>false,'error'=>'RPC request failed'];

        $body = $res->json();
        $rcpt = $body['result'] ?? null;
        if (!$rcpt) {
            // Not mined yet
            $paper->chain_status = ResearchPaper::STATUS_REGISTERED;
            $paper->save();
            return ['ok'=>true,'pending'=>true];
        }

        $statusHex = $rcpt['status'] ?? '0x0';
        $blockHex  = $rcpt['blockNumber'] ?? null;
        $success   = ($statusHex === '0x1');

        if ($success) {
            $paper->chain_status = ResearchPaper::STATUS_CONFIRMED;
            $paper->block_number = $blockHex ? hexdec($blockHex) : null;
            $paper->confirmed_at = now();
        } else {
            $paper->chain_status = ResearchPaper::STATUS_FAILED;
        }
        $paper->save();

        return ['ok'=>true,'confirmed'=>$success, 'block'=>$paper->block_number];
    }
}
