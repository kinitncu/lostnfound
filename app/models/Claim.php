<?php
class Claim {
    public static function create(int $item_id, int $claimer_id, ?string $message): int {
        $st = pdo()->prepare('INSERT INTO claims (item_id, claimer_id, message, status) VALUES (:iid,:uid,:msg,"pending")');
        $st->execute([
            ':iid' => $item_id,
            ':uid' => $claimer_id,
            ':msg' => $message ?: null
        ]);
        return (int)pdo()->lastInsertId();
    }

    public static function find(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM claims WHERE id=:id LIMIT 1');
        $st->execute([':id' => $id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function pendingByItem(int $item_id): ?array {
        $st = pdo()->prepare('SELECT * FROM claims WHERE item_id=:iid AND status="pending" ORDER BY created_at ASC LIMIT 1');
        $st->execute([':iid' => $item_id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function pendingByUserAndItem(int $user_id, int $item_id): ?array {
        $st = pdo()->prepare('SELECT * FROM claims WHERE item_id=:iid AND claimer_id=:uid AND status="pending" LIMIT 1');
        $st->execute([':iid' => $item_id, ':uid' => $user_id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function listPending(int $limit = 100): array {
        $sql = 'SELECT c.*, i.title, i.type, i.status AS item_status, i.state AS item_state,
                       u.name AS claimer_name, u.school_id AS claimer_school_id
                FROM claims c
                JOIN items i ON i.id = c.item_id
                JOIN users u ON u.id = c.claimer_id
                WHERE c.status="pending"
                ORDER BY c.created_at ASC
                LIMIT :lim';
        $st = pdo()->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function approve(int $id): void {
        $st = pdo()->prepare('UPDATE claims SET status="approved" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function reject(int $id): void {
        $st = pdo()->prepare('UPDATE claims SET status="rejected" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function cancel(int $id, int $user_id): bool {
        $st = pdo()->prepare('UPDATE claims SET status="cancelled" WHERE id=:id AND claimer_id=:uid AND status="pending"');
        $st->execute([':id' => $id, ':uid' => $user_id]);
        return $st->rowCount() > 0;
    }

    public static function rejectOthers(int $item_id, int $except_id): void {
        $st = pdo()->prepare('UPDATE claims SET status="rejected" WHERE item_id=:iid AND status="pending" AND id<>:ex');
        $st->execute([':iid' => $item_id, ':ex' => $except_id]);
    }

    public static function pendingCountByItem(int $item_id): int {
        $st = pdo()->prepare('SELECT COUNT(*) AS cnt FROM claims WHERE item_id=:iid AND status="pending"');
        $st->execute([':iid' => $item_id]);
        $row = $st->fetch();
        return (int)($row['cnt'] ?? 0);
    }
}