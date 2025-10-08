<?php

namespace App\Http\Controllers;

use App\Models\ResearchPaper;
use App\Models\Notification;
use App\Models\User;
use App\Services\ChainRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class BlockchainController extends Controller
{
    public function computeHash(ResearchPaper $paper)
    {
         abort_unless(auth()->user()?->isAdmin(), 403);



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

        return back()->with('status','SHA-256 computed.');
    }

    public function saveRegistration(Request $req, ResearchPaper $paper)
    {
          abort_unless(auth()->user()?->isAdmin(), 403);



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

// Notify paper owner
Notification::create([
    'user_id' => $paper->user_id,
    'title'   => 'On-chain registration saved',
    'message' => "Your paper '{$paper->title}' was registered on-chain. Awaiting confirmations.",
]);

return back()->with('status','Registration saved. Awaiting confirmation.');

    }

    public function confirm(ResearchPaper $paper, ChainRegistry $chain)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);


        $res = $chain->confirm($paper);
        if (!($res['ok'] ?? false)) {
            return back()->with('error', $res['error'] ?? 'Confirmation failed');
        }

       if ($res['confirmed'] ?? false) {
       // Notify owner
            Notification::create([
                'user_id' => $paper->user_id,
                'title'   => 'On-chain confirmation',
                'message' => "Your paper '{$paper->title}' is confirmed on-chain (block {$res['block']}).",
            ]);
            return back()->with('status', 'Transaction confirmed on-chain (block '.$res['block'].').');
        }
        if ($res['pending'] ?? false) {
            Notification::create([
                'user_id' => $paper->user_id,
                'title'   => 'On-chain pending',
                'message' => "Your paper '{$paper->title}' is still pending confirmation.",
            ]);
            return back()->with('status', 'Still pending… try again shortly.');
        }

        Notification::create([
            'user_id' => $paper->user_id,
            'title'   => 'On-chain failed',
            'message' => "Your paper '{$paper->title}' transaction failed on-chain.",
        ]);
        return back()->with('status', 'Transaction failed on-chain.');

    }
}
