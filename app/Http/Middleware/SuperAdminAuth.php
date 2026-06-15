<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AlumniUser;

class SuperAdminAuth
{
    /**
     * Restricts access to super_admin accounts only.
     * Assumes AdminAuth has already verified the user is admin/super_admin —
     * this middleware narrows it further to super_admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $alumniId = session('alumni_id');

        if (!$alumniId) {
            return redirect()->route('login')
                ->with('error', 'Please login first.');
        }

        $user = AlumniUser::find($alumniId);

        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'Only super admins can access this page.');
        }

        return $next($request);
    }
}