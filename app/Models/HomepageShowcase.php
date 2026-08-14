<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageShowcase extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image', 'desktop_image', 'mobile_image',
        'link_type', 'service_id', 'link_url', 'animation', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
