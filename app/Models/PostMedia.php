<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = [
        'post_id',
        'type',
        'file_path',
        'file_name',
        'file_mime',
        'file_size',
        'thumbnail_path',
        'position',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'position'  => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function toApiArray(): array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'url'       => asset('storage/' . $this->file_path),
            'thumbnail' => $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'position'  => $this->position,
        ];
    }
}