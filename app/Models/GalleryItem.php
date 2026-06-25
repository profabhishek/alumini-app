<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $table = 'gallery_items';

    protected $fillable = [
        'title', 'country', 'event_name', 'event_date', 'image', 'status', 'sort_order', 'author_id',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function author()
    {
        return $this->belongsTo(AlumniUser::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}