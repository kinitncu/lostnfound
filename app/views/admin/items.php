<?php
  $qs = http_build_query([
    'r' => 'admin/items',
    'status' => $status ?? 'pending',
    'type' => $type ?? '',
    'q' => $q ?? '',
    'page' => $page ?? 1,
  ]);
  $returnUrl = base_url('index.php?'.$qs);

  $hasAdminFilter = (($status ?? 'pending') !== 'pending') || (($type ?? '') !== '') || (($q ?? '') !== '');
?>
<div class="container container-1200 py-4">
  <h2 class="mb-3">Items</h2>

  <form class="lnf-card card p-3 mb-3" method="get" action="<?= e(base_url('index.php')) ?>">
    <input type="hidden" name="r" value="admin/items">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="pending" <?= ($status ?? 'pending')==='pending'?'selected':'' ?>>Pending</option>
          <option value="approved" <?= ($status ?? '')==='approved'?'selected':'' ?>>Approved</option>
          <option value="rejected" <?= ($status ?? '')==='rejected'?'selected':'' ?>>Rejected</option>
          <option value="all" <?= ($status ?? '')==='all'?'selected':'' ?>>All</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Type</label>
        <select class="form-select" name="type">
          <option value="" <?= ($type ?? '')===''?'selected':'' ?>>All</option>
          <option value="lost" <?= ($type ?? '')==='lost'?'selected':'' ?>>Lost</option>
          <option value="found" <?= ($type ?? '')==='found'?'selected':'' ?>>Found</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Search</label>
        <input class="form-control" name="q" value="<?= e($q ?? '') ?>" placeholder="Title, description, or location">
      </div>
      <div class="col-md-2">
        <button class="btn btn-outline-brand w-100 mt-3 mt-md-0">Filter</button>
      </div>
    </div>
  </form>

  <?php if ($msg = get_flash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center">
      <div>No items match your current filters.</div>
      <?php if ($hasAdminFilter): ?>
        <a href="<?= e(base_url('index.php?r=admin/items')) ?>" class="btn btn-sm btn-outline-dark">Clear filters</a>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <form id="bulkForm" method="post" class="mb-2 d-flex gap-2 flex-wrap">
      <?= csrf_field() ?>
      <input type="hidden" name="redirect" value="<?= e($returnUrl) ?>">
      <?php if (($status ?? 'pending') === 'pending' || ($status ?? '') === 'all'): ?>
        <button id="bulkApproveBtn" class="btn btn-success btn-sm" type="submit"
                formaction="<?= e(base_url('index.php?r=admin/items/bulk_approve')) ?>" disabled
                onclick="return confirm('Approve selected pending items?');">
          Approve selected
        </button>
        <button id="bulkRejectBtn" class="btn btn-warning btn-sm" type="submit"
                formaction="<?= e(base_url('index.php?r=admin/items/bulk_reject')) ?>" disabled
                onclick="return confirm('Reject selected pending items?');">
          Reject selected
        </button>
      <?php endif; ?>
      <button id="bulkDeleteBtn" class="btn btn-danger btn-sm" type="submit"
              formaction="<?= e(base_url('index.php?r=admin/items/bulk_delete')) ?>" disabled
              onclick="return confirm('Delete selected items permanently (including images, comments, claims, reports)?');">
        Delete selected
      </button>
    </form>

    <div class="table-responsive lnf-card card">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:36px;"><input type="checkbox" id="checkAll"></th>
            <th>ID</th>
            <th>Type</th>
            <th>Title</th>
            <th>Status</th>
            <th>Submitted by</th>
            <th>When</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><input type="checkbox" class="row-check" name="ids[]" form="bulkForm" value="<?= (int)$it['id'] ?>"></td>
              <td><?= (int)$it['id'] ?></td>
              <td><span class="badge <?= $it['type']==='lost' ? 'bg-danger' : 'bg-success' ?>"><?= e(ucfirst($it['type'])) ?></span></td>
              <td><?= e($it['title']) ?></td>
              <td>
                <?php if ($it['status']==='pending'): ?>
                  <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif ($it['status']==='approved'): ?>
                  <span class="badge bg-success">Approved</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Rejected</span>
                <?php endif; ?>
              </td>
              <td><?= e($it['name'] ?: $it['school_id']) ?></td>
              <td><small class="text-muted"><?= date('M j, Y g:i A', strtotime($it['created_at'])) ?></small></td>
              <td class="text-end">
                <?php if ($it['status']==='pending'): ?>
                  <form method="post" action="<?= e(base_url('index.php?r=admin/items/approve')) ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= e($returnUrl) ?>">
                    <button class="btn btn-sm btn-success">Approve</button>
                  </form>
                  <form method="post" action="<?= e(base_url('index.php?r=admin/items/reject')) ?>" class="d-inline ms-1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                    <input type="hidden" name="redirect" value="<?= e($returnUrl) ?>">
                    <button class="btn btn-sm btn-outline-danger">Reject</button>
                  </form>
                <?php endif; ?>
                <a class="btn btn-sm btn-outline-secondary ms-1" href="<?= e(base_url('index.php?r=items/show&id='.(int)$it['id'])) ?>" target="_blank">View</a>
                <form method="post" action="<?= e(base_url('index.php?r=admin/items/delete')) ?>" class="d-inline ms-1" onsubmit="return confirm('Delete this item permanently (including images, comments, claims, reports)?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                  <input type="hidden" name="redirect" value="<?= e($returnUrl) ?>">
                  <button class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (($pages ?? 1) > 1): ?>
      <nav class="mt-3">
        <ul class="pagination justify-content-center">
          <?php
            $cur = $page ?? 1;
            $totalPages = $pages ?? 1;
            $base = 'index.php?r=admin/items&status='.urlencode($status ?? 'pending').'&type='.urlencode($type ?? '').'&q='.urlencode($q ?? '');
            $range = 2;
            $start = max(1, $cur - $range);
            $end   = min($totalPages, $cur + $range);
          ?>
          <li class="page-item <?= $cur<=1?'disabled':'' ?>">
            <a class="page-link" href="<?= e(base_url($base.'&page='.max(1, $cur-1))) ?>">«</a>
          </li>
          <?php if ($start > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= e(base_url($base.'&page=1')) ?>">1</a></li>
            <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
          <?php endif; ?>
          <?php for ($p = $start; $p <= $end; $p++): ?>
            <li class="page-item <?= $p===$cur?'active':'' ?>">
              <a class="page-link" href="<?= e(base_url($base.'&page='.$p)) ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
          <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
            <li class="page-item"><a class="page-link" href="<?= e(base_url($base.'&page='.$totalPages)) ?>"><?= $totalPages ?></a></li>
          <?php endif; ?>
          <li class="page-item <?= $cur>=$totalPages?'disabled':'' ?>">
            <a class="page-link" href="<?= e(base_url($base.'&page='.min($totalPages, $cur+1))) ?>">»</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script>
  (function(){
    const checkAll = document.getElementById('checkAll');
    const checks = document.querySelectorAll('.row-check');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const bulkRejectBtn = document.getElementById('bulkRejectBtn');

    function updateBulkButtons() {
      let any = false;
      checks.forEach(c => { if (c.checked) any = true; });
      if (bulkDeleteBtn) bulkDeleteBtn.disabled = !any;
      if (bulkApproveBtn) bulkApproveBtn.disabled = !any;
      if (bulkRejectBtn) bulkRejectBtn.disabled = !any;
    }

    if (checkAll) {
      checkAll.addEventListener('change', function(){
        checks.forEach(c => { c.checked = checkAll.checked; });
        updateBulkButtons();
      });
    }
    checks.forEach(c => c.addEventListener('change', updateBulkButtons));
  })();
</script>