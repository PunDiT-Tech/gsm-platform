<?php

namespace App\Helpers;

use App\Models\User;

class StaffNotifier
{
    public static function notify(string $title, string $message): void
    {
        $staff = User::whereHas('roles')->where('is_active', true)->get();

        foreach ($staff as $user) {
            $user->notify(new \App\Notifications\StaffNotification($title, $message));
        }
    }
}