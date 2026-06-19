<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupInvitation extends Model
{
    protected $fillable = ['group_id', 'invited_by', 'alumni_id', 'status', 'responded_at'];

    protected $casts = ['responded_at' => 'datetime'];

    public function group()
    {
        return $this->belongsTo(CommunityGroup::class, 'group_id');
    }

    public function invitedBy()
    {
        return $this->belongsTo(AlumniUser::class, 'invited_by');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isAccepted(): bool { return $this->status === 'accepted'; }
}
