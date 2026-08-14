<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = ['user_id', 'type', 'description', 'ip', 'user_agent'];
}
