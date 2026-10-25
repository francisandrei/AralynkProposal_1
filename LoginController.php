<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = strtolower($request->email);

        // Too many wrong passwords: wait 5 minutes (5 attempts allowed)
        $key = 'login|' . $email . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $minutes = ceil(RateLimiter::availableIn($key) / 60);
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Too many failed attempts. Please try again in ' . $minutes . ' minute(s).']);
        }

        // 1. find the user and check the password
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 300);
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Wrong email or password.']);
        }

        // 2. check the account status
        if ($user->status == 'pending') {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Your business account is waiting for admin approval. Please check again later.']);
        }
        if ($user->status == 'inactive') {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'This account has been deactivated. Please contact the administrator.']);
        }

        // 3. login success
        RateLimiter::clear($key);
        Auth::login($user);
        $request->session()->regenerate();      // protects against session fixation
        ActivityLog::create(['user_id' => $user->id, 'action' => 'login', 'details' => $user->email]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
