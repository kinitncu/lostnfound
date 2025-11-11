<div class="container container-1200 py-4">
  <h2 class="mb-3">Report an Item</h2>
  <p class="text-muted">Submit a Lost or Found item. Submissions are reviewed by an administrator before they appear publicly.</p>

  <?php if (!empty($error)): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="<?= e(base_url('index.php?r=items/store')) ?>" enctype="multipart/form-data" class="lnf-card card p-3 p-md-4">
    <?= csrf_field() ?>

    <div class="row g-3">
      <div class="col-sm-4">
        <label class="form-label">Type</label>
        <select class="form-select" name="type" required>
          <option value="">Choose...</option>
          <option value="lost" <?= ($old['type'] ?? '')==='lost' ? 'selected':'' ?>>Lost</option>
          <option value="found" <?= ($old['type'] ?? '')==='found' ? 'selected':'' ?>>Found</option>
        </select>
      </div>

      <div class="col-sm-8">
        <label class="form-label">Title</label>
        <input type="text" class="form-control" name="title" required maxlength="160" value="<?= e($old['title'] ?? '') ?>" placeholder="e.g., Black wallet near library">
      </div>

      <div class="col-md-6">
        <label class="form-label">Campus</label>
        <input type="text" class="form-control" name="campus" value="<?= e($old['campus'] ?? '') ?>" placeholder="e.g., Taft, Laguna, BGC">
      </div>

      <div class="col-md-6">
        <label class="form-label">Location</label>
        <input type="text" class="form-control" name="location" value="<?= e($old['location'] ?? '') ?>" placeholder="e.g., Velasco Hall, 2/F corridor">
      </div>

      <div class="col-12">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description" rows="4" placeholder="Add helpful details (brand, color, unique marks)"><?= e($old['description'] ?? '') ?></textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Photos (JPG/PNG, max 2MB each, up to 5)</label>
        <input id="photosInput" type="file" class="form-control" name="photos[]" accept="image/jpeg,image/png" multiple>
        <div id="photoErrors" class="text-muted small mt-1"></div>
        <input type="hidden" name="primary_index" id="primary_index" value="0">
        <div id="photoPreviews" class="preview-grid mt-3"></div>
        <div class="form-text">
          Large images are optimized automatically before upload. If you add one photo, it becomes the primary thumbnail.
          If you add multiple, click the star to choose the primary.
        </div>
      </div>
    </div>

    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-brand" type="submit">Submit</button>
      <a href="<?= e(base_url('index.php?r=items')) ?>" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>