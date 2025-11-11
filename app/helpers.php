<?php
function base_url(string $path = ''): string {
    $base = rtrim(APP_URL, '/') . '/';
    return $base . ltrim($path, '/');
}

function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

function redirect(string $path): void {
    header('Location: ' . (str_starts_with($path, 'http') ? $path : base_url($path)));
    exit;
}

function is_logged_in(): bool { return isset($_SESSION['user_id']); }
function current_user_id(): int { return (int)($_SESSION['user_id'] ?? 0); }
function current_user(): ?array {
    if (!is_logged_in()) return null;
    return User::findById(current_user_id());
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('index.php?r=auth/login');
    }
}

function require_admin(): void {
    require_login();
    $u = current_user();
    if (!$u || (int)$u['is_admin'] !== 1) {
        http_response_code(403);
        render('errors/403');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_token" value="'.e(csrf_token()).'">';
}
function verify_csrf(): void {
    $token = $_POST['_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function set_flash(string $key, string $value): void { $_SESSION['flash'][$key] = $value; }
function get_flash(string $key): ?string {
    if (isset($_SESSION['flash'][$key])) { $v = $_SESSION['flash'][$key]; unset($_SESSION['flash'][$key]); return $v; }
    return null;
}

function render(string $view, array $data = []): void {
    extract($data, EXTR_SKIP);
    $normalized = str_replace(['\\', '..'], ['/', ''], $view);
    $viewFile = __DIR__ . '/views/' . $normalized . '.php';

    if (!file_exists($viewFile)) {
        http_response_code(404);
        if (defined('APP_ENV') && APP_ENV === 'dev') {
            exit('View not found at: ' . $viewFile);
        }
        exit('View not found.');
    }

    ob_start();
    include $viewFile;
    $content = ob_get_clean();

    include __DIR__ . '/views/layout.php';
}

/* Upload helpers */
function uploads_path(string $rel = ''): string {
    $p = UPLOADS_DIR . DIRECTORY_SEPARATOR . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel), DIRECTORY_SEPARATOR);
    return $p;
}
function uploads_url(string $rel = ''): string {
    return base_url('assets/uploads/' . ltrim($rel, '/'));
}