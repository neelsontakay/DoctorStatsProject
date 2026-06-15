<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function record(
        string $action,
        ?User $user = null,
        ?Organization $organization = null,
        ?Model $entity = null,
        ?array $metadata = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'organization_id' => $organization?->id,
            'action' => $action,
            'entity_type' => $entity !== null ? $entity::class : 'system',
            'entity_id' => $entity?->getKey(),
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
