<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'created_by',
        'creator_role',

        'title',
        'slug',

        'category',
        'event_mode',
        'location',

        'start_date',
        'end_date',

        'start_time',
        'end_time',

        'description',

        'event_type',
        'ticket_price',

        'total_seats',
        'registered_count',

        'registration_deadline',
        'registration_required',

        'banner_image',

        'status',
        'published_at',
    ];

    protected $casts = [
        'start_date'            => 'date',
        'end_date'              => 'date',
        'registration_deadline' => 'date',
        'registration_required' => 'boolean',
        'published_at'          => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(
            AlumniUser::class,
            'created_by'
        );
    }


    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function getRegisteredCountAttribute()
    {
        return $this->registrations()->sum('no_of_people');
    }
}