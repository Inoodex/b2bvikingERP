<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogSupport
{
    public static function log(array $payload): AuditLog
    {
        return AuditLog::create([
            'user_id' => $payload['user_id'] ?? Auth::id(),
            'vendor_id' => $payload['vendor_id'] ?? null,
            'module' => $payload['module'] ?? 'system',
            'action' => $payload['action'] ?? 'updated',
            'entity_type' => $payload['entity_type'] ?? null,
            'entity_id' => $payload['entity_id'] ?? null,
            'reference_no' => $payload['reference_no'] ?? null,
            'description' => $payload['description'] ?? null,
            'old_values' => self::normalize($payload['old_values'] ?? null),
            'new_values' => self::normalize($payload['new_values'] ?? null),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    protected static function normalize(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        return ['value' => $value];
    }
}
