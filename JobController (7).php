<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Category;
use App\Models\JobPost;
use App\Models\JobReport;
use App\Models\Skill;
use App\Models\StudentDayAvailability;
use App\Services\MatchService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    // ----- Search and filter the jobs -----
    public function index(Request $request, MatchService $matcher)
    {
        $student = $request->user()->studentProfile;
        $student->loadForMatching();

        // What the student typed in the search form
        $keyword = trim($request->input('q', ''));
        $categoryId = (int) $request->input('category');
        $jobType = $request->input('type', '');
        $skillId = (int) $request->input('skill');
        $location = trim($request->input('location', ''));
        $weekday = (int) $request->input('day');
        $fromDate = $request->input('from', '');
        $sort = $request->input('sort', 'newest');

        // Build the query step by step (Laravel fills in the values safely)
        $query = JobPost::open()->with(['businessProfile.user', 'category', 'skills', 'courses']);

        if ($keyword != '') {
            $query->where(function ($w) use ($keyword) {
                $w->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%')
                  ->orWhereHas('businessProfile', function ($b) use ($keyword) {
                      $b->where('business_name', 'like', '%' . $keyword . '%');
                  });
            });
        }
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }
        if (in_array($jobType, config('aralynk.job_types'))) {
            $query->where('job_type', $jobType);
        }
        if ($skillId > 0) {
            $query->whereHas('skills', function ($s) use ($skillId) {
                $s->where('skills.id', $skillId);
            });
        }
        if ($location != '') {
            $query->where('work_location', 'like', '%' . $location . '%');
        }
        if ($fromDate != '' && strtotime($fromDate)) {
            $query->where(function ($w) use ($fromDate) {
                $w->whereNull('work_date')->orWhereRaw('COALESCE(end_date, work_date) >= ?', [$fromDate]);
            });
        }

        $found = $query->orderByDesc('id')->limit(100)->get();

        // The weekday filter and the match score are done in PHP
        $jobs = [];
        foreach ($found as $job) {
            if ($weekday > 0) {
                $onThatDay = false;
                foreach ($matcher->jobDays($job) as $day) {
                    if ($day['weekday'] == $weekday) $onThatDay = true;
                }
                if (!$onThatDay) continue;
            }
            $jobs[] = ['job' => $job, 'match' => $matcher->getMatch($student, $job)];
        }

        if ($sort == 'match') {
            usort($jobs, function ($a, $b) {
                return $b['match']['total'] - $a['match']['total'];
            });
        }
        if ($sort == 'deadline') {
            usort($jobs, function ($a, $b) {
                return strcmp($a['job']->deadline, $b['job']->deadline);
            });
        }

        return view('student.jobs', [
            'jobs' => $jobs,
            'appliedIds' => $student->applications()->pluck('job_post_id')->toArray(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'skills' => Skill::where('is_approved', true)->orderBy('name')->get(),
            'filters' => compact('keyword', 'categoryId', 'jobType', 'skillId', 'location', 'weekday', 'fromDate', 'sort'),
        ]);
    }

    // ----- Job details -----
    public function show(Request $request, MatchService $matcher, $id)
    {
        $job = JobPost::with(['businessProfile.user', 'category', 'skills', 'courses'])->findOrFail($id);
        if ($job->is_hidden) {
            abort(404);
        }

        $student = $request->user()->studentProfile;
        $student->loadForMatching();

        $application = Application::where('student_profile_id', $student->id)->where('job_post_id', $job->id)->first();
        $canApply = $job->status == 'open' && $job->deadline >= now()->toDateString()
                    && (!$application || $application->status == 'Withdrawn');

        $dayNames = [];
        foreach ($matcher->jobDays($job) as $day) {
            $dayNames[] = substr(config('aralynk.days')[$day['weekday']], 0, 3);
        }

        $businessUser = $job->businessProfile->user;

        return view('student.job_details', [
            'job' => $job,
            'match' => $matcher->getMatch($student, $job),
            'application' => $application,
            'canApply' => $canApply,
            'dayNames' => $dayNames,
            'businessUser' => $businessUser,
            'businessRating' => $businessUser->averageRating(),
            'businessRatingCount' => $businessUser->ratingsReceived()->count(),
        ]);
    }

    // ----- "I'm available for this day": save the job's date(s) and hours as the student's availability -----
    public function markAvailable(Request $request, MatchService $matcher, $id)
    {
        $job = JobPost::findOrFail($id);
        $student = $request->user()->studentProfile;

        $saved = 0;
        foreach ($matcher->jobDays($job) as $day) {
            if ($day['date'] == '' || $day['date'] < now()->toDateString()) continue;
            StudentDayAvailability::updateOrCreate(
                ['student_profile_id' => $student->id, 'avail_date' => $day['date']],
                ['start_time' => $job->start_time, 'end_time' => $job->end_time]
            );
            $saved++;
        }

        if ($saved > 0) {
            return back()->with('success', "You are marked available for this job's day(s). Your match score was updated.");
        }
        return back()->with('warning', 'This job has no specific date to mark.');
    }

    // ----- Report an inappropriate job post -----
    public function report(Request $request, $id)
    {
        $job = JobPost::findOrFail($id);
        $data = $request->validate(['reason' => 'required|string|min:5|max:500']);

        JobReport::create(['job_post_id' => $job->id, 'reporter_id' => $request->user()->id, 'reason' => $data['reason']]);

        return back()->with('success', 'Thank you. The administrator will review this post.');
    }
}
