<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

// Inbox-style messages between students and businesses (refresh the page to see new messages).
class MessageController extends Controller
{
    // May I message this user?
    // A student may message an active business. A business may message only students who applied to its jobs.
    private function canMessage($me, $otherId)
    {
        if ($me->role == 'student') {
            return User::where('id', $otherId)->where('role', 'business')->where('status', 'active')->exists();
        }

        return Application::whereHas('student', function ($q) use ($otherId) {
            $q->where('user_id', $otherId);
        })->whereHas('jobPost.businessProfile', function ($q) use ($me) {
            $q->where('user_id', $me->id);
        })->exists();
    }

    public function index(Request $request)
    {
        $me = $request->user();
        $withId = (int) $request->input('with');
        $jobId = (int) $request->input('job');

        // ----- The list of people I talked to (newest conversation first) -----
        $all = Message::where('sender_id', $me->id)->orWhere('receiver_id', $me->id)->orderByDesc('id')->get();
        $people = [];
        foreach ($all as $message) {
            $otherId = ($message->sender_id == $me->id) ? $message->receiver_id : $message->sender_id;
            if (!isset($people[$otherId])) {
                $people[$otherId] = ['id' => $otherId, 'last' => $message->created_at, 'unread' => 0];
            }
            if ($message->receiver_id == $me->id && !$message->is_read) {
                $people[$otherId]['unread']++;
            }
        }

        // ----- The open conversation -----
        $other = null;
        $thread = collect();
        if ($withId > 0) {
            $other = User::findOrFail($withId);
            $hadMessages = isset($people[$withId]);
            if (!$this->canMessage($me, $withId) && !$hadMessages) {
                return redirect()->route('messages')->with('danger', 'You cannot open this conversation.');
            }

            // mark their messages to me as read
            Message::where('receiver_id', $me->id)->where('sender_id', $withId)->update(['is_read' => true]);

            $thread = Message::with('jobPost')
                ->where(function ($q) use ($me, $withId) {
                    $q->where('sender_id', $me->id)->where('receiver_id', $withId);
                })
                ->orWhere(function ($q) use ($me, $withId) {
                    $q->where('sender_id', $withId)->where('receiver_id', $me->id);
                })
                ->orderBy('id')->get();
        }

        // names of everyone in the list
        $names = User::whereIn('id', array_keys($people))->pluck('name', 'id');

        return view('messages', [
            'people' => $people,
            'names' => $names,
            'other' => $other,
            'withId' => $withId,
            'jobId' => $jobId,
            'thread' => $thread,
        ]);
    }

    public function store(Request $request)
    {
        $me = $request->user();
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'job_post_id' => 'nullable|integer',
            'body' => 'required|string|max:2000',
        ]);

        if (!$this->canMessage($me, $data['receiver_id'])) {
            return redirect()->route('messages')->with('danger', 'You cannot message this user.');
        }

        Message::create([
            'sender_id' => $me->id,
            'receiver_id' => $data['receiver_id'],
            'job_post_id' => $data['job_post_id'] ?: null,
            'body' => $data['body'],
        ]);
        Notification::send($data['receiver_id'], 'message', 'New message from ' . $me->name, 'messages?with=' . $me->id);

        return redirect()->route('messages', ['with' => $data['receiver_id'], 'job' => $data['job_post_id']]);
    }
}
