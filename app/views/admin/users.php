<div class="container container-1200 py-4">
  <h2 class="mb-3">Users</h2>

  <?php if ($msg = get_flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($msg = get_flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="lnf-card card p-3">
        <h5 class="mb-2">Create user</h5>
        <form method="post" action="<?= e(base_url('index.php?r=admin/users/store')) ?>">
          <?= csrf_field() ?>
          <div class="mb-2">
            <label class="form-label">School ID (10 digits)</label>
            <input type="text" class="form-control" name="school_id" inputmode="numeric" maxlength="10" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email">
          </div>
          <div class="mb-2">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone">
          </div>

          <div class="mb-2">
            <label class="form-label">User type</label>
            <select class="form-select" name="user_type">
              <option value="">—</option>
              <option value="student">Student</option>
              <option value="staff">Staff</option>
            </select>
          </div>

          <div class="row g-2">
            <div class="col-md-4"><input class="form-control" name="first_name" placeholder="First name"></div>
            <div class="col-md-4"><input class="form-control" name="middle_name" placeholder="Middle name"></div>
            <div class="col-md-4"><input class="form-control" name="last_name" placeholder="Last name"></div>
          </div>

          <div class="row g-2 mt-1">
            <div class="col-md-4"><input class="form-control" name="department" placeholder="Department"></div>
            <div class="col-md-4"><input class="form-control" name="year_level" placeholder="Year level"></div>
            <div class="col-md-4"><input class="form-control" name="section" placeholder="Section"></div>
          </div>
          <div class="mt-1">
            <input class="form-control" name="position_title" placeholder="Position">
          </div>

          <div class="mt-2">
            <label class="form-label">Initial password (optional)</label>
            <input type="password" class="form-control" name="password" placeholder="Leave blank to require first-time setup">
          </div>

          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_admin" id="isAdmin">
            <label class="form-check-label" for="isAdmin">Grant admin privileges</label>
          </div>

          <div class="mt-3">
            <button class="btn btn-brand">Create</button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="lnf-card card p-3">
        <h5 class="mb-2">All users</h5>
        <?php if (empty($users)): ?>
          <div class="alert alert-secondary">No users yet.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>School ID</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Admin</th>
                  <th>Contact</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= e($u['school_id']) ?></td>
                    <td><?= e($u['name'] ?? trim(($u['first_name'] ?? '').' '.($u['last_name'] ?? ''))) ?></td>
                    <td><?= e($u['user_type'] ?? '—') ?></td>
                    <td><?= ((int)$u['is_admin']===1)?'Yes':'No' ?></td>
                    <td class="small">
                      <?php if (!empty($u['email'])): ?><?= e($u['email']) ?><br><?php endif; ?>
                      <?php if (!empty($u['phone'])): ?><?= e($u['phone']) ?><?php endif; ?>
                    </td>
                    <td class="text-end">
                      <?php if ((int)$u['id'] !== (int)current_user_id()): ?>
                        <form method="post" action="<?= e(base_url('index.php?r=admin/users/delete')) ?>" class="d-inline" onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted small">Self</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>