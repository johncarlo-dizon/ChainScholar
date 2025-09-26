<?php

namespace App\Http\Controllers;

use App\Models\BlockchainRequest;
use App\Models\Notification;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Http\Request;

class BlockchainRequestController extends Controller
{
    public function store(Request $req, ResearchPaper $paper)
    {
        $user = $req->user();
        // Admins don't need to request
        if ($user->isAdmin()) {
            return back()->with('info', 'Admins can register directly without a request.');
        }

        // Only owner, student, adviser may request
        if ($user->id !== $paper->user_id && !$user->isAdviser() && !$user->isStudent()) {
            abort(403);
        }

        // Prevent duplicate pending
        $hasPending = BlockchainRequest::where('paper_id', $paper->id)
            ->where('status', 'PENDING')->exists();
        if ($hasPending) {
            return back()->with('info', 'A pending blockchain request already exists for this paper.');
        }

        $br = BlockchainRequest::create([
            'paper_id'     => $paper->id,
            'requester_id' => $user->id,
            'type'         => 'REGISTER', // single-step request
            'status'       => 'PENDING',
        ]);

        // Notify all admins
        $adminIds = User::where('role','ADMIN')->pluck('id');
        foreach ($adminIds as $aid) {
            Notification::create([
                'user_id' => $aid,
                'title'   => 'Blockchain request received',
                'message' => "{$user->name} requested on-chain submission for: {$paper->title}",
            ]);
        }

        return back()->with('status', 'Request submitted. Admin will review it.');
    }

    public function approve(Request $req, BlockchainRequest $br)
    {
        $user = $req->user();
        abort_unless($user->isAdmin(), 403);

        if ($br->status !== 'PENDING') {
            return back()->with('info', 'This request has already been processed.');
        }

        $br->update([
            'status'   => 'APPROVED',
            'acted_by' => $user->id,
            'acted_at' => now(),
        ]);

        // Notify requester
        Notification::create([
            'user_id' => $br->requester_id,
            'title'   => 'Request approved',
            'message' => "Your blockchain request for '{$br->paper->title}' was approved. Registration is being processed.",
        ]);

        return back()->with('status', 'Request approved. Proceed to register on-chain.');
    }

    public function decline(Request $req, BlockchainRequest $br)
    {
        $user = $req->user();
        abort_unless($user->isAdmin(), 403);

        if ($br->status !== 'PENDING') {
            return back()->with('info', 'This request has already been processed.');
        }

        $data = $req->validate([
            'reason' => ['required','string','max:1000'],
        ]);

        $br->update([
            'status'   => 'REFUSED',
            'reason'   => $data['reason'],
            'acted_by' => $user->id,
            'acted_at' => now(),
        ]);

        Notification::create([
            'user_id' => $br->requester_id,
            'title'   => 'Request declined',
            'message' => "Your blockchain request for '{$br->paper->title}' was declined. Reason: {$data['reason']}",
        ]);

        return back()->with('status', 'Request declined.');
    }
}
