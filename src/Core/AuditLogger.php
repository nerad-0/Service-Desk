<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

class AuditLogger
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function log(?int $actorId, string $action, string $entityType, ?int $entityId = null, array $metadata = []): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO audit_logs (actor_id, action, entity_type, entity_id, metadata, ip_address)
                 VALUES (:actor_id, :action, :entity_type, :entity_id, :metadata, :ip_address)'
            );
            $stmt->execute([
                'actor_id' => $actorId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'metadata' => $metadata === [] ? null : json_encode($metadata, JSON_UNESCAPED_UNICODE),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable) {
            error_log('Audit log write failed: ' . $action);
        }
    }
}

