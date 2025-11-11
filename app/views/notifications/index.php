<?php
function group_label($ts) {
  $d = strtotime($ts);
  $today = strtotime('today');
  $yday  = strtotime('yesterday');
  if ($d >= $today) return 'Today';
  if ($d >= $yday && $d < $today) return 'Yesterday';
  return 'Earlier';
}
$groups = ['Today' => [], 'Yesterday' => [], 'Earlier' => []];
foreach ($list as $n) { $groups[group_label($n['created_at'])][] = $n; }
function notif_icon($type) {
  return match ($type) {
    'comment_new'  => '💬',
    'claim_new'    => '🧾',
    'item_approved'=> '✅',
    'item_rejected'=> '❌',
    'claim_approved'=> '🎉',
    'claim_rejected'=> '⚠️',
    default        => '🔔',
  };
}
?>
<div class="container container-1200 py-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="mb-0">Notifications</h2>
    <form method="post" action="<?= e(base_url('index.php?r=notifications/mark_all')) ?>">
      <?= csrf_field() ?>
      <button class="btn btn-outline-brand btn-sm">Mark all as read</button>
    </form>
  </div>

  <?php if (empty($list)): ?>
    <div class="alert alert-secondary">No notifications yet.</div>
  <?php else: ?>
    <div class="notif-list">
      <?php foreach ($groups as $label => $items): ?>
        <?php if (!empty($items)): ?>
          <div class="notif-group">
            <div class="notif-group-title"><?= e($label) ?></div>
            <?php foreach ($items as $n): ?>
              <div class="notif-item <?= (int)$n['is_read'] ? 'is-read' : 'is-unread' ?>">
                <div class="notif-icon"><?= notif_icon($n['type']) ?></div>
                <div class="notif-content">
                  <div class="notif-title"><?= e($n['title']) ?></div>
                  <?php if (!empty($n['body'])): ?>
                    <div class="notif-body text-muted"><?= nl2br(e($n['body'])) ?></div>
                  <?php endif; ?>
                  <div class="notif-meta">
                    <span class="text-muted small"><?= date('M j, Y g:i A', strtotime($n['created_at'])) ?></span>
                    <?php if (!empty($n['url'])): ?>
                      <a class="small ms-2" href="<?= e($n['url']) ?>">Open</a>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="notif-actions">
                  <?php if (!(int)$n['is_read']): ?>
                    <form method="post" action="<?= e(base_url('index.php?r=notifications/mark_read')) ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                      <button class="btn btn-sm btn-outline-secondary">Mark read</button>
                    </form>
                  <?php else: ?>
                    <span class="badge bg-light text-dark">Read</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>