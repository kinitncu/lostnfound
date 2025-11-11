<?php
class Category {
    public static function all(): array {
        return pdo()->query('SELECT * FROM categories WHERE active=1 ORDER BY name ASC')->fetchAll();
    }
    public static function allWithInactive(): array {
        return pdo()->query('SELECT * FROM categories ORDER BY active DESC, name ASC')->fetchAll();
    }
    public static function byIds(array $ids): array {
        if (empty($ids)) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st = pdo()->prepare("SELECT id, name FROM categories WHERE id IN ($in)");
        $st->execute(array_values($ids));
        $out = [];
        foreach ($st->fetchAll() as $r) $out[(int)$r['id']] = $r['name'];
        return $out;
    }
    public static function find(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM categories WHERE id=:id');
        $st->execute([':id' => $id]);
        $r = $st->fetch();
        return $r ?: null;
    }
    public static function create(string $name, int $active=1): int {
        $st = pdo()->prepare('INSERT INTO categories (name, active) VALUES (:n,:a)');
        $st->execute([':n' => $name, ':a' => $active]);
        return (int)pdo()->lastInsertId();
    }
    public static function update(int $id, string $name, int $active): void {
        $st = pdo()->prepare('UPDATE categories SET name=:n, active=:a WHERE id=:id');
        $st->execute([':n' => $name, ':a' => $active, ':id' => $id]);
    }
    public static function delete(int $id): void {
        $st = pdo()->prepare('DELETE FROM categories WHERE id=:id');
        $st->execute([':id' => $id]);
    }
}