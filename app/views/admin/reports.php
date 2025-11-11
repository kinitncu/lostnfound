<div class="container container-1200 py-4">
  <h2 class="mb-3">Reports</h2>

  <?php if ($msg = get_flash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
  <?php endif; ?>

  <h5 class="mt-3">Open reports</h5>
  <?php if (empty($open)): ?>
    <div class="alert alert-secondary">No open reports.</div>
  <?php else: ?>
    <div class="table-responsive lnf-card card mb-4">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Target</th>
            <th>Reason</th>
            <th>Reporter</th>
            <th>When</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($open as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><span class="badge bg-warning text-dark"><?= e(ucfirst($r['type'])) ?></span></td>
            <td>
              <?php if ($r['type'] === 'item'): ?>
                <div class="small">
                  <div class="fw-semibold">Item #<?= (int)$r['target_id'] ?>: <?= e($r['item_title'] ?? '—') ?></div>
                  <a target="_blank" href="<?= e(base_url('index.php?r=items/show&id='.(int)($r['item_id'] ?? $r['target_id']))) ?>">View</a>
                </div>
              <?php else: ?>
                <div class="small">
                  <div class="fw-semibold">Comment #<?= (int)$r['target_id'] ?> (<?= e($r['comment_status'] ?? 'unknown') ?>)</div>
                  <div class="text-muted"><?= e(mb_strimwidth($r['comment_content'] ?? '', 0, 120, '…')) ?></div>
                  <?php $cidItem = (int)($r['comment_item_id'] ?? $r['item_id']); ?>
                  <a target="_blank" href="<?= e(base_url('index.php?r=items/show&id='.$cidItem)) ?>">View Item</a>
                </div>
              <?php endif; ?>
            </td>
            <td><div class="small"><?= nl2br(e($r['reason'])) ?></div></td>
            <td><div class="small"><?= e($r['reporter_name'] ?: $r['reporter_school_id']) ?></div></td>
            <td><small class="text-muted"><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></small></td>
            <td class="text-end">
              <?php if ($r['type'] === 'comment'): ?>
                <form method="post" action="<?= e(base_url('index.php?r=admin/comments/delete')) ?>" class="d-inline" onsubmit="return confirm('Delete this comment permanently?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$r['target_id'] ?>">
                  <button class="btn btn-sm btn-danger">Delete comment</button>
                </form>
              <?php endif; ?>

              <form method="post" action="<?= e(base_url('index.php?r=admin/reports/resolve')) ?>" class="d-inline ms-1">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm btn-primary">Resolve</button>
              </form>
              <form method="post" action="<?= e(base_url('index.php?r=admin/reports/dismiss')) ?>" class="d-inline ms-1">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button class="btn btn-sm btn-outline-secondary">Dismiss</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h5>Recent resolved/dismissed</h5>
  <?php if (empty($recent)): ?>
    <div class="alert alert-secondary">None yet.</div>
  <?php else: ?>
    <div class="table-responsive lnf-card card">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Target</th>
            <th>Status</th>
            <th>When</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= e(ucfirst($r['type'])) ?></td>
              <td>
                <?php if ($r['type'] === 'item'): ?>
                  <div class="small">Item #<?= (int)$r['target_id'] ?>: <?= e($r['item_title'] ?? '—') ?></div>
                <?php else: ?>
                  <div class="small">Comment #<?= (int)$r['target_id'] ?> — <?= e(mb_strimwidth($r['comment_content'] ?? '', 0, 60, '…')) ?> (<?= e($r['comment_status'] ?? 'unknown') ?>)</div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $r['status']==='resolved'?'bg-success':'bg-secondary' ?>">
                  <?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td><small class="text-muted"><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></small></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>