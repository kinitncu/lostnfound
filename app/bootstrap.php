<?php
require __DIR__.'/config.php';
require __DIR__.'/session.php';
require __DIR__.'/helpers.php';
require __DIR__.'/db.php';

// Ensure uploads directories exist (best-effort)
if (!is_dir(UPLOADS_DIR)) { @mkdir(UPLOADS_DIR, 0775, true); }
$uploadsItems = UPLOADS_DIR . DIRECTORY_SEPARATOR . 'items';
if (!is_dir($uploadsItems)) { @mkdir($uploadsItems, 0775, true); }
$uploadsAvatars = UPLOADS_DIR . DIRECTORY_SEPARATOR . 'avatars';
if (!is_dir($uploadsAvatars)) { @mkdir($uploadsAvatars, 0775, true); }

require __DIR__.'/models/User.php';
require __DIR__.'/models/Item.php';
require __DIR__.'/models/ItemImage.php';
require __DIR__.'/models/Comment.php';
require __DIR__.'/models/Report.php';
require __DIR__.'/models/Claim.php';
require __DIR__.'/models/Notification.php';
require __DIR__.'/models/Category.php';
require __DIR__.'/models/Campus.php';
require __DIR__.'/models/Location.php';
require __DIR__.'/models/AuditLog.php';

require __DIR__.'/controllers/HomeController.php';
require __DIR__.'/controllers/AuthController.php';
require __DIR__.'/controllers/ProfileController.php';
require __DIR__.'/controllers/ItemsController.php';
require __DIR__.'/controllers/CommentsController.php';
require __DIR__.'/controllers/ReportsController.php';
require __DIR__.'/controllers/ClaimsController.php';
require __DIR__.'/controllers/NotificationsController.php';
require __DIR__.'/controllers/AdminController.php';
require __DIR__.'/controllers/AdminUtilitiesController.php';
require __DIR__.'/controllers/AdminUsersController.php';