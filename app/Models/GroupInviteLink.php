<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GroupInviteLink extends Model
{
    protected $fillable = ['group_id', 'created_by', 'token', 'used_by', 'used_at', 'expires_at'];

    protected $casts = ['used_at' => 'datetime', 'expires_at' => 'datetime'];

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(AlumniUser::class, 'created_by');
    }

    public function usedBy()
    {
        return $this->belongsTo(AlumniUser::class, 'used_by');
    }

    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    public static function generate(int $groupId, int $createdBy): self
    {
        return self::create([
            'group_id'   => $groupId,
            'created_by' => $createdBy,
            'token'      => Str::random(32),
            'expires_at' => now()->addDays(7),
        ]);
    }
}
