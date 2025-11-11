<?php
class CommentsController {
    public static function store(): void {
        require_login();
        verify_csrf();

        $item_id = (int)($_POST['item_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($item_id <= 0) {
            set_flash('error', 'Invalid item.');
            redirect('index.php');
        }

        $item = Item::findApprovedById($item_id);
        if (!$item) {
            set_flash('error', 'Item not found.');
            redirect('index.php');
        }

        if ($content === '' || mb_strlen($content) > 1000) {
            set_flash('error', 'Comment must be between 1 and 1000 characters.');
            redirect('index.php?r=items/show&id='.$item_id.'#comments');
        }

        Comment::create($item_id, current_user_id(), $content);

        // Notify item owner (not self)
        if ((int)$item['user_id'] !== current_user_id()) {
            $actor = current_user();
            $title = 'New comment on your item';
            $body  = ($actor['name'] ?: $actor['school_id']).': '.mb_substr($content,0,120);
            $url   = base_url('index.php?r=items/show&id='.$item_id.'#comments');
            Notification::create((int)$item['user_id'], 'comment_new', 'item', $item_id, $title, $body, $url);
        }

        set_flash('success', 'Comment posted.');
        redirect('index.php?r=items/show&id='.$item_id.'#comments');
    }
}