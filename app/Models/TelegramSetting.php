<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSetting extends Model
{
    protected $fillable = ['enabled', 'bot_token', 'chat_id', 'events'];

    protected $casts = [
        'enabled' => 'boolean',
        'events' => 'array',
    ];
}
