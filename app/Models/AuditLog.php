<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'before_state', 'after_state', 'ip', 'user_agent'];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];
}
