<?php
class Item {
    public static function create(int $user_id, string $type, string $title, ?string $description, ?string $campus, ?string $location, ?int $category_id): int {
        $st = pdo()->prepare('INSERT INTO items (user_id, type, title, description, campus, location, category_id, status, state)
                              VALUES (:uid, :type, :title, :desc, :campus, :loc, :cat, "pending", "open")');
        $st->execute([
            ':uid'    => $user_id,
            ':type'   => $type,
            ':title'  => $title,
            ':desc'   => $description,
            ':campus' => $campus,
            ':loc'    => $location,
            ':cat'    => $category_id
        ]);
        return (int)pdo()->lastInsertId();
    }

    public static function findById(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM items WHERE id=:id LIMIT 1');
        $st->execute([':id' => $id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    public static function findApprovedById(int $id): ?array {
        $st = pdo()->prepare('SELECT * FROM items WHERE id=:id AND status="approved" LIMIT 1');
        $st->execute([':id' => $id]);
        $r = $st->fetch();
        return $r ?: null;
    }

    // Public feed with full filters; LIMIT/OFFSET inlined (no binding) to avoid HY093 on native prepares
    public static function listApproved(
        ?string $type,
        ?string $q,
        ?string $campusName,
        ?string $locationName,
        ?string $state,           // 'open','in_claim','returned','all' (null => default open + in_claim)
        ?string $dateFrom,        // 'YYYY-MM-DD'
        ?string $dateTo,          // 'YYYY-MM-DD'
        ?string $sort = 'newest', // 'newest' or 'oldest'
        int $limit = 24,
        int $offset = 0
    ): array {
        $where  = ['status="approved"'];
        $params = [];

        // State filter
        if ($state === 'all') {
            $where[] = 'state IN ("open","claim_initiated","returned")';
        } elseif ($state === 'returned') {
            $where[] = 'state = "returned"';
        } elseif ($state === 'in_claim') {
            $where[] = 'state = "claim_initiated"';
        } else {
            $where[] = 'state IN ("open","claim_initiated")';
        }

        if ($type === 'lost' || $type === 'found') { $where[] = 'type = :type'; $params[':type'] = $type; }
        if ($q)           { $where[] = '(title LIKE :q OR description LIKE :q OR location LIKE :q)'; $params[':q'] = '%'.$q.'%'; }
        if ($campusName)  { $where[] = 'campus = :campus';  $params[':campus'] = $campusName; }
        if ($locationName){ $where[] = 'location = :loc';   $params[':loc'] = $locationName; }
        if ($dateFrom)    { $where[] = 'DATE(created_at) >= :df'; $params[':df'] = $dateFrom; }
        if ($dateTo)      { $where[] = 'DATE(created_at) <= :dt'; $params[':dt'] = $dateTo; }

        $order = ($sort === 'oldest') ? 'ASC' : 'DESC';
        $lim   = max(1, (int)$limit);
        $off   = max(0, (int)$offset);

        $sql = 'SELECT * FROM items WHERE '.implode(' AND ', $where)." ORDER BY created_at $order LIMIT $lim OFFSET $off";
        $st = pdo()->prepare($sql);
        foreach ($params as $k => $v) { $st->bindValue($k, $v); }
        $st->execute();
        return $st->fetchAll();
    }

    // Admin listing; LIMIT/OFFSET inlined
    public static function listByStatus(?string $status, ?string $type, ?string $q, int $limit = 200, int $offset = 0): array {
        $where  = [];
        $params = [];

        if (in_array($status, ['pending','approved','rejected'], true)) {
            $where[] = 'i.status = :status'; $params[':status'] = $status;
        }
        if ($type === 'lost' || $type === 'found') {
            $where[] = 'i.type = :type'; $params[':type'] = $type;
        }
        if ($q) {
            $where[] = '(i.title LIKE :q OR i.description LIKE :q OR i.location LIKE :q)';
            $params[':q'] = '%'.$q.'%';
        }

        $lim = max(1, (int)$limit);
        $off = max(0, (int)$offset);

        $sql = 'SELECT i.*, u.school_id, u.name
                FROM items i JOIN users u ON u.id=i.user_id'.
               (!empty($where) ? ' WHERE '.implode(' AND ', $where) : '').
               " ORDER BY i.created_at DESC LIMIT $lim OFFSET $off";
        $st = pdo()->prepare($sql);
        foreach ($params as $k => $v) { $st->bindValue($k, $v); }
        $st->execute();
        return $st->fetchAll();
    }

    public static function countByStatus(?string $status, ?string $type, ?string $q): int {
        $where  = [];
        $params = [];

        if (in_array($status, ['pending','approved','rejected'], true)) {
            $where[] = 'status = :status'; $params[':status'] = $status;
        }
        if ($type === 'lost' || $type === 'found') {
            $where[] = 'type = :type'; $params[':type'] = $type;
        }
        if ($q) {
            $where[] = '(title LIKE :q OR description LIKE :q OR location LIKE :q)';
            $params[':q'] = '%'.$q.'%';
        }

        $sql = 'SELECT COUNT(*) AS c FROM items'.(!empty($where) ? ' WHERE '.implode(' AND ', $where) : '');
        $st = pdo()->prepare($sql);
        foreach ($params as $k => $v) { $st->bindValue($k, $v); }
        $st->execute();
        $row = $st->fetch();
        return (int)($row['c'] ?? 0);
    }

    public static function approve(int $id): void {
        $st = pdo()->prepare('UPDATE items SET status="approved", state="open" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function reject(int $id): void {
        $st = pdo()->prepare('UPDATE items SET status="rejected", state="closed" WHERE id=:id');
        $st->execute([':id' => $id]);
    }

    public static function updateState(int $id, string $state): void {
        $allowed = ['open','claim_initiated','returned','closed'];
        if (!in_array($state, $allowed, true)) return;
        $st = pdo()->prepare('UPDATE items SET state=:s WHERE id=:id');
        $st->execute([':s' => $state, ':id' => $id]);
    }

    // Safe delete; avoids dynamic IN by using JOIN deletes
    public static function deleteWithFiles(int $id): void {
        $pdo = pdo();
        $pdo->beginTransaction();
        try {
            // Delete reports for this item's comments
            $st = $pdo->prepare('DELETE r FROM reports r
                                 JOIN comments c ON c.id=r.target_id AND r.type="comment"
                                 WHERE c.item_id=:id');
            $st->execute([':id' => $id]);

            // Delete item-related reports
            $st = $pdo->prepare('DELETE FROM reports WHERE type="item" AND target_id=:id');
            $st->execute([':id' => $id]);
            $st = $pdo->prepare('DELETE FROM reports WHERE item_id=:id');
            $st->execute([':id' => $id]);

            // Delete the item (cascade removes comments/claims/images if FKs are set)
            $st = $pdo->prepare('DELETE FROM items WHERE id=:id');
            $st->execute([':id' => $id]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        // Remove uploaded files
        $dir = uploads_path('items/'.$id);
        if (is_dir($dir)) {
            $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
            }
            @rmdir($dir);
        }
    }
}