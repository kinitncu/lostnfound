<div class="container container-1200 py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="lnf-card card"><div class="card-body p-4">
        <h4 class="mb-3">Set your password</h4>
        <p class="text-muted small">You're signed in for the first time. Create a password to continue.</p>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="<?= e(base_url('index.php?r=auth/setup_password')) ?>" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
          </div>
          <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
          </div>
          <button type="submit" class="btn btn-brand w-100">Save password</button>
        </form>
      </div></div>
    </div>
  </div>
</div>