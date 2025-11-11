<?php
require __DIR__ . '/../app/bootstrap.php';
header('Content-Type: text/plain');

$u = current_user();
if (!$u || (int)$u['is_admin'] !== 1) {
    http_response_code(403);
    exit("403: Admin only.\n");
}

echo "APP_URL: " . APP_URL . PHP_EOL;
echo "BASE_PATH: " . BASE_PATH . PHP_EOL;
echo "UPLOADS_DIR: " . UPLOADS_DIR . PHP_EOL;
echo "Uploads dir exists: " . (is_dir(UPLOADS_DIR) ? 'YES' : 'NO') . PHP_EOL;
echo "Uploads dir writable: " . (is_writable(UPLOADS_DIR) ? 'YES' : 'NO') . PHP_EOL;

// Try a write test
$testFile = uploads_path('_write_test.txt');
$ok = @file_put_contents($testFile, "ok @ " . date('c'));
echo "Write test: " . ($ok !== false ? "OK ($testFile)" : "FAILED") . PHP_EOL;
if ($ok !== false) {
    @unlink($testFile);
}

// Show last 5 items and their primary image paths
echo PHP_EOL . "Recent items & primary image availability:" . PHP_EOL;
$rows = pdo()->query('SELECT id FROM items ORDER BY id DESC LIMIT 5')->fetchAll();
if (!$rows) {
    echo "No items yet." . PHP_EOL;
} else {
    foreach ($rows as $r) {
        $id = (int)$r['id'];
        $img = ItemImage::primaryByItem($id);
        if ($img) {
            $fs = uploads_path('items/'.$id.'/'.$img['filename']);
            $url = uploads_url('items/'.$id.'/'.$img['filename']);
            echo "Item #$id -> " . $img['filename'] . PHP_EOL;
            echo "  FS exists: " . (file_exists($fs) ? 'YES' : 'NO') . " | " . $fs . PHP_EOL;
            echo "  URL: " . $url . PHP_EOL;
        } else {
            echo "Item #$id -> no image rows." . PHP_EOL;
        }
    }
}

echo PHP_EOL . "If writable=NO, set write permissions for /assets/uploads in your control panel and try again." . PHP_EOL;