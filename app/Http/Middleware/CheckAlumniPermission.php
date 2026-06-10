<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AlumniUser;

class CheckAlumniPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $userId = session('alumni_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = AlumniUser::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        // If no specific permission required, just check they are staff
        if (empty($permissions)) {
            if (!$user->isStaff()) {
                abort(403, 'Unauthorized.');
            }
            return $next($request);
        }

        // Check each required permission
        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                abort(403, 'You do not have permission to access this section.');
            }
        }

        return $next($request);
    }
}