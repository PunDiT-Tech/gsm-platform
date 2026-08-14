<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = ['key', 'title', 'content', 'image', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];
}
