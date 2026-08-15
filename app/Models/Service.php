<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'short_description', 'full_description', 'icon', 'image',
        'price', 'currency', 'processing_time', 'service_type', 'payment_required', 'payment_method_id',
        'is_active', 'is_featured', 'sort_order', 'customer_notice', 'customer_instructions',
        'admin_internal_notes', 'consent_required',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'payment_required' => 'boolean',
        'consent_required' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceField::class)->orderBy('sort_order');
    }

    public function activeFields(): HasMany
    {
        return $this->fields()->where('is_active', true);
    }

    public function informationBlocks(): HasMany
    {
        return $this->hasMany(ServiceInformationBlock::class)->orderBy('sort_order');
    }

    public function activeInformationBlocks(): HasMany
    {
        return $this->informationBlocks()->where('is_active', true);
    }

    public function links(): HasMany
    {
        return $this->hasMany(ServiceLink::class)->orderBy('sort_order');
    }

    public function activeLinks(): HasMany
    {
        return $this->links()->where('is_active', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->whereHas('category', fn ($q) => $q->where('is_active', true))->where('is_active', true);
    }
}
