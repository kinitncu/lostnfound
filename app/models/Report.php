<?php
class Report {
    public static function create(string $type, int $target_id, int $reporter_id, string $reason, ?int $item_id = null): int {
        $st = pdo()->prepare('INSERT INTO reports (type, target_id, item_id, reporter_id, reason) VALUES (:t,:tid,:iid,:rid,:r)');
        $st->execute([
            ':t' => $type,
            ':tid' => $target_id,
            ':iid' => $item_id,
            ':rid' => $reporter_id,
            ':r' => $reason
        ]);
        return (int)pdo()->lastInsertId();
    }

    public static function listOpen(int $limit = 100): array {
        $sql = 'SELECT r.*,
                       u.name AS reporter_name, u.school_id AS reporter_school_id,
                       i.title AS item_title,
                       c.content AS comment_content, c.item_id AS comment_item_id, c.status AS comment_status
                FROM reports r
                JOIN users u ON u.id = r.reporter_id
                LEFT JOIN items i ON (r.type="item" AND i.id = r.target_id)
                LEFT JOIN comments c ON (r.type="comment" AND c.id = r.target_id)
                WHERE r.status="open"
                ORDER BY r.created_at DESC
                LIMIT :lim';
        $st = pdo()->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function listRecent(int $limit = 50): array {
        $sql = 'SELECT r.*,
                       u.name AS reporter_name, u.school_id AS reporter_school_id,
                       i.title AS item_title,
                       c.content AS comment_content, c.item_id AS comment_item_id, c.status AS comment_status
                FROM reports r
                JOIN users u ON u.id = r.reporter_id
                LEFT JOIN items i ON (r.type="item" AND i.id = r.target_id)
                LEFT JOIN comments c ON (r.type="comment" AND c.id = r.target_id)
                WHERE r.status IN ("resolved","dismissed")
                ORDER BY r.created_at DESC
                LIMIT :lim';
        $st = pdo()->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function resolve(int $id): void {
        $st = pdo()->prepare('UPDATE reports SET status="resolved" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function dismiss(int $id): void {
        $st = pdo()->prepare('UPDATE reports SET status="dismissed" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function resolveByTarget(string $type, int $target_id): void {
        $st = pdo()->prepare('UPDATE reports SET status="resolved" WHERE type=:t AND target_id=:tid AND status="open"');
        $st->execute([':t' => $type, ':tid' => $target_id]);
    }

    public static function find(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM reports WHERE id=:id LIMIT 1');
        $st->execute([':id' => $id]);
        $r = $st->fetch();
        return $r ?: null;
    }
}