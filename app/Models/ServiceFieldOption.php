<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFieldOption extends Model
{
    public $timestamps = false;

    protected $fillable = ['service_field_id', 'label', 'value', 'sort_order'];

    public function field(): BelongsTo
    {
        return $this->belongsTo(ServiceField::class, 'service_field_id');
    }
}
