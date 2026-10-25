<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobPost;
use App\Models\Notification;
use App\Models\Rating;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // ----- Apply for a job -----
    public function store(Request $request)
    {
        $data = $request->validate([
            'job_post_id' => 'required|exists:job_posts,id',
            'message' => 'nullable|string|max:1000',
        ]);

        $student = $request->user()->studentProfile;
        $job = JobPost::with('businessProfile')->findOrFail($data['job_post_id']);

        // 1. Is the job still open?
        if ($job->is_hidden || $job->status != 'open' || $job->deadline < now()->toDateString()) {
            return redirect()->route('student.jobs')->with('danger', 'This job is no longer accepting applications.');
        }

        // 2. Did the student already apply? (a student can apply only once per job)
        $existing = Application::where('student_profile_id', $student->id)->where('job_post_id', $job->id)->first();
        if ($existing && $existing->status != 'Withdrawn') {
            return back()->with('warning', 'You already applied to this job.');
        }

        // 3. Save the application (or re-open a withdrawn one)
        if ($existing) {
            $existing->forceFill(['status' => 'Pending', 'message' => $data['message'] ?? null, 'created_at' => now()])->save();
        } else {
            Application::create([
                'student_profile_id' => $student->id,
                'job_post_id' => $job->id,
                'message' => $data['message'] ?? null,
            ]);
        }

        // 4. Tell the business and the student
        Notification::send($job->businessProfile->user_id, 'application', $student->full_name . ' applied for ' . $job->title, 'business/applicants');
        Notification::send($request->user()->id, 'application', 'Application submitted for ' . $job->title, 'student/applications');

        return redirect()->route('student.applications')->with('success', 'Application submitted! The business will review it.');
    }

    // ----- My applications -----
    public function index(Request $request)
    {
        $student = $request->user()->studentProfile;

        $applications = $student->applications()
            ->with(['jobPost.businessProfile.user'])
            ->orderByDesc('id')
            ->get();

        // ratings I already gave, keyed by application id
        $myRatings = Rating::where('rater_id', $request->user()->id)->get()->keyBy('application_id');

        return view('student.applications', ['applications' => $applications, 'myRatings' => $myRatings]);
    }

    // ----- Withdraw an application -----
    public function withdraw(Request $request, $id)
    {
        $student = $request->user()->studentProfile;

        // looking only inside THIS student's applications = it must be my application
        $application = $student->applications()->with('jobPost.businessProfile')->findOrFail($id);

        if (in_array($application->status, ['Pending', 'Reviewed', 'Accepted'])) {
            $application->update(['status' => 'Withdrawn']);
            Notification::send($application->jobPost->businessProfile->user_id, 'response',
                $student->full_name . ' withdrew their application for ' . $application->jobPost->title, 'business/applicants');
            return back()->with('success', 'Application withdrawn.');
        }

        return back();
    }
}
