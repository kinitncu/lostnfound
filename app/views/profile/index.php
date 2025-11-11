<div class="container container-1200 py-5">
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="lnf-card card h-100"><div class="card-body p-4">
        <h5 class="mb-3">Profile</h5>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <?php if (!empty($success)): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

        <form method="post" action="<?= e(base_url('index.php?r=profile')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="update_profile">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">School ID</label>
              <input type="text" class="form-control" value="<?= e($user['school_id'] ?? '') ?>" disabled>
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <input type="text" class="form-control" value="<?= e(ucfirst($user['user_type'] ?? '')) ?>" disabled>
            </div>

            <div class="col-md-4">
              <label class="form-label">First name</label>
              <input type="text" class="form-control" name="first_name" value="<?= e($user['first_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Middle name</label>
              <input type="text" class="form-control" name="middle_name" value="<?= e($user['middle_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Last name</label>
              <input type="text" class="form-control" name="last_name" value="<?= e($user['last_name'] ?? '') ?>">
            </div>

            <?php if (($user['user_type'] ?? '') === 'student'): ?>
              <div class="col-md-4">
                <label class="form-label">Department</label>
                <input type="text" class="form-control" name="department" value="<?= e($user['department'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Year level</label>
                <input type="text" class="form-control" name="year_level" value="<?= e($user['year_level'] ?? '') ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Section</label>
                <input type="text" class="form-control" name="section" value="<?= e($user['section'] ?? '') ?>">
              </div>
            <?php elseif (($user['user_type'] ?? '') === 'staff'): ?>
              <div class="col-md-12">
                <label class="form-label">Position</label>
                <input type="text" class="form-control" name="position_title" value="<?= e($user['position_title'] ?? '') ?>">
              </div>
            <?php else: ?>
              <div class="col-12">
                <div class="alert alert-info mb-0">
                  Your role isn’t set. Please contact an administrator to set your role to Student or Staff.
                </div>
              </div>
            <?php endif; ?>

            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" value="<?= e($user['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" class="form-control" name="phone" value="<?= e($user['phone'] ?? '') ?>">
            </div>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-brand">Save profile</button>
          </div>
        </form>
      </div></div>
    </div>

    <div class="col-lg-5">
      <div class="lnf-card card h-100"><div class="card-body p-4">
        <h5 class="mb-3">Change password</h5>
        <form method="post" action="<?= e(base_url('index.php?r=profile')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="change_password">
          <div class="mb-3">
            <label class="form-label">Current password</label>
            <input type="password" class="form-control" name="current_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New password</label>
            <input type="password" class="form-control" name="new_password" required minlength="8">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm new password</label>
            <input type="password" class="form-control" name="new_password_confirm" required minlength="8">
          </div>
          <button class="btn btn-outline-brand">Update password</button>
        </form>
      </div></div>
    </div>
  </div>
</div>