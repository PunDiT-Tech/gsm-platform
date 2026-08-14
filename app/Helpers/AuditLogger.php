<?php

namespace App\Helpers;

use App\Models\AuditLog;

class AuditLogger
{
    public static function log(string $action, mixed $entity = null, ?array $before = null, ?array $after = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity?->id,
            'before_state' => $before,
            'after_state' => $after,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
