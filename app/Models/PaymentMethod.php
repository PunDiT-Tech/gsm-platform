<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = ['code', 'name', 'description', 'instructions', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
