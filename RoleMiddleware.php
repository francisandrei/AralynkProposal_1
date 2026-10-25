<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Used in routes like:  RoleMiddleware::class . ':student'   or   RoleMiddleware::class . ':student,business'
// Only users with one of those roles (and an active account) may open the page.
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // the admin may have deactivated the account
        if ($user->status != 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('danger', 'Your account is not active.');
        }

        if (!in_array($user->role, $roles)) {
            abort(403, 'Access denied. This page is not for your account type.');
        }

        return $next($request);
    }
}
