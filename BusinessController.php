<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function index()
    {
        // pending businesses (waiting for approval) are listed first
        $businesses = User::where('role', 'business')
            ->with(['businessProfile.category', 'businessProfile.jobPosts'])
            ->orderByRaw("FIELD(status, 'pending', 'active', 'inactive')")
            ->orderByDesc('id')
            ->get();

        return view('admin.businesses', ['businesses' => $businesses]);
    }

    // approve / reject / activate / deactivate a business account
    public function setStatus(Request $request, $id)
    {
        $data = $request->validate(['action' => 'required|in:approve,reject,activate,deactivate']);
        $user = User::where('role', 'business')->findOrFail($id);

        $newStatus = in_array($data['action'], ['approve', 'activate']) ? 'active' : 'inactive';
        $user->update(['status' => $newStatus]);
        ActivityLog::create(['user_id' => $request->user()->id, 'action' => 'business_' . $data['action'], 'details' => $user->email]);

        return back()->with('success', 'Business account updated (' . $data['action'] . ').');
    }
}
