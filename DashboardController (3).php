<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// /dashboard sends each user to the dashboard of their own role
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;

        if ($role == 'student') return redirect()->route('student.dashboard');
        if ($role == 'business') return redirect()->route('business.dashboard');
        return redirect()->route('admin.dashboard');
    }
}
