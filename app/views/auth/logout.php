<div class="container container-1200 py-5">
  <div class="lnf-card card"><div class="card-body p-4">
    <h5 class="mb-3">Log out</h5>
    <form method="post" action="<?= e(base_url('index.php?r=auth/logout')) ?>">
      <?= csrf_field() ?>
      <p>Are you sure you want to log out?</p>
      <button class="btn btn-outline-brand">Logout</button>
      <a class="btn btn-secondary ms-2" href="<?= e(base_url('index.php')) ?>">Cancel</a>
    </form>
  </div></div>
</div>