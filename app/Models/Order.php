<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'tracking_token', 'customer_id', 'service_id',
        'service_name_snapshot', 'price_snapshot', 'currency_snapshot',
        'status', 'payment_status', 'customer_name', 'customer_email', 'customer_phone',
        'coupon_code', 'consent_given_at', 'completed_at', 'cancelled_at', 'expires_at',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'consent_given_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(OrderFieldValue::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class)->orderBy('created_at');
    }

    public function results(): HasMany
    {
        return $this->hasMany(OrderResult::class)->orderBy('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function couponUsage(): HasOne
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('order_number', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('customer_email', 'like', "%{$term}%")
                ->orWhereHas('fieldValues', fn ($fv) => $fv->where('value', 'like', "%{$term}%"));
        });
    }
}
