<div class="container container-1200 py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="lnf-card card"><div class="card-body p-4">
        <h4 class="mb-3">Login</h4>
        <p class="text-muted small">Use your 10-digit School ID. First-time users will set a password next.</p>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="<?= e(base_url('index.php?r=auth/login')) ?>" novalidate>
          <?= csrf_field() ?>
          <div class="mb-3">
            <label for="school_id" class="form-label">School ID</label>
            <input type="text" inputmode="numeric" pattern="\d{10}" maxlength="10" class="form-control" id="school_id" name="school_id" required value="<?= e($old['school_id'] ?? '') ?>">
            <div class="form-text">Enter your 10-digit ID (numbers only).</div>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank if first-time login">
          </div>
          <button type="submit" class="btn btn-brand w-100">Sign in</button>
        </form>
      </div></div>
      <p class="text-center text-muted small mt-3">Need help? Contact SDFO.</p>
    </div>
  </div>
</div>