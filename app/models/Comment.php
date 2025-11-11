<?php
class Comment {
    public static function create(int $item_id, int $user_id, string $content): int {
        $st = pdo()->prepare('INSERT INTO comments (item_id, user_id, content) VALUES (:iid, :uid, :c)');
        $st->execute([':iid' => $item_id, ':uid' => $user_id, ':c' => $content]);
        return (int)pdo()->lastInsertId();
    }

    public static function visibleByItem(int $item_id): array {
        $sql = 'SELECT c.*, u.name AS user_name, u.school_id AS user_school_id
                FROM comments c
                JOIN users u ON u.id = c.user_id
                WHERE c.item_id = :iid AND c.status = "visible"
                ORDER BY c.created_at ASC';
        $st = pdo()->prepare($sql);
        $st->execute([':iid' => $item_id]);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM comments WHERE id=:id LIMIT 1');
        $st->execute([':id' => $id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function hide(int $id): void {
        $st = pdo()->prepare('UPDATE comments SET status="hidden" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function show(int $id): void {
        $st = pdo()->prepare('UPDATE comments SET status="visible" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function delete(int $id): void {
        $st = pdo()->prepare('DELETE FROM comments WHERE id=:id');
        $st->execute([':id' => $id]);
    }
}