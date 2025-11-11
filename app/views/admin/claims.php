<div class="container container-1200 py-4">
  <h2 class="mb-3">Claims (Pending)</h2>

  <?php if (empty($claims)): ?>
    <div class="alert alert-secondary">No pending claims.</div>
  <?php else: ?>
    <div class="table-responsive lnf-card card">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Item</th>
            <th>Claimer</th>
            <th>Message</th>
            <th>When</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($claims as $c): ?>
          <tr>
            <td><?= (int)$c['id'] ?></td>
            <td>
              <div class="small">
                <div class="fw-semibold"><?= e($c['title']) ?></div>
                <div>
                  <span class="badge <?= $c['type']==='lost' ? 'bg-danger' : 'bg-success' ?>"><?= e(ucfirst($c['type'])) ?></span>
                  <span class="badge bg-info text-dark ms-1"><?= e(ucfirst($c['item_state'])) ?></span>
                </div>
                <a class="small" target="_blank" href="<?= e(base_url('index.php?r=items/show&id='.(int)$c['item_id'])) ?>">View item</a>
              </div>
            </td>
            <td class="small"><?= e($c['claimer_name'] ?: $c['claimer_school_id']) ?></td>
            <td class="small"><?= e($c['message'] ?? '—') ?></td>
            <td><small class="text-muted"><?= date('M j, Y g:i A', strtotime($c['created_at'])) ?></small></td>
            <td class="text-end">
              <form method="post" action="<?= e(base_url('index.php?r=admin/claims/approve')) ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <input type="hidden" name="item_id" value="<?= (int)$c['item_id'] ?>">
                <button class="btn btn-sm btn-success">Approve</button>
              </form>
              <form method="post" action="<?= e(base_url('index.php?r=admin/claims/reject')) ?>" class="d-inline ms-1">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <input type="hidden" name="item_id" value="<?= (int)$c['item_id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Reject</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>