<?php

namespace App\Http\Middleware;

use App\Models\AlumniUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Only hit the database at most once every 60 seconds per user.
     * Between those writes we use the cache as a gate.
     */
    private const THROTTLE_SECONDS = 60;

    public function handle(Request $request, Closure $next): Response
    {
        $alumniId = session('alumni_id');

        if ($alumniId) {
            $cacheKey = "alumni_last_seen_{$alumniId}";

            // Only write to DB if the cache key has expired (i.e. > 60s since last write)
            if (!Cache::has($cacheKey)) {
                AlumniUser::where('id', $alumniId)
                    ->update(['last_seen_at' => now()]);

                // Set cache gate — no DB write for next 60 seconds
                Cache::put($cacheKey, true, self::THROTTLE_SECONDS);
            }
        }

        return $next($request);
    }
}