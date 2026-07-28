<?php

namespace App\Http\Controllers;

use App\Models\BroadcastMessage;
use App\Models\Member;
use App\Models\User;
use App\Notifications\BroadcastNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BroadcastController extends Controller
{
    public function index()
    {
        $this->authorize('manage-users');

        $broadcasts = BroadcastMessage::with('sender')
            ->latest()
            ->paginate(15);

        return view('admin.broadcasts.index', compact('broadcasts'));
    }

    public function create()
    {
        $this->authorize('manage-users');

        $memberCount = Member::where('status', 'active')->count();

        return view('admin.broadcasts.create', compact('memberCount'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-users');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'category' => 'required|string|in:general,urgent,meeting,policy,financial,other',
            'priority' => 'required|string|in:normal,high,urgent',
        ]);

        $sender = auth()->user();
        $recipientsCount = 0;

        DB::transaction(function () use ($validated, $sender, &$recipientsCount) {
            $members = Member::where('status', 'active')
                ->whereNotNull('user_id')
                ->with('user')
                ->get();

            foreach ($members as $member) {
                if ($member->user) {
                    try {
                        $member->user->notify(
                            new BroadcastNotification(
                                $validated['title'],
                                $validated['body'],
                                $validated['category'],
                                $sender->name
                            )
                        );
                        $recipientsCount++;
                    } catch (\Exception $e) {
                        Log::error('Broadcast notification failed for user ' . $member->user_id . ': ' . $e->getMessage());
                    }
                }
            }

            BroadcastMessage::create([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'category' => $validated['category'],
                'priority' => $validated['priority'],
                'sent_by' => $sender->id,
                'recipients_count' => $recipientsCount,
            ]);
        });

        return redirect()
            ->route('admin.broadcasts.index')
            ->with('success', "Broadcast sent successfully to {$recipientsCount} member(s).");
    }
}
