<?php

namespace App\Http\Controllers;

use App\Models\ResearchPaper;
use App\Services\ChainRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BlockchainController extends Controller
{
    public function computeHash(ResearchPaper $paper)
    {
        if (auth()->id() !== $paper->user_id && !auth()->user()?->isAdmin()) {
    abort(403);
}


        // Compute sha256 from stored file
        $disk = $paper->file_disk ?: 'public';
        if (!$paper->file_path || !Storage::disk($disk)->exists($paper->file_path)) {
            return back()->with('error','File not found.');
        }
        $stream = Storage::disk($disk)->readStream($paper->file_path);
        $hashCtx = hash_init('sha256');
        while (!feof($stream)) {
            hash_update($hashCtx, fread($stream, 8192));
        }
        fclose($stream);
        $paper->sha256 = hash_final($hashCtx);
        $paper->chain_status = ResearchPaper::STATUS_UPLOADED;
        $paper->save();

        return back()->with('success','SHA-256 computed.');
    }

    public function saveRegistration(Request $req, ResearchPaper $paper)
    {
          if (auth()->id() !== $paper->user_id && !auth()->user()?->isAdmin()) {
        abort(403);
    }


        $data = $req->validate([
            'wallet'   => ['required','regex:/^0x[a-fA-F0-9]{40}$/'],
            'tx_hash'  => ['required','regex:/^0x[a-fA-F0-9]{64}$/'],
            'chain_id' => ['required','integer'],
        ]);

        $paper->fill([
            'wallet'      => strtolower($data['wallet']),
            'tx_hash'     => strtolower($data['tx_hash']),
            'chain_id'    => $data['chain_id'],
            'chain_status'=> ResearchPaper::STATUS_REGISTERED,
        ])->save();

        return back()->with('success','Registration saved. Awaiting confirmation.');
    }

    public function confirm(ResearchPaper $paper, ChainRegistry $chain)
    {
     if (auth()->id() !== $paper->user_id && !auth()->user()?->isAdmin()) {
    abort(403);
}

        $res = $chain->confirm($paper);
        if (!($res['ok'] ?? false)) {
            return back()->with('error', $res['error'] ?? 'Confirmation failed');
        }

        if ($res['confirmed'] ?? false) {
            return back()->with('success', 'Transaction confirmed on-chain (block '.$res['block'].').');
        }
        if ($res['pending'] ?? false) {
            return back()->with('info', 'Still pending… try again shortly.');
        }

        return back()->with('error', 'Transaction failed on-chain.');
    }
}
