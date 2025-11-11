<?php
class Notification {
    public static function create(int $user_id, string $type, ?string $ref_type, ?int $ref_id, string $title, ?string $body, ?string $url): int {
        // Clip to max lengths
        $title = mb_substr($title, 0, 160);
        $body  = $body !== null ? mb_substr($body, 0, 500) : null;
        $url   = $url !== null ? mb_substr($url, 0, 255) : null;

        $st = pdo()->prepare('INSERT INTO notifications (user_id, type, ref_type, ref_id, title, body, url) VALUES (:uid,:t,:rt,:rid,:title,:body,:url)');
        $st->execute([
            ':uid' => $user_id,
            ':t'   => $type,
            ':rt'  => $ref_type,
            ':rid' => $ref_id,
            ':title'=> $title,
            ':body'=> $body,
            ':url' => $url
        ]);
        return (int)pdo()->lastInsertId();
    }

    public static function unreadCount(int $user_id): int {
        $st = pdo()->prepare('SELECT COUNT(*) AS c FROM notifications WHERE user_id=:u AND is_read=0');
        $st->execute([':u' => $user_id]);
        $row = $st->fetch();
        return (int)($row['c'] ?? 0);
    }

    public static function latest(int $user_id, int $limit = 50): array {
        $st = pdo()->prepare('SELECT * FROM notifications WHERE user_id=:u ORDER BY created_at DESC LIMIT :lim');
        $st->bindValue(':u', $user_id, PDO::PARAM_INT);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function markRead(int $id, int $user_id): void {
        $st = pdo()->prepare('UPDATE notifications SET is_read=1 WHERE id=:id AND user_id=:u');
        $st->execute([':id' => $id, ':u' => $user_id]);
    }

    public static function markAllRead(int $user_id): void {
        $st = pdo()->prepare('UPDATE notifications SET is_read=1 WHERE user_id=:u AND is_read=0');
        $st->execute([':u' => $user_id]);
    }
}