<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFieldValue extends Model
{
    protected $fillable = ['order_id', 'service_field_id', 'label', 'value', 'file_path'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ServiceField::class, 'service_field_id');
    }
}
