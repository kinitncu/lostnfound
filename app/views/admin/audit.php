<div class="container container-1200 py-4">
  <h2 class="mb-3">Audit Logs</h2>
  <p class="text-muted small">Last 200 admin actions.</p>

  <?php if (empty($rows)): ?>
    <div class="alert alert-secondary">No audit logs yet.</div>
  <?php else: ?>
    <div class="table-responsive lnf-card card">
      <table class="table table-sm align-middle mb-0">
        <thead>
          <tr>
            <th>When</th>
            <th>Admin</th>
            <th>Action</th>
            <th>Subject</th>
            <th>Meta</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><small class="text-muted"><?= date('M j, Y g:i A', strtotime($r['created_at'])) ?></small></td>
            <td class="small"><?= e($r['admin_name'] ?: $r['admin_sid']) ?></td>
            <td class="small"><span class="badge bg-dark"><?= e($r['action']) ?></span></td>
            <td class="small"><?= e($r['subject_type'] ?? '—') ?> #<?= e((string)($r['subject_id'] ?? '—')) ?></td>
            <td class="small">
              <?php if (!empty($r['metadata'])): ?>
                <code class="small"><?= e($r['metadata']) ?></code>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>