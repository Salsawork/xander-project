<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guideline extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'skill_level',
        'category',
        'tags',
        'featured_image',
        'youtube_url',
        'is_new',
        'is_featured',
        'views_count',
        'reading_time_minutes',
        'author_name',
        'published_at',
        'status',
    ];
    
    protected $casts = [
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'reading_time_minutes' => 'integer',
        'published_at' => 'datetime',
        'skill_level' => 'string',
        'status' => 'string',
    ];
}
