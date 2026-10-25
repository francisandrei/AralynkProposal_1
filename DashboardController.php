<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\JobPost;
use App\Models\JobReport;
use App\Models\Rating;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'students' => User::where('role', 'student')->count(),
            'businesses' => User::where('role', 'business')->count(),
            'jobs' => JobPost::count(),
            'applications' => Application::count(),
            'accepted' => Application::whereIn('status', ['Accepted', 'Completed'])->count(),
            'ratings' => Rating::count(),
            'pendingBusinesses' => User::where('role', 'business')->where('status', 'pending')->count(),
            'openReports' => JobReport::where('status', 'open')->count(),
            'logs' => ActivityLog::with('user')->orderByDesc('id')->limit(10)->get(),
        ]);
    }
}
