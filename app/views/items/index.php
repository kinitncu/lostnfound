<div class="container container-1200 py-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <h2 class="mb-2 mb-sm-0">Items</h2>
    <div>
      <?php if (is_logged_in()): ?>
        <a class="btn btn-brand" href="<?= e(base_url('index.php?r=items/create')) ?>">Report Item</a>
      <?php else: ?>
        <a class="btn btn-outline-brand" href="<?= e(base_url('index.php?r=auth/login')) ?>">Login to Report</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex justify-content-between align-items-center">
      <div><?= e($error) ?></div>
      <a href="<?= e(base_url('index.php?r=items')) ?>" class="btn btn-sm btn-outline-light">Clear filters</a>
    </div>
  <?php endif; ?>

  <form class="lnf-card card p-3 mb-4" method="get" action="<?= e(base_url('index.php')) ?>">
    <input type="hidden" name="r" value="items">
    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label">Type</label>
        <select class="form-select" name="type">
          <option value="" <?= $type ? '' : 'selected' ?>>All</option>
          <option value="lost" <?= $type==='lost'?'selected':'' ?>>Lost</option>
          <option value="found" <?= $type==='found'?'selected':'' ?>>Found</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">State</label>
        <select class="form-select" name="state">
          <option value="" <?= ($state ?? '')===''?'selected':'' ?>>Open + In-claim</option>
          <option value="open" <?= ($state ?? '')==='open'?'selected':'' ?>>Open</option>
          <option value="in_claim" <?= ($state ?? '')==='in_claim'?'selected':'' ?>>In claim</option>
          <option value="returned" <?= ($state ?? '')==='returned'?'selected':'' ?>>Returned</option>
          <option value="all" <?= ($state ?? '')==='all'?'selected':'' ?>>All</option>
        </select>
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" id="includeReturned" onchange="this.form.state.value=this.checked?'all':''" <?= (($state ?? '')==='all')?'checked':'' ?>>
          <label class="form-check-label" for="includeReturned">Include returned</label>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Search</label>
        <input type="text" class="form-control" name="q" value="<?= e($q) ?>" placeholder="Title, description, or location">
      </div>
      <div class="col-md-2">
        <label class="form-label">Campus</label>
        <input type="text" class="form-control" name="campus" value="<?= e($campus ?? '') ?>" placeholder="e.g., Taft">
      </div>
      <div class="col-md-3">
        <label class="form-label">Location</label>
        <input type="text" class="form-control" name="location" value="<?= e($location ?? '') ?>" placeholder="e.g., Velasco Hall">
      </div>

      <div class="col-md-2">
        <label class="form-label">From</label>
        <input type="date" class="form-control" name="date_from" value="<?= e($date_from ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">To</label>
        <input type="date" class="form-control" name="date_to" value="<?= e($date_to ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Sort</label>
        <select class="form-select" name="sort">
          <option value="newest" <?= ($sort ?? 'newest')==='newest'?'selected':'' ?>>Newest</option>
          <option value="oldest" <?= ($sort ?? '')==='oldest'?'selected':'' ?>>Oldest</option>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-outline-brand w-100 mt-3 mt-md-0">Filter</button>
      </div>
    </div>
  </form>

  <?php if (empty($items)): ?>
    <?php if (!empty($isFiltered) && empty($error)): ?>
      <div class="alert alert-info d-flex justify-content-between align-items-center">
        <div>No items match your filters. Try broadening your search or clearing filters.</div>
        <a href="<?= e(base_url('index.php?r=items')) ?>" class="btn btn-sm btn-outline-dark">Clear filters</a>
      </div>
    <?php elseif (empty($error)): ?>
      <div class="alert alert-secondary">No items found.</div>
    <?php endif; ?>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($items as $it): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="lnf-card card h-100">
            <?php if ($it['thumb_url']): ?>
              <img src="<?= e($it['thumb_url']) ?>" class="item-card-thumb" alt="Item photo">
            <?php else: ?>
              <div class="item-card-thumb placeholder d-flex align-items-center justify-content-center">
                <span class="text-muted small">No image</span>
              </div>
            <?php endif; ?>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <span class="badge <?= $it['type']==='lost' ? 'bg-danger' : 'bg-success' ?>"><?= e(ucfirst($it['type'])) ?></span>
                  <?php if (!empty($it['state'])): ?>
                    <?php if ($it['state']==='claim_initiated'): ?>
                      <span class="badge bg-warning text-dark ms-1">In claim</span>
                    <?php elseif ($it['state']==='returned'): ?>
                      <span class="badge bg-secondary ms-1">Returned</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
                <small class="text-muted"><?= date('M j, Y', strtotime($it['created_at'])) ?></small>
              </div>
              <h6 class="card-title mb-2"><?= e($it['title']) ?></h6>
              <p class="text-muted small mb-1"><?= e($it['campus'] ?: '—') ?></p>
              <p class="text-muted small mb-2"><?= e($it['location'] ?: 'No location specified') ?></p>
              <a class="stretched-link" href="<?= e(base_url('index.php?r=items/show&id='.$it['id'])) ?>"></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>