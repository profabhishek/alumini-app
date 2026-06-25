<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MentorCategory extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'icon_svg', 'description', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public static function boot(): void
    {
        parent::boot();
        static::creating(function ($cat) {
            if (empty($cat->slug)) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function mentorProfiles()
    {
        return $this->belongsToMany(MentorProfile::class, 'mentor_profile_categories');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
