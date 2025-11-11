<?php
class User {
    public static function findById(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM users WHERE id=:id LIMIT 1');
        $st->execute([':id' => $id]);
        $u = $st->fetch();
        return $u ?: null;
    }

    public static function findBySchoolId(string $sid): ?array {
        $st = pdo()->prepare('SELECT * FROM users WHERE school_id=:sid LIMIT 1');
        $st->execute([':sid' => $sid]);
        $u = $st->fetch();
        return $u ?: null;
    }

    public static function listAll(int $limit = 1000, int $offset = 0): array {
        $st = pdo()->prepare('SELECT * FROM users ORDER BY created_at DESC LIMIT :lim OFFSET :off');
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public static function createByAdmin(array $data): int {
        $sql = 'INSERT INTO users (school_id, password_hash, email, phone, name, is_admin,
                                   user_type, first_name, middle_name, last_name,
                                   department, year_level, section, position_title, avatar_filename)
                VALUES (:sid, :ph, :email, :phone, :name, :is_admin,
                        :user_type, :first_name, :middle_name, :last_name,
                        :department, :year_level, :section, :position_title, NULL)';
        $st = pdo()->prepare($sql);
        $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $st->execute([
            ':sid' => $data['school_id'],
            ':ph'  => $data['password_hash'] ?? null,
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':name'  => $fullName !== '' ? $fullName : ($data['name'] ?? null),
            ':is_admin' => (int)($data['is_admin'] ?? 0),
            ':user_type' => $data['user_type'] ?? null,
            ':first_name' => $data['first_name'] ?? null,
            ':middle_name' => $data['middle_name'] ?? null,
            ':last_name' => $data['last_name'] ?? null,
            ':department' => $data['department'] ?? null,
            ':year_level' => $data['year_level'] ?? null,
            ':section' => $data['section'] ?? null,
            ':position_title' => $data['position_title'] ?? null,
        ]);
        return (int)pdo()->lastInsertId();
    }

    public static function deleteByAdmin(int $id): void {
        $st = pdo()->prepare('DELETE FROM users WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function updatePasswordHash(int $id, string $hash): void {
        $st = pdo()->prepare('UPDATE users SET password_hash=:h WHERE id=:id');
        $st->execute([':h' => $hash, ':id' => $id]);
    }

    public static function updateContactInfo(int $id, ?string $email, ?string $phone): void {
        $st = pdo()->prepare('UPDATE users SET email=:e, phone=:p WHERE id=:id');
        $st->execute([':e' => $email, ':p' => $phone, ':id' => $id]);
    }

    public static function updateProfileDetails(int $id, array $data): void {
        // NOTE: requires columns: email, phone, first_name, middle_name, last_name,
        // department, year_level, section, position_title, name
        $sql = 'UPDATE users SET
                  email=:email, phone=:phone,
                  first_name=:first_name, middle_name=:middle_name, last_name=:last_name,
                  department=:department, year_level=:year_level, section=:section, position_title=:position_title,
                  name=:display_name
                WHERE id=:id';
        $display = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $st = pdo()->prepare($sql);
        $st->execute([
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':first_name' => $data['first_name'] ?? null,
            ':middle_name' => $data['middle_name'] ?? null,
            ':last_name' => $data['last_name'] ?? null,
            ':department' => $data['department'] ?? null,
            ':year_level' => $data['year_level'] ?? null,
            ':section' => $data['section'] ?? null,
            ':position_title' => $data['position_title'] ?? null,
            ':display_name' => $display !== '' ? $display : null,
            ':id' => $id
        ]);
    }
}