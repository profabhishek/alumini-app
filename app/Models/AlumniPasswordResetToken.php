<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniPasswordResetToken extends Model
{
    public $timestamps = false;   // we handle created_at manually

    protected $table = 'alumni_password_reset_tokens';

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'created_at'  => 'datetime',
    ];

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}