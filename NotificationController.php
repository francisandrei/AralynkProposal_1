<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $notes = Notification::where('user_id', $userId)->orderByDesc('id')->limit(60)->get();

        // opening the page marks them as read (the list above still shows which ones were new)
        Notification::where('user_id', $userId)->update(['is_read' => true]);

        return view('notifications', ['notes' => $notes]);
    }
}
