<?php
class Location {
    public static function listByCampus(?int $campus_id = null): array {
        if ($campus_id) {
            $st = pdo()->prepare('SELECT l.*, c.name AS campus_name FROM locations l JOIN campuses c ON c.id=l.campus_id WHERE l.campus_id=:cid ORDER BY l.name ASC');
            $st->execute([':cid' => $campus_id]);
            return $st->fetchAll();
        }
        return pdo()->query('SELECT l.*, c.name AS campus_name FROM locations l JOIN campuses c ON c.id=l.campus_id ORDER BY c.name ASC, l.name ASC')->fetchAll();
    }
    public static function create(int $campus_id, string $name, int $active=1): int {
        $st = pdo()->prepare('INSERT INTO locations (campus_id, name, active) VALUES (:cid,:n,:a)');
        $st->execute([':cid' => $campus_id, ':n' => $name, ':a' => $active]);
        return (int)pdo()->lastInsertId();
    }
    public static function update(int $id, int $campus_id, string $name, int $active): void {
        $st = pdo()->prepare('UPDATE locations SET campus_id=:cid, name=:n, active=:a WHERE id=:id');
        $st->execute([':cid' => $campus_id, ':n' => $name, ':a' => $active, ':id' => $id]);
    }
    public static function delete(int $id): void {
        $st = pdo()->prepare('DELETE FROM locations WHERE id=:id');
        $st->execute([':id' => $id]);
    }
}