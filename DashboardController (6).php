<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\Notification;
use App\Services\MatchService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, MatchService $matcher)
    {
        $user = $request->user();
        $student = $user->studentProfile;
        $student->loadForMatching();

        // ----- Numbers for the top tiles -----
        $totalApps = $student->applications()->count();
        $pendingApps = $student->applications()->where('status', 'Pending')->count();
        $acceptedApps = $student->applications()->where('status', 'Accepted')->count();

        // ----- Open jobs the student has not applied to yet, with their match -----
        $appliedIds = $student->applications()->pluck('job_post_id')->toArray();
        $jobs = JobPost::open()
            ->with(['businessProfile', 'category', 'skills', 'courses'])
            ->whereNotIn('id', $appliedIds)
            ->get();

        $list = [];
        foreach ($jobs as $job) {
            $list[] = ['job' => $job, 'match' => $matcher->getMatch($student, $job)];
        }
        // best match first
        usort($list, function ($a, $b) {
            return $b['match']['total'] - $a['match']['total'];
        });
        $recommended = array_slice($list, 0, 5);

        // ----- Deadline reminder: good-match jobs that close within 2 days (reminded only once) -----
        foreach ($list as $item) {
            $job = $item['job'];
            if (strtotime($job->deadline) <= strtotime('+2 days') && $item['match']['total'] >= 40) {
                $text = 'Deadline approaching: ' . $job->title . ' closes ' . date('M j, Y', strtotime($job->deadline));
                $already = Notification::where('user_id', $user->id)->where('message', $text)->exists();
                if (!$already) {
                    Notification::send($user->id, 'deadline', $text, 'student/jobs/' . $job->id);
                }
            }
        }

        $data = [
            'student' => $student,
            'completion' => $student->completion(),
            'totalApps' => $totalApps,
            'pendingApps' => $pendingApps,
            'acceptedApps' => $acceptedApps,
            'recommended' => $recommended,
            'recent' => JobPost::open()->with('businessProfile')->orderByDesc('id')->limit(5)->get(),
            'myApps' => $student->applications()->with('jobPost.businessProfile')->orderByDesc('id')->limit(5)->get(),
            'notes' => $user->appNotifications()->orderByDesc('id')->limit(5)->get(),
            'ratingAverage' => $user->averageRating(),
            'ratingCount' => $user->ratingsReceived()->count(),
        ];

        return view('student.dashboard', $data);
    }
}
