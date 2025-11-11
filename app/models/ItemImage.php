<?php
class ItemImage {
    public static function add(int $item_id, string $filename, bool $is_primary = false): int {
        $st = pdo()->prepare('INSERT INTO item_images (item_id, filename, is_primary) VALUES (:iid, :fn, :p)');
        $st->execute([
            ':iid' => $item_id,
            ':fn' => $filename,
            ':p' => $is_primary ? 1 : 0,
        ]);
        return (int) pdo()->lastInsertId();
    }

    public static function setPrimary(int $item_id, int $image_id): void {
        $pdo = pdo();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('UPDATE item_images SET is_primary=0 WHERE item_id=:iid');
            $st->execute([':iid' => $item_id]);

            $st = $pdo->prepare('UPDATE item_images SET is_primary=1 WHERE id=:img AND item_id=:iid');
            $st->execute([':img' => $image_id, ':iid' => $item_id]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function primaryByItem(int $item_id): ?array {
        $st = pdo()->prepare('SELECT * FROM item_images WHERE item_id=:iid ORDER BY is_primary DESC, id ASC LIMIT 1');
        $st->execute([':iid' => $item_id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function allByItem(int $item_id): array {
        $st = pdo()->prepare('SELECT * FROM item_images WHERE item_id=:iid ORDER BY is_primary DESC, id ASC');
        $st->execute([':iid' => $item_id]);
        return $st->fetchAll();
    }
}