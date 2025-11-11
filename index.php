<?php
require __DIR__ . '/app/bootstrap.php';

$route = $_GET['r'] ?? 'home';

if (!empty($_SESSION['must_set_password']) && !in_array($route, ['auth/setup_password','auth/logout'], true)) {
    redirect('index.php?r=auth/setup_password');
}

switch ($route) {
  case 'home': HomeController::index(); break;

  // Auth
  case 'auth/login': AuthController::login(); break;
  case 'auth/setup_password': AuthController::setupPassword(); break;
  case 'auth/logout':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AuthController::logout(); }
    else { render('auth/logout'); }
    break;

  // Profile
  case 'profile': ProfileController::index(); break;

  // Items (public)
  case 'items': ItemsController::index(); break;
  case 'items/show': ItemsController::show(); break;

  // Items (create + self delete)
  case 'items/create': ItemsController::create(); break;
  case 'items/store':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { ItemsController::store(); }
    else { redirect('index.php?r=items/create'); }
    break;
  case 'items/delete':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { ItemsController::deleteSelf(); }
    else { redirect('index.php'); }
    break;

  // Comments
  case 'comments/store':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { CommentsController::store(); }
    else { redirect('index.php'); }
    break;

  // Reports
  case 'reports/store':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { ReportsController::store(); }
    else { redirect('index.php'); }
    break;

  // Claims
  case 'claims/store':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { ClaimsController::store(); }
    else { redirect('index.php'); }
    break;
  case 'claims/cancel':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { ClaimsController::cancel(); }
    else { redirect('index.php'); }
    break;

  // Notifications
  case 'notifications': NotificationsController::index(); break;
  case 'notifications/mark_all':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { NotificationsController::markAll(); }
    else { redirect('index.php?r=notifications'); }
    break;
  case 'notifications/mark_read':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { NotificationsController::markRead(); }
    else { redirect('index.php?r=notifications'); }
    break;
  case 'notifications/poll': NotificationsController::poll(); break;

  // Admin: items moderation + search + pagination + bulk actions
  case 'admin/items': AdminController::itemsPending(); break;
  case 'admin/items/approve':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::approve(); }
    else { redirect('index.php?r=admin/items'); }
    break;
  case 'admin/items/reject':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::reject(); }
    else { redirect('index.php?r=admin/items'); }
    break;
  case 'admin/items/delete':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::deleteItem(); }
    else { redirect('index.php?r=admin/items'); }
    break;
  case 'admin/items/bulk_delete':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::bulkDelete(); }
    else { redirect('index.php?r=admin/items'); }
    break;
  case 'admin/items/bulk_approve':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::bulkApprove(); }
    else { redirect('index.php?r=admin/items'); }
    break;
  case 'admin/items/bulk_reject':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::bulkReject(); }
    else { redirect('index.php?r=admin/items'); }
    break;

  // Admin: reports + comments delete
  case 'admin/reports': AdminController::reports(); break;
  case 'admin/reports/resolve':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::resolveReport(); }
    else { redirect('index.php?r=admin/reports'); }
    break;
  case 'admin/reports/dismiss':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::dismissReport(); }
    else { redirect('index.php?r=admin/reports'); }
    break;
  case 'admin/comments/delete':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::deleteComment(); }
    else { redirect('index.php?r=admin/reports'); }
    break;

  // Admin: claims moderation
  case 'admin/claims': AdminController::claimsPending(); break;
  case 'admin/claims/approve':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::approveClaim(); }
    else { redirect('index.php?r=admin/claims'); }
    break;
  case 'admin/claims/reject':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminController::rejectClaim(); }
    else { redirect('index.php?r=admin/claims'); }
    break;

  // Admin: user management
  case 'admin/users': AdminUsersController::index(); break;
  case 'admin/users/store':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminUsersController::store(); }
    else { redirect('index.php?r=admin/users'); }
    break;
  case 'admin/users/delete':
    if ($_SERVER['REQUEST_METHOD'] === 'POST') { AdminUsersController::delete(); }
    else { redirect('index.php?r=admin/users'); }
    break;

  // Admin: Audit only
  case 'admin/audit': AdminUtilitiesController::audit(); break;

  default:
    http_response_code(404);
    render('errors/404');
}