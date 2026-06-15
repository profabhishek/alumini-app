<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniSession extends Model
{
    protected $table = 'alumni_sessions';

    protected $fillable = [
        'alumni_user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'device',
        'location',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function alumniUser(): BelongsTo
    {
        return $this->belongsTo(AlumniUser::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────
    public static function parseDevice(string $userAgent): string
    {
        $browser = 'Unknown Browser';
        $os      = 'Unknown OS';

        // Browser
        if (str_contains($userAgent, 'Edg/'))       $browser = 'Edge';
        elseif (str_contains($userAgent, 'OPR/'))   $browser = 'Opera';
        elseif (str_contains($userAgent, 'Chrome'))  $browser = 'Chrome';
        elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($userAgent, 'Safari'))  $browser = 'Safari';

        // OS
        if (str_contains($userAgent, 'Windows'))     $os = 'Windows';
        elseif (str_contains($userAgent, 'Mac'))     $os = 'macOS';
        elseif (str_contains($userAgent, 'Android')) $os = 'Android';
        elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) $os = 'iOS';
        elseif (str_contains($userAgent, 'Linux'))   $os = 'Linux';

        return "{$browser} on {$os}";
    }
}