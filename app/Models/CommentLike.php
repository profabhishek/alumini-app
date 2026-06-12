<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    protected $fillable = [
        'comment_id',
        'alumni_id',
    ];

    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniUser::class, 'alumni_id');
    }
}