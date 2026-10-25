<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Category;
use App\Models\JobPost;
use App\Models\Rating;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        // jobs per category and applications per category (popular categories)
        $categories = Category::withCount('jobPosts')->orderByDesc('job_posts_count')->orderBy('name')->get();
        $popular = Category::select('categories.id', 'categories.name')
            ->selectRaw('COUNT(applications.id) as total')
            ->join('job_posts', 'job_posts.category_id', '=', 'categories.id')
            ->join('applications', 'applications.job_post_id', '=', 'job_posts.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')->limit(5)->get();

        $byStatus = Application::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.reports', [
            'totalStudents' => User::where('role', 'student')->count(),
            'totalBusinesses' => User::where('role', 'business')->count(),
            'totalJobs' => JobPost::count(),
            'activeJobs' => JobPost::where('status', 'open')->count(),
            'closedJobs' => JobPost::where('status', '<>', 'open')->count(),
            'totalApps' => Application::count(),
            'acceptedApps' => Application::whereIn('status', ['Accepted', 'Completed'])->count(),
            'rejectedApps' => Application::where('status', 'Rejected')->count(),
            'successful' => Application::where('status', 'Completed')->count(),
            'totalRatings' => Rating::count(),
            'averageRating' => Rating::avg('stars'),
            'categoryNames' => $categories->pluck('name')->toArray(),
            'categoryTotals' => $categories->pluck('job_posts_count')->toArray(),
            'statusNames' => $byStatus->keys()->toArray(),
            'statusTotals' => $byStatus->values()->toArray(),
            'popular' => $popular,
        ]);
    }
}
