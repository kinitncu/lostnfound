<?php
class NotificationsController {
    public static function index(): void {
        require_login();
        $list = Notification::latest(current_user_id(), 50);
        render('notifications/index', ['list' => $list]);
    }

    public static function markAll(): void {
        require_login();
        verify_csrf();
        Notification::markAllRead(current_user_id());
        set_flash('success', 'All notifications marked as read.');
        redirect('index.php?r=notifications');
    }

    public static function markRead(): void {
        require_login();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) Notification::markRead($id, current_user_id());
        redirect('index.php?r=notifications');
    }

    public static function poll(): void {
        header('Content-Type: application/json');
        if (!is_logged_in()) {
            echo json_encode(['ok' => true, 'unread' => 0]); return;
        }
        $c = Notification::unreadCount(current_user_id());
        echo json_encode(['ok' => true, 'unread' => $c]);
    }
}