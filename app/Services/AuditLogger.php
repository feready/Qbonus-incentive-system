<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(
        string $event,
        string $description,
        ?User $user = null,
        array $metadata = [],
        ?string $emailOverride = null
    ): void {
        /** @var Request $request */
        $request = request();

        $userAgent = $request->userAgent();

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email ?? $emailOverride,

            'event' => $event,
            'description' => $description,

            'ip_address' => $request->ip(),

            'user_agent' => $userAgent
                ? mb_substr($userAgent, 0, 2000)
                : null,

            // Hanya menyimpan path, bukan query string.
            'url' => $request->getPathInfo(),

            'method' => $request->method(),

            'metadata' => $metadata !== []
                ? $metadata
                : null,
        ]);
    }
}
