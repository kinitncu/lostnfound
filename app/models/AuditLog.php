<?php
class AuditLog {
    public static function record(int $admin_id, string $action, ?string $subject_type, ?int $subject_id, array $meta = []): void {
        $payload = empty($meta) ? null : json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $st = pdo()->prepare('INSERT INTO audit_logs (admin_id, action, subject_type, subject_id, metadata) VALUES (:uid,:a,:t,:sid,:m)');
        $st->execute([
            ':uid' => $admin_id,
            ':a'   => $action,
            ':t'   => $subject_type,
            ':sid' => $subject_id,
            ':m'   => $payload
        ]);
    }
    public static function latest(int $limit = 200): array {
        $st = pdo()->prepare('SELECT al.*, u.name AS admin_name, u.school_id AS admin_sid
                              FROM audit_logs al JOIN users u ON u.id=al.admin_id
                              ORDER BY al.created_at DESC LIMIT :lim');
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }
}