<?php
class Campus {
    public static function all(): array {
        return pdo()->query('SELECT * FROM campuses WHERE active=1 ORDER BY name ASC')->fetchAll();
    }
    public static function allWithInactive(): array {
        return pdo()->query('SELECT * FROM campuses ORDER BY active DESC, name ASC')->fetchAll();
    }
    public static function find(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM campuses WHERE id=:id');
        $st->execute([':id' => $id]);
        $r = $st->fetch();
        return $r ?: null;
    }
    public static function create(string $name, int $active=1): int {
        $st = pdo()->prepare('INSERT INTO campuses (name, active) VALUES (:n,:a)');
        $st->execute([':n' => $name, ':a' => $active]);
        return (int)pdo()->lastInsertId();
    }
    public static function update(int $id, string $name, int $active): void {
        $st = pdo()->prepare('UPDATE campuses SET name=:n, active=:a WHERE id=:id');
        $st->execute([':n' => $name, ':a' => $active, ':id' => $id]);
    }
    public static function delete(int $id): void {
        $st = pdo()->prepare('DELETE FROM campuses WHERE id=:id');
        $st->execute([':id' => $id]);
    }
    public static function findOrCreateByName(string $name): int {
        $st = pdo()->prepare('SELECT id FROM campuses WHERE name=:n LIMIT 1');
        $st->execute([':n' => $name]);
        $r = $st->fetch();
        if ($r) return (int)$r['id'];
        return self::create($name, 1);
    }
}