<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceField extends Model
{
    protected $fillable = [
        'service_id', 'label', 'internal_name', 'type', 'placeholder', 'description',
        'is_required', 'validation', 'validation_regex', 'min_length', 'max_length',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ServiceFieldOption::class)->orderBy('sort_order');
    }

    public function isSelectType(): bool
    {
        return in_array($this->type, ['SELECT', 'MULTI_SELECT', 'RADIO', 'CHECKBOX']);
    }

    public function isFileType(): bool
    {
        return $this->type === 'FILE';
    }
}
