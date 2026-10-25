<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('q', ''));

        $query = User::where('role', 'student')->with('studentProfile.course');
        if ($search != '') {
            $query->where(function ($w) use ($search) {
                $w->where('email', 'like', '%' . $search . '%')
                  ->orWhereHas('studentProfile', function ($p) use ($search) {
                      $p->where('full_name', 'like', '%' . $search . '%')->orWhere('student_no', 'like', '%' . $search . '%');
                  });
            });
        }

        return view('admin.students', ['students' => $query->orderByDesc('id')->limit(200)->get(), 'search' => $search]);
    }

    // Activate or deactivate a student account
    public function setStatus(Request $request, $id)
    {
        $data = $request->validate(['status' => 'required|in:active,inactive']);
        $user = User::where('role', 'student')->findOrFail($id);
        $user->update(['status' => $data['status']]);
        ActivityLog::create(['user_id' => $request->user()->id, 'action' => 'student_' . $data['status'], 'details' => $user->email]);

        return back()->with('success', 'Student account is now ' . $data['status'] . '.');
    }
}
