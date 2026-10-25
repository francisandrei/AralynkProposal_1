<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Course;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showStudent()
    {
        $courses = Course::orderBy('name')->get();
        return view('auth.register_student', ['courses' => $courses]);
    }

    public function storeStudent(Request $request)
    {
        // Laravel checks every field. If something is wrong it goes back to the form with the error messages.
        $data = $request->validate([
            'full_name' => 'required|string|min:3|max:120',
            'student_no' => ['required', 'regex:/^[A-Za-z0-9\-]{4,30}$/', 'unique:student_profiles,student_no'],
            'email' => 'required|email|max:150|unique:users,email',
            'contact_no' => ['nullable', 'regex:/^[0-9+\- ]{7,20}$/'],
            'course_id' => 'required|exists:courses,id',
            'year_level' => 'required|integer|between:1,5',
            'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
            'consent' => 'accepted',
        ], [
            'password.regex' => 'The password must contain letters and numbers.',
            'consent.accepted' => 'You must agree to the privacy notice.',
        ]);

        // Save the user and the profile together (if one fails, none is saved)
        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),    // the real password is never saved
                'role' => 'student',
                'status' => 'active',
            ]);
            StudentProfile::create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'],
                'student_no' => $data['student_no'],
                'contact_no' => $data['contact_no'] ?? null,
                'course_id' => $data['course_id'],
                'year_level' => $data['year_level'],
            ]);
        });

        return redirect()->route('login')->with('success', 'Account created! Log in, then complete your profile (skills and availability).');
    }

    public function showBusiness()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('auth.register_business', ['categories' => $categories]);
    }

    public function storeBusiness(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|min:2|max:150',
            'contact_person' => 'required|string|min:3|max:120',
            'email' => 'required|email|max:150|unique:users,email',
            'contact_no' => ['required', 'regex:/^[0-9+\- ]{7,20}$/'],
            'address' => 'required|string|min:5|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:2000',
            'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/'],
            'consent' => 'accepted',
        ], [
            'password.regex' => 'The password must contain letters and numbers.',
            'consent.accepted' => 'You must agree to the terms.',
        ]);

        DB::transaction(function () use ($data) {
            // status "pending" = cannot log in until the admin approves the business
            $user = User::create([
                'name' => $data['business_name'],
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),
                'role' => 'business',
                'status' => 'pending',
            ]);
            BusinessProfile::create([
                'user_id' => $user->id,
                'business_name' => $data['business_name'],
                'contact_person' => $data['contact_person'],
                'contact_no' => $data['contact_no'],
                'address' => $data['address'],
                'category_id' => $data['category_id'],
                'description' => $data['description'] ?? null,
            ]);
        });

        return redirect()->route('login')->with('success', 'Registration received! An administrator will review your business. You can log in once it is approved.');
    }
}
