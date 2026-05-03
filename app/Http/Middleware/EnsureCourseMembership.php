<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCourseMembership
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) return redirect('/slophub/login');

        $course = $user->currentCourse();
        if (!$course) {
            // Admin with no memberships can still access admin pages. Non-admins need a course.
            if (!$user->is_admin) {
                Auth::logout();
                return redirect('/slophub/login')->with('status', 'Din konto er ikke tilknyttet noget kursus.');
            }
        } else {
            app()->instance('current_course', $course);
        }

        return $next($request);
    }
}
