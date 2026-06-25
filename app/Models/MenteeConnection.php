<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenteeConnection extends Model
{
    protected $fillable = [
        'mentor_profile_id', 'mentee_id', 'status',
        'message', 'mentor_note', 'connected_at',
    ];

    protected $casts = ['connected_at' => 'datetime'];

    public function mentor()
    {
        return $this->belongsTo(MentorProfile::class, 'mentor_profile_id');
    }

    public function mentee()
    {
        return $this->belongsTo(AlumniUser::class, 'mentee_id');
    }
}
