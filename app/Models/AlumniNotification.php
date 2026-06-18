<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniNotification extends Model
{
    protected $fillable = [
        'recipient_id',
        'actor_id',
        'type',
        'post_id',
        'comment_id',
        'preview',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function actor()
    {
        return $this->belongsTo(AlumniUser::class, 'actor_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

}