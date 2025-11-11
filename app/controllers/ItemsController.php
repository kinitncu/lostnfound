<?php
class ItemsController {
    private static function isYmd(?string $s): bool {
        if (!$s) return false;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
        [$y,$m,$d] = explode('-', $s);
        return checkdate((int)$m,(int)$d,(int)$y);
    }

    public static function index(): void {
        $type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : null;
        $q = trim($_GET['q'] ?? '');
        if (!in_array($type, ['lost','found'], true)) $type = null;

        $campus   = trim($_GET['campus'] ?? '');
        $location = trim($_GET['location'] ?? '');
        $state    = strtolower(trim($_GET['state'] ?? ''));
        // Normalize state aliases
        if ($state === 'inclaim') $state = 'in_claim';
        if (!in_array($state, ['open','in_claim','returned','all',''], true)) $state = '';

        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo   = trim($_GET['date_to'] ?? '');
        $sort     = strtolower(trim($_GET['sort'] ?? 'newest'));
        if (!in_array($sort, ['newest','oldest'], true)) $sort = 'newest';

        // Validate dates (ignore invalid ones)
        if (!self::isYmd($dateFrom)) $dateFrom = '';
        if (!self::isYmd($dateTo))   $dateTo   = '';

        $isFiltered = (bool)($type || $q !== '' || $campus !== '' || $location !== '' || $state !== '' || $dateFrom !== '' || $dateTo !== '' || $sort !== 'newest');

        $items = [];
        $error = null;
        try {
            $items = Item::listApproved(
                $type,
                $q !== '' ? $q : null,
                $campus !== '' ? $campus : null,
                $location !== '' ? $location : null,
                $state !== '' ? $state : null,
                $dateFrom !== '' ? $dateFrom : null,
                $dateTo   !== '' ? $dateTo   : null,
                $sort,
                24,
                0
            );
        } catch (Throwable $e) {
            // Friendly error for user; details can be logged to Audit if needed
            $error = 'We couldn’t run your search right now. Please adjust your filters or try again.';
        }

        // Attach primary thumb
        $list = [];
        foreach ($items as $it) {
            $img = ItemImage::primaryByItem((int)$it['id']);
            $it['thumb_url'] = $img ? uploads_url('items/'.$it['id'].'/'.$img['filename']) : null;
            $list[] = $it;
        }

        render('items/index', [
            'items'      => $list,
            'type'       => $type,
            'q'          => $q,
            'campus'     => $campus,
            'location'   => $location,
            'state'      => $state,
            'date_from'  => $dateFrom,
            'date_to'    => $dateTo,
            'sort'       => $sort,
            'isFiltered' => $isFiltered,
            'error'      => $error
        ]);
    }

    public static function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(404); render('errors/404'); return; }

        $item = Item::findApprovedById($id);
        if (!$item) { http_response_code(404); render('errors/404'); return; }

        $images = ItemImage::allByItem($id);
        $user   = User::findById((int)$item['user_id']);
        $comments = Comment::visibleByItem($id);
        $category = null;

        render('items/show', compact('item','images','user','comments','category'));
    }

    public static function create(): void {
        require_login();
        $error = get_flash('error');
        $old = $_SESSION['old_item'] ?? [];
        unset($_SESSION['old_item']);
        render('items/create', compact('error','old'));
    }

    public static function store(): void {
        require_login();
        verify_csrf();

        $type        = strtolower(trim($_POST['type'] ?? ''));
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $campus      = trim($_POST['campus'] ?? '');
        $location    = trim($_POST['location'] ?? '');
        $primary_index = isset($_POST['primary_index']) ? (int)$_POST['primary_index'] : 0;

        $errors = [];
        if (!in_array($type, ['lost','found'], true)) $errors[] = 'Type must be Lost or Found.';
        if ($title === '' || mb_strlen($title) > 160) $errors[] = 'Title is required (max 160 chars).';

        $files = $_FILES['photos'] ?? null;
        $fileCount = 0;
        if ($files && is_array($files['name'])) {
            for ($i=0; $i<count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                $fileCount++;
                if ($files['error'][$i] !== UPLOAD_ERR_OK) $errors[] = 'One or more photos failed to upload.';
                if ($files['size'][$i] > 2*1024*1024) $errors[] = 'Images must be 2MB or smaller.';
                $info = @getimagesize($files['tmp_name'][$i]);
                if (!$info || !in_array($info['mime'], ['image/jpeg','image/png'], true)) {
                    $errors[] = 'Only JPG and PNG images are allowed.';
                }
            }
        }
        if ($fileCount > 5) $errors[] = 'You can upload a maximum of 5 images.';
        if ($fileCount === 1) { $primary_index = 0; }

        if (!is_dir(UPLOADS_DIR))    $errors[] = 'Uploads directory is missing: ' . UPLOADS_DIR;
        if (!is_writable(UPLOADS_DIR)) $errors[] = 'Uploads directory is not writable. Please enable write permissions for assets/uploads.';

        if (!empty($errors)) {
            $_SESSION['old_item'] = compact('type','title','description','campus','location');
            set_flash('error', implode(' ', $errors));
            redirect('index.php?r=items/create');
        }

        $item_id = Item::create(
            current_user_id(),
            $type,
            $title,
            $description !== '' ? $description : null,
            $campus !== '' ? $campus : null,
            $location !== '' ? $location : null,
            null
        );

        $destDir = uploads_path('items/' . $item_id);
        if (!is_dir($destDir)) { @mkdir($destDir, 0775, true); }

        $uploaded = 0;
        $primarySet = false;
        $firstImageId = 0;

        if ($files && is_array($files['name'])) {
            for ($i=0; $i<count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                if ($files['size'][$i] > 2*1024*1024) continue;
                $info = @getimagesize($files['tmp_name'][$i]);
                if (!$info || !in_array($info['mime'], ['image/jpeg','image/png'], true)) continue;

                $ext = $info['mime'] === 'image/png' ? 'png' : 'jpg';
                $name = bin2hex(random_bytes(16)) . '.' . $ext;
                $destPath = $destDir . DIRECTORY_SEPARATOR . $name;

                $moved = @move_uploaded_file($files['tmp_name'][$i], $destPath);
                if (!$moved) $moved = @rename($files['tmp_name'][$i], $destPath);

                if ($moved && file_exists($destPath)) {
                    $makePrimary = (!$primarySet && ($i === $primary_index));
                    $imageId = ItemImage::add($item_id, $name, $makePrimary);
                    if ($firstImageId === 0) $firstImageId = $imageId;
                    if ($makePrimary) $primarySet = true;
                    $uploaded++;
                }
                if ($uploaded >= 5) break;
            }
        }

        if (!$primarySet && $firstImageId > 0) {
            ItemImage::setPrimary($item_id, $firstImageId);
        }

        if ($fileCount > 0 && $uploaded === 0) {
            set_flash('success', 'Item submitted. Note: images could not be saved (check folder permissions).');
        } else {
            set_flash('success', 'Item submitted and is pending moderation.');
        }

        redirect('index.php?r=items');
    }

    public static function deleteSelf(): void {
        require_login();
        verify_csrf();

        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect'] ?? 'index.php?r=items';
        if ($id <= 0) { set_flash('error','Invalid item.'); redirect($redirect); }

        $item = Item::findById($id);
        if (!$item) { set_flash('error','Item not found.'); redirect($redirect); }

        $isOwner = ((int)$item['user_id'] === current_user_id());
        $isAdmin = (int)(current_user()['is_admin'] ?? 0) === 1;

        if (!$isOwner && !$isAdmin) {
            set_flash('error','You do not have permission to delete this post.');
            redirect($redirect);
        }

        Item::deleteWithFiles($id);

        if ($isAdmin) {
            AuditLog::record(current_user_id(), 'item_delete', 'item', $id, ['title' => $item['title'] ?? null, 'by' => 'admin']);
            set_flash('success','Item deleted.');
        } else {
            AuditLog::record(current_user_id(), 'item_delete_self', 'item', $id, ['title' => $item['title'] ?? null]);
            set_flash('success','Your post was deleted.');
        }

        redirect($redirect);
    }
}