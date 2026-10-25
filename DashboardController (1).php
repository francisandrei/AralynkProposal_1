<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Message;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $business = $user->businessProfile;

        // all applications to this business's jobs (without the withdrawn ones)
        $applications = Application::whereHas('jobPost', function ($q) use ($business) {
            $q->where('business_profile_id', $business->id);
        })->where('status', '<>', 'Withdrawn');

        return view('business.dashboard', [
            'business' => $business,
            'activeJobs' => $business->jobPosts()->where('status', 'open')->count(),
            'applicantCount' => (clone $applications)->count(),
            'pendingCount' => (clone $applications)->where('status', 'Pending')->count(),
            'acceptedCount' => (clone $applications)->whereIn('status', ['Accepted', 'Completed'])->count(),
            'unreadMessages' => Message::where('receiver_id', $user->id)->where('is_read', false)->count(),
            'recent' => (clone $applications)->with(['student.course', 'jobPost'])->orderByDesc('id')->limit(6)->get(),
            'jobs' => $business->jobPosts()->withCount(['applications as applicant_count' => function ($q) {
                $q->where('status', '<>', 'Withdrawn');
            }])->orderByDesc('id')->limit(6)->get(),
            'ratingAverage' => $user->averageRating(),
            'ratingCount' => $user->ratingsReceived()->count(),
        ]);
    }
}
