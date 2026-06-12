<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    protected $fillable = [
        'post_id',
        'alumni_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }
}