<?php
class AdminController {
    public static function itemsPending(): void {
        require_admin();

        $status = $_GET['status'] ?? 'pending'; // pending/approved/rejected/all
        $type   = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
        $q      = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 25;
        $offset = ($page - 1) * $limit;

        $status = in_array($status, ['pending','approved','rejected','all'], true) ? $status : 'pending';
        $type = in_array($type, ['lost','found'], true) ? $type : '';

        $total = Item::countByStatus(
            $status === 'all' ? null : $status,
            $type !== '' ? $type : null,
            $q !== '' ? $q : null
        );

        $items = Item::listByStatus(
            $status === 'all' ? null : $status,
            $type !== '' ? $type : null,
            $q !== '' ? $q : null,
            $limit,
            $offset
        );

        $pages = max(1, (int)ceil($total / $limit));

        render('admin/items', compact('items','status','type','q','page','pages','total','limit'));
    }

    public static function approve(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect'] ?? null;

        if ($id > 0) {
            Item::approve($id);
            $it = Item::findById($id);
            if ($it) {
                Notification::create(
                    (int)$it['user_id'],
                    'item_approved',
                    'item',
                    $id,
                    'Your item was approved',
                    'Item: '.$it['title'],
                    base_url('index.php?r=items/show&id='.$id)
                );
                AuditLog::record(current_user_id(), 'item_approve', 'item', $id, ['title'=>$it['title']]);
            }
        }

        set_flash('success', 'Item approved.');
        redirect($redirect ?: 'index.php?r=admin/items');
    }

    public static function reject(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect'] ?? null;

        if ($id > 0) {
            $it = Item::findById($id);
            Item::reject($id);
            if ($it) {
                Notification::create(
                    (int)$it['user_id'],
                    'item_rejected',
                    'item',
                    $id,
                    'Your item was rejected',
                    'Item: '.$it['title'],
                    base_url('index.php')
                );
                AuditLog::record(current_user_id(), 'item_reject', 'item', $id, ['title'=>$it['title']]);
            }
        }

        set_flash('success', 'Item rejected.');
        redirect($redirect ?: 'index.php?r=admin/items');
    }

    public static function deleteItem(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect'] ?? null;

        if ($id > 0) {
            $it = Item::findById($id);
            Item::deleteWithFiles($id);
            AuditLog::record(current_user_id(), 'item_delete', 'item', $id, ['title' => $it['title'] ?? null]);
            set_flash('success', 'Item deleted.');
        }

        redirect($redirect ?: 'index.php?r=admin/items');
    }

    public static function bulkDelete(): void {
        require_admin();
        verify_csrf();
        $ids = $_POST['ids'] ?? [];
        $redirect = $_POST['redirect'] ?? null;

        $count = 0;
        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $iid = (int)$id;
                if ($iid <= 0) continue;
                $it = Item::findById($iid);
                Item::deleteWithFiles($iid);
                AuditLog::record(current_user_id(), 'item_delete', 'item', $iid, [
                    'title' => $it['title'] ?? null,
                    'bulk' => 1
                ]);
                $count++;
            }
        }

        set_flash('success', $count > 0 ? "Deleted $count item(s)." : 'No items selected.');
        redirect($redirect ?: 'index.php?r=admin/items');
    }

    public static function bulkApprove(): void {
        require_admin();
        verify_csrf();
        $ids = $_POST['ids'] ?? [];
        $redirect = $_POST['redirect'] ?? null;

        $count = 0;
        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $iid = (int)$id;
                if ($iid <= 0) continue;
                $it = Item::findById($iid);
                if (!$it || $it['status'] !== 'pending') continue;

                Item::approve($iid);
                Notification::create(
                    (int)$it['user_id'],
                    'item_approved',
                    'item',
                    $iid,
                    'Your item was approved',
                    'Item: '.$it['title'],
                    base_url('index.php?r=items/show&id='.$iid)
                );
                AuditLog::record(current_user_id(), 'item_approve', 'item', $iid, [
                    'title'=>$it['title'],
                    'bulk'=>1
                ]);
                $count++;
            }
        }

        set_flash('success', $count > 0 ? "Approved $count item(s)." : 'No pending items selected.');
        redirect($redirect ?: 'index.php?r=admin/items');
    }

    public static function bulkReject(): void {
        require_admin();
        verify_csrf();
        $ids = $_POST['ids'] ?? [];
        $redirect = $_POST['redirect'] ?? null;

        $count = 0;
        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $iid = (int)$id;
                if ($iid <= 0) continue;
                $it = Item::findById($iid);
                if (!$it || $it['status'] !== 'pending') continue;

                Item::reject($iid);
                Notification::create(
                    (int)$it['user_id'],
                    'item_rejected',
                    'item',
                    $iid,
                    'Your item was rejected',
                    'Item: '.$it['title'],
                    base_url('index.php')
                );
                AuditLog::record(current_user_id(), 'item_reject', 'item', $iid, [
                    'title'=>$it['title'],
                    'bulk'=>1
                ]);
                $count++;
            }
        }

        set_flash('success', $count > 0 ? "Rejected $count item(s)." : 'No pending items selected.');
        redirect($redirect ?: 'index.php?r=admin/items');
    }

    // Reports queue
    public static function reports(): void {
        require_admin();
        $open = Report::listOpen(100);
        $recent = Report::listRecent(50);
        render('admin/reports', compact('open','recent'));
    }

    public static function resolveReport(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Report::resolve($id);
            AuditLog::record(current_user_id(), 'report_resolve', 'report', $id);
        }
        set_flash('success', 'Report marked resolved.');
        redirect('index.php?r=admin/reports');
    }

    public static function dismissReport(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Report::dismiss($id);
            AuditLog::record(current_user_id(), 'report_dismiss', 'report', $id);
        }
        set_flash('success', 'Report dismissed.');
        redirect('index.php?r=admin/reports');
    }

    public static function deleteComment(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $c = Comment::find($id);
            Comment::delete($id);
            Report::resolveByTarget('comment', $id);
            AuditLog::record(current_user_id(), 'comment_delete', 'comment', $id, [
                'snippet'=> mb_substr($c['content'] ?? '', 0, 60)
            ]);
            set_flash('success', 'Comment deleted and related open reports resolved.');
        }
        redirect('index.php?r=admin/reports');
    }

    // Claims moderation
    public static function claimsPending(): void {
        require_admin();
        $claims = Claim::listPending(100);
        render('admin/claims', compact('claims'));
    }

    public static function approveClaim(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $cl = Claim::find($id);
            if ($cl) {
                Claim::approve($id);
                Claim::rejectOthers((int)$cl['item_id'], $id);
                Item::updateState((int)$cl['item_id'], 'returned');
                Notification::create(
                    (int)$cl['claimer_id'],
                    'claim_approved',
                    'claim',
                    $id,
                    'Your claim was approved',
                    'Claim approved for item #'.$cl['item_id'],
                    base_url('index.php?r=items/show&id='.$cl['item_id'])
                );
                AuditLog::record(current_user_id(), 'claim_approve', 'claim', $id, [
                    'item_id'=>(int)$cl['item_id']
                ]);
            }
            set_flash('success', 'Claim approved. Item marked as Returned.');
        }
        redirect('index.php?r=admin/claims');
    }

    public static function rejectClaim(): void {
        require_admin();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $cl = Claim::find($id);
            if ($cl) {
                Claim::reject($id);
                if (Claim::pendingCountByItem((int)$cl['item_id']) === 0) {
                    Item::updateState((int)$cl['item_id'], 'open');
                }
                Notification::create(
                    (int)$cl['claimer_id'],
                    'claim_rejected',
                    'claim',
                    $id,
                    'Your claim was rejected',
                    'Claim rejected for item #'.$cl['item_id'],
                    base_url('index.php?r=items/show&id='.$cl['item_id'])
                );
                AuditLog::record(current_user_id(), 'claim_reject', 'claim', $id, [
                    'item_id'=>(int)$cl['item_id']
                ]);
            }
            set_flash('success', 'Claim rejected.');
        }
        redirect('index.php?r=admin/claims');
    }
}