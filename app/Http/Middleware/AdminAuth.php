<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AlumniUser;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $alumniId = session('alumni_id');

        if (!$alumniId) {
            return redirect()->route('login')
                ->with('error', 'Please login first.');
        }

        $user = AlumniUser::find($alumniId);

        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}