<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

// Monitoring only. The admin does not accept or reject applicants.
class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', '');

        $query = Application::with(['student', 'jobPost.businessProfile']);
        if (in_array($status, ['Pending', 'Reviewed', 'Accepted', 'Rejected', 'Withdrawn', 'Completed'])) {
            $query->where('status', $status);
        }

        return view('admin.applications', ['applications' => $query->orderByDesc('id')->limit(200)->get(), 'status' => $status]);
    }
}
