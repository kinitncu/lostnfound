<?php
class ReportsController {
    public static function store(): void {
        require_login();
        verify_csrf();

        $type = strtolower(trim($_POST['type'] ?? ''));
        $target_id = (int)($_POST['target_id'] ?? 0);
        $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : null;
        $reason = trim($_POST['reason'] ?? '');

        if (!in_array($type, ['item','comment'], true) || $target_id <= 0) {
            set_flash('error', 'Invalid report.');
            redirect('index.php');
        }
        if ($reason === '' || mb_strlen($reason) > 500) {
            set_flash('error', 'Please provide a reason (max 500 chars).');
            if ($item_id) redirect('index.php?r=items/show&id='.$item_id.'#comments');
            redirect('index.php');
        }

        // Validate target exists
        if ($type === 'item') {
            $item = Item::findApprovedById($target_id);
            if (!$item) { set_flash('error', 'Item not found.'); redirect('index.php'); }
            $item_id = $item['id'];
        } else {
            $comment = Comment::find($target_id);
            if (!$comment) { set_flash('error', 'Comment not found.'); redirect('index.php'); }
            if (!$item_id) $item_id = (int)$comment['item_id'];
        }

        Report::create($type, $target_id, current_user_id(), $reason, $item_id);
        set_flash('success', 'Report submitted. Thank you.');
        redirect('index.php?r=items/show&id='.$item_id.'#comments');
    }
}