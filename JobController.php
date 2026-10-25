<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\JobPost;
use App\Models\JobReport;
use App\Models\Notification;
use Illuminate\Http\Request;

// The admin can hide or delete inappropriate job posts. The admin never decides who gets hired.
class JobController extends Controller
{
    public function index()
    {
        return view('admin.jobs', [
            'reports' => JobReport::with(['jobPost', 'reporter'])->where('status', 'open')->orderByDesc('id')->get(),
            'jobs' => JobPost::with(['businessProfile', 'category'])->withCount('applications')->orderByDesc('id')->limit(200)->get(),
        ]);
    }

    public function hide(Request $request, $id)
    {
        $job = JobPost::with('businessProfile')->findOrFail($id);
        $job->update(['is_hidden' => true]);
        Notification::send($job->businessProfile->user_id, 'admin', 'One of your job posts was hidden by the administrator: ' . $job->title . '. Please contact the admin.', 'business/jobs');
        ActivityLog::create(['user_id' => $request->user()->id, 'action' => 'job_hide', 'details' => 'job ' . $job->id]);

        return back()->with('success', 'Job hidden from students.');
    }

    public function unhide($id)
    {
        JobPost::findOrFail($id)->update(['is_hidden' => false]);
        return back()->with('success', 'Job is visible again.');
    }

    public function destroy(Request $request, $id)
    {
        JobPost::findOrFail($id)->delete();
        ActivityLog::create(['user_id' => $request->user()->id, 'action' => 'job_delete', 'details' => 'job ' . $id]);

        return back()->with('success', 'Job deleted.');
    }

    // Close a report: "resolved" (action was taken) or "dismissed" (nothing wrong)
    public function closeReport(Request $request, $id)
    {
        $data = $request->validate(['status' => 'required|in:resolved,dismissed']);
        JobReport::findOrFail($id)->update(['status' => $data['status']]);

        return back()->with('success', 'Report updated.');
    }
}
