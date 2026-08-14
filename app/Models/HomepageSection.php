<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = ['key', 'title', 'content', 'image', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function getContentAttribute($value): mixed
    {
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $value;
    }
}
