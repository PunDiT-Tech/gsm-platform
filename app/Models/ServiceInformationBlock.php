<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceInformationBlock extends Model
{
    protected $fillable = ['service_id', 'type', 'title', 'content', 'url', 'image', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
