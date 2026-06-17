<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    protected $fillable = [
        'job_id',
        'alumni_id',
        'full_name',
        'email',
        'phone',
        'resume',
        'cover_letter',
        'status',
        'rejection_reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function applicant()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }
    
}