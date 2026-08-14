<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = ['payment_id', 'file_path', 'original_name', 'mime_type', 'transaction_id', 'notes'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
