<?php
  $isOwner = is_logged_in() && ((int)$item['user_id'] === (int)current_user_id());
  $isAdmin = is_logged_in() && ((int)(current_user()['is_admin'] ?? 0) === 1);
  $returnUrl = base_url('index.php?r=items'); // after delete, go back to feed
?>
<div class="container container-1200 py-4">
  <div class="row g-4">
    <div class="col-lg-7">
      <?php if (!empty($images)): ?>
        <div class="lnf-card card overflow-hidden mb-3">
          <img src="<?= e(uploads_url('items/'.$item['id'].'/'.$images[0]['filename'])) ?>" class="w-100 item-hero-img" alt="Item photo">
        </div>
        <?php if (count($images) > 1): ?>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach (array_slice($images, 1) as $img): ?>
              <a href="<?= e(uploads_url('items/'.$item['id'].'/'.$img['filename'])) ?>" target="_blank">
                <img src="<?= e(uploads_url('items/'.$item['id'].'/'.$img['filename'])) ?>" class="thumb-img" alt="Item photo">
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="lnf-card card p-5 text-center text-muted">No images provided.</div>
      <?php endif; ?>
    </div>

    <div class="col-lg-5">
      <div class="lnf-card card p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <span class="badge <?= $item['type']==='lost' ? 'bg-danger' : 'bg-success' ?>"><?= e(ucfirst($item['type'])) ?></span>
            <?php if (!empty($item['state'])): ?>
              <?php if ($item['state']==='claim_initiated'): ?><span class="badge bg-warning text-dark ms-1">In claim</span><?php endif; ?>
              <?php if ($item['state']==='returned'): ?><span class="badge bg-secondary ms-1">Returned</span><?php endif; ?>
              <?php if ($item['state']==='closed'): ?><span class="badge bg-dark ms-1">Closed</span><?php endif; ?>
            <?php endif; ?>
          </div>
          <small class="text-muted"><?= date('M j, Y g:i A', strtotime($item['created_at'])) ?></small>
        </div>
        <h3 class="h4 mb-2"><?= e($item['title']) ?></h3>
        <p class="text-muted"><?= nl2br(e($item['description'] ?? '')) ?></p>

        <dl class="row small mb-3">
          <dt class="col-4">Campus</dt><dd class="col-8"><?= e($item['campus'] ?? '—') ?></dd>
          <dt class="col-4">Location</dt><dd class="col-8"><?= e($item['location'] ?? '—') ?></dd>
          <dt class="col-4">Posted by</dt><dd class="col-8"><?= e($user['name'] ?: $user['school_id']) ?></dd>
          <?php if (!empty($user['email'])): ?><dt class="col-4">Email</dt><dd class="col-8"><?= e($user['email']) ?></dd><?php endif; ?>
          <?php if (!empty($user['phone'])): ?><dt class="col-4">Phone</dt><dd class="col-8"><?= e($user['phone']) ?></dd><?php endif; ?>
        </dl>

        <div class="d-flex gap-2">
          <button class="btn btn-outline-brand btn-sm"
                  type="button"
                  data-bs-toggle="modal"
                  data-bs-target="#reportModal"
                  data-report-type="item"
                  data-report-id="<?= (int)$item['id'] ?>"
                  data-item-id="<?= (int)$item['id'] ?>">
            Report this item
          </button>

          <?php if ($isOwner): ?>
            <form method="post" action="<?= e(base_url('index.php?r=items/delete')) ?>" onsubmit="return confirm('Delete your post permanently (including images, comments, claims, reports)?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
              <input type="hidden" name="redirect" value="<?= e($returnUrl) ?>">
              <button class="btn btn-danger btn-sm">Delete my post</button>
            </form>
          <?php endif; ?>

          <?php if ($isAdmin && !$isOwner): ?>
            <form method="post" action="<?= e(base_url('index.php?r=admin/items/delete')) ?>" onsubmit="return confirm('Admin: delete this post permanently (including images, comments, claims, reports)?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
              <input type="hidden" name="redirect" value="<?= e($returnUrl) ?>">
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <hr class="my-4">

  <div id="comments" class="row g-4">
    <div class="col-lg-7">
      <h4 class="mb-3">Comments</h4>
      <?php if (empty($comments)): ?>
        <div class="alert alert-secondary">No comments yet.</div>
      <?php else: ?>
        <div class="comment-list">
          <?php foreach ($comments as $c): ?>
            <div class="comment-block lnf-card card p-3 mb-2">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="comment-author">
                  <strong><?= e($c['user_name'] ?: $c['user_school_id']) ?></strong>
                </div>
                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($c['created_at'])) ?></small>
              </div>
              <div class="comment-body"><?= nl2br(e($c['content'])) ?></div>
              <div class="mt-2 text-end">
                <button class="btn btn-sm btn-outline-danger"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#reportModal"
                        data-report-type="comment"
                        data-report-id="<?= (int)$c['id'] ?>"
                        data-item-id="<?= (int)$item['id'] ?>">
                  Report
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-5">
      <div class="lnf-card card p-3">
        <?php if (is_logged_in()): ?>
          <h5 class="mb-2">Add a comment</h5>
          <form method="post" action="<?= e(base_url('index.php?r=comments/store')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">
            <div class="mb-2">
              <textarea class="form-control" name="content" rows="3" maxlength="1000" required placeholder="Be helpful and respectful."></textarea>
            </div>
            <button class="btn btn-brand">Post comment</button>
          </form>
        <?php else: ?>
          <div class="alert alert-info mb-2">Please log in to post a comment.</div>
          <a class="btn btn-outline-brand" href="<?= e(base_url('index.php?r=auth/login')) ?>">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="reportForm" class="modal-content" method="post" action="<?= e(base_url('index.php?r=reports/store')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="type" id="report_type" value="">
      <input type="hidden" name="target_id" id="report_target_id" value="">
      <input type="hidden" name="item_id" id="report_item_id" value="<?= (int)$item['id'] ?>">
      <div class="modal-header">
        <h5 class="modal-title">Report content</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Reason</label>
        <textarea class="form-control" name="reason" id="report_reason" rows="4" maxlength="500" required placeholder="Describe what’s inappropriate or incorrect."></textarea>
        <div class="form-text">Max 500 characters. Admins will review your report.</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-brand" type="submit">Submit report</button>
      </div>
    </form>
  </div>
</div>