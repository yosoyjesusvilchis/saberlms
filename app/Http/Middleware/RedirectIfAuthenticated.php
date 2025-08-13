<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guard = $guards[0] ?? null;

        if (Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();

            switch ((int) $user->role) {
                case 1:
                    return redirect()->route('admin.dashboard');
                case 2:
                    return redirect()->route('instructor.dashboard');
                case 3:
                    return redirect()->route('student.dashboard');
                case 4:
                    return redirect()->route('organization.dashboard');
                default:
                    return redirect('/'); // Fallback si el rol no coincide
            }
        }

        return $next($request);
    }
}
