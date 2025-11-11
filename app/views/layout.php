<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Lost and Found - Systems Plus College Foundation</title>

  <!-- Favicons (tab logo) -->
  <link rel="icon" href="<?= e(base_url('assets/img/favicon.ico')) ?>" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= e(base_url('assets/img/favicon-32.png')) ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= e(base_url('assets/img/favicon-16.png')) ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= e(base_url('assets/img/apple-touch-icon.png')) ?>">
  <!-- Fallback: use logo if specific favicon files are not yet uploaded -->
  <link rel="shortcut icon" href="<?= e(base_url('assets/img/logo-badge.png')) ?>">
  <meta name="theme-color" content="#2E1A6B">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <script>window.BASE_URL="<?= e(base_url()) ?>";</script>
</head>
<body class="bg-muted d-flex flex-column min-vh-100">
  <header id="app-header" class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-brand py-2">
      <div class="container container-1200">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(base_url('index.php')) ?>">
          <img src="<?= e(base_url('assets/img/logo-badge.png')) ?>" alt="Systems Plus College Foundation" class="logo-badge">
          <span class="fw-semibold">Systems Plus College Foundation</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topNav">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
            <li class="nav-item"><a class="nav-link" href="<?= e(base_url('index.php')) ?>">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= e(base_url('index.php?r=items')) ?>">Items</a></li>
            <?php if (is_logged_in()): ?>
              <li class="nav-item"><a class="nav-link" href="<?= e(base_url('index.php?r=items/create')) ?>">Report Item</a></li>
              <li class="nav-item">
                <a class="nav-link position-relative" href="<?= e(base_url('index.php?r=notifications')) ?>">
                  Notifications <span id="notifCount" class="badge rounded-pill bg-danger ms-1 d-none">0</span>
                </a>
              </li>
              <?php if ((current_user()['is_admin'] ?? 0) == 1): ?>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Admin</a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= e(base_url('index.php?r=admin/items')) ?>">Moderation</a></li>
                    <li><a class="dropdown-item" href="<?= e(base_url('index.php?r=admin/reports')) ?>">Reports</a></li>
                    <li><a class="dropdown-item" href="<?= e(base_url('index.php?r=admin/claims')) ?>">Claims</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= e(base_url('index.php?r=admin/users')) ?>">Users</a></li>
                    <li><a class="dropdown-item" href="<?= e(base_url('index.php?r=admin/audit')) ?>">Audit</a></li>
                  </ul>
                </li>
              <?php endif; ?>
              <li class="nav-item ms-lg-2">
                <a class="nav-link" href="<?= e(base_url('index.php?r=profile')) ?>">Profile</a>
              </li>
              <li class="nav-item ms-lg-2">
                <form method="post" action="<?= e(base_url('index.php?r=auth/logout')) ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-light" type="submit">Logout</button>
                </form>
              </li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="<?= e(base_url('index.php?r=auth/login')) ?>">Login</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
  </header>

  <?php if ($msg = get_flash('error')): ?>
    <div class="alert alert-danger rounded-0 text-center mb-0"><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = get_flash('success')): ?>
    <div class="alert alert-success rounded-0 text-center mb-0"><?= e($msg) ?></div>
  <?php endif; ?>

  <main class="flex-grow-1">
    <?= $content ?>
  </main>

  <footer class="bg-dark text-white mt-4">
    <div class="container container-1200 py-4 d-flex align-items-center">
      <img src="<?= e(base_url('assets/img/logo-badge.png')) ?>" class="logo-badge me-2" alt="Systems Plus College Foundation">
      <div>
        <div class="fw-semibold">Systems Plus College Foundation</div>
        <small class="text-white-50">© <?= date('Y') ?> All rights reserved</small>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="<?= e(base_url('assets/js/app.js?v=16')) ?>"></script>
</body>
</html>