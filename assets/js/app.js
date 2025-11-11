$(function() {
  // Highlight nav item by query string
  const qs = window.location.search;
  $('.navbar .nav-link').each(function(){
    const href = $(this).attr('href') || '';
    if (qs && href.includes(qs)) $(this).addClass('active');
  });

  // Header auto-hide + elevate
  (function headerAutoHide() {
    const header = document.getElementById('app-header');
    const nav = document.getElementById('topNav');
    if (!header) return;

    let lastY = Math.max(0, window.scrollY || 0);
    let ticking = false;
    const DELTA = 8;         // min delta to toggle hide/show
    const SHOW_AT_TOP = 48;  // keep visible near top
    const HIDE_AFTER = 80;   // allow hide only after this scroll
    const ELEVATE_AFTER = 24; // add shadow after slight scroll

    const isModalOpen = () => document.querySelector('.modal.show') !== null;
    const isNavOpen = () => nav && nav.classList.contains('show');

    function onScroll() {
      const y = Math.max(0, window.scrollY || 0);
      const dy = y - lastY;

      // Elevate header subtly when scrolled a bit
      if (y > ELEVATE_AFTER) {
        header.classList.add('header-elevated');
      } else {
        header.classList.remove('header-elevated');
      }

      // Keep visible if mobile nav or modal is open
      if (isNavOpen() || isModalOpen()) {
        header.classList.remove('header-hidden');
        lastY = y; ticking = false; return;
      }

      // Always show near top
      if (y <= SHOW_AT_TOP) {
        header.classList.remove('header-hidden');
        lastY = y; ticking = false; return;
      }

      // Hide on scroll down past threshold; show on scroll up
      if (dy > DELTA && y > HIDE_AFTER) {
        header.classList.add('header-hidden');
      } else if (dy < -DELTA) {
        header.classList.remove('header-hidden');
      }

      lastY = y;
      ticking = false;
    }

    // Initial state
    onScroll();

    // Scroll listener
    window.addEventListener('scroll', function() {
      if (!ticking) {
        window.requestAnimationFrame(onScroll);
        ticking = true;
      }
    }, { passive: true });

    // Keep header visible when nav opens
    if (nav) {
      nav.addEventListener('show.bs.collapse', () => header.classList.remove('header-hidden'));
      nav.addEventListener('shown.bs.collapse', () => header.classList.remove('header-hidden'));
    }

    // Re-evaluate on resize
    window.addEventListener('resize', () => {
      header.classList.remove('header-hidden');
      lastY = Math.max(0, window.scrollY || 0);
      onScroll();
    });
  })();

  // -------- Client-side image previews + compression (Report Item) --------
  const $input   = $('#photosInput');
  const $grid    = $('#photoPreviews');
  const $errors  = $('#photoErrors');
  const $primary = $('#primary_index');

  if ($input.length) {
    let dt = new DataTransfer();
    const MAX_FILES = 5;
    const MAX_SIZE = 2 * 1024 * 1024;
    const ALLOWED = ['image/jpeg', 'image/png'];

    function setMsg(msg, isError = false) {
      if (!msg) { $errors.text(''); return; }
      $errors.toggleClass('text-danger', !!isError);
      $errors.toggleClass('text-muted', !isError);
      $errors.html(msg);
    }

    function render() {
      $grid.empty();
      const files = Array.from(dt.files);
      let pIdx = parseInt($primary.val() || '0', 10);

      if (files.length === 0) { pIdx = 0; $primary.val('0'); }
      else if (files.length === 1) { pIdx = 0; $primary.val('0'); }
      else if (pIdx >= files.length) { pIdx = 0; $primary.val('0'); }

      $grid.toggleClass('single', files.length <= 1);

      files.forEach((file, idx) => {
        const url = URL.createObjectURL(file);
        const $tile = $(`
          <div class="preview-tile" data-idx="${idx}">
            <img class="preview-img" alt="preview">
            <button type="button" class="preview-remove" title="Remove">&times;</button>
            <button type="button" class="preview-primary-toggle" title="Make primary">★</button>
            <span class="preview-primary-badge">Primary</span>
          </div>
        `);
        $tile.find('img').attr('src', url);
        if (idx === pIdx) $tile.addClass('is-primary');
        $grid.append($tile);
        $tile.find('img').on('load', () => URL.revokeObjectURL(url));
      });
    }

    function setPrimary(idx) {
      const total = dt.files.length;
      if (total <= 1) { $primary.val('0'); render(); return; }
      if (idx < 0 || idx >= total) return;
      $primary.val(String(idx));
      render();
    }

    function rebuildDT(files) {
      const newDt = new DataTransfer();
      files.forEach(f => newDt.items.add(f));
      dt = newDt;
      $input[0].files = dt.files;
    }

    function removeAt(idx) {
      const files = Array.from(dt.files);
      files.splice(idx, 1);
      rebuildDT(files);

      let p = parseInt($primary.val() || '0', 10);
      if (files.length <= 1) p = 0;
      else {
        if (p === idx) p = 0;
        else if (idx < p) p = Math.max(0, p - 1);
      }
      $primary.val(String(p));
      render();
    }

    function fileToImage(file) {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error('Image load error'));
        img.src = URL.createObjectURL(file);
      });
    }
    async function canvasCompress(sourceFile, maxSide, quality) {
      let width, height, drawFn;
      try {
        const bmp = await createImageBitmap(sourceFile, { imageOrientation: 'from-image' });
        width = bmp.width; height = bmp.height;
        drawFn = (ctx, w, h) => ctx.drawImage(bmp, 0, 0, w, h);
      } catch {
        const img = await fileToImage(sourceFile);
        width = img.naturalWidth; height = img.naturalHeight;
        drawFn = (ctx, w, h) => ctx.drawImage(img, 0, 0, w, h);
      }
      const scale = Math.min(maxSide / width, maxSide / height, 1);
      const targetW = Math.round(width * scale);
      const targetH = Math.round(height * scale);
      const canvas = document.createElement('canvas');
      canvas.width = targetW; canvas.height = targetH;
      const ctx = canvas.getContext('2d');
      drawFn(ctx, targetW, targetH);
      const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
      if (!blob) return sourceFile;
      const newName = (sourceFile.name || 'photo').replace(/\.[^/.]+$/, '') + '.jpg';
      return new File([blob], newName, { type: 'image/jpeg', lastModified: Date.now() });
    }
    async function compressToTarget(file) {
      if (file.size <= MAX_SIZE && (file.type === 'image/jpeg' || file.type === 'image/png')) return file;
      const steps = [
        { max: 1600, q: 0.85 },
        { max: 1280, q: 0.80 },
        { max: 1024, q: 0.75 },
        { max:  800, q: 0.70 }
      ];
      let out = file;
      for (const s of steps) {
        out = await canvasCompress(out, s.max, s.q);
        if (out.size <= MAX_SIZE) break;
      }
      return out;
    }

    async function addFromInput(e) {
      setMsg('Optimizing images…', false);
      const incoming = Array.from(e.target.files || []);
      let files = Array.from(dt.files);
      const msgs = [];
      const room = Math.max(0, MAX_FILES - files.length);
      const toProcess = incoming.filter(f => {
        if (!ALLOWED.includes(f.type)) { msgs.push(`${f.name}: unsupported type`); return false; }
        return true;
      }).slice(0, room);

      for (const f of toProcess) {
        try {
          const optimized = await compressToTarget(f);
          if (optimized.size > MAX_SIZE) { msgs.push(`${f.name}: could not reduce below 2MB, skipping.`); continue; }
          files.push(optimized);
        } catch {
          msgs.push(`${f.name}: compression failed, skipping.`);
        }
      }

      rebuildDT(files);
      if (dt.files.length === 1) $primary.val('0');
      if (incoming.length > room) msgs.push(`Only ${MAX_FILES} images allowed. Extra files were ignored.`);
      setMsg(msgs.join('<br>') || '', msgs.length > 0);
      render();
      $input.val('');
    }

    // Delegated clicks
    $grid.on('click', '.preview-primary-toggle', function(e) {
      e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation();
      const idx = parseInt($(this).closest('.preview-tile').attr('data-idx') || '-1', 10);
      if (Number.isInteger(idx) && idx >= 0) setPrimary(idx);
    });
    $grid.on('click', '.preview-remove', function(e) {
      e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation();
      const idx = parseInt($(this).closest('.preview-tile').attr('data-idx') || '-1', 10);
      if (Number.isInteger(idx) && idx >= 0) removeAt(idx);
    });

    $input.on('change', (e) => { addFromInput(e); });
  }

  // Report modal wiring
  let reportContext = null;
  $(document).on('click', '[data-report-type]', function() {
    reportContext = { type: this.getAttribute('data-report-type') || '', id: this.getAttribute('data-report-id') || '', itemId: this.getAttribute('data-item-id') || '' };
  });
  const reportModalEl = document.getElementById('reportModal');
  if (reportModalEl) {
    reportModalEl.addEventListener('show.bs.modal', function (event) {
      const trigger = event.relatedTarget;
      const type = (trigger && trigger.getAttribute('data-report-type')) || (reportContext && reportContext.type) || '';
      const id = (trigger && trigger.getAttribute('data-report-id')) || (reportContext && reportContext.id) || '';
      const itemId = (trigger && trigger.getAttribute('data-item-id')) || (reportContext && reportContext.itemId) || '';
      $('#report_type').val(type);
      $('#report_target_id').val(id);
      $('#report_item_id').val(itemId);
      $('#report_reason').val('');
    });
  }

  // Claim modal wiring
  const claimModalEl = document.getElementById('claimModal');
  if (claimModalEl) {
    claimModalEl.addEventListener('show.bs.modal', function (event) {
      const trigger = event.relatedTarget;
      const itemId = trigger ? trigger.getAttribute('data-item-id') : '';
      if (itemId) $('#claim_item_id').val(itemId);
    });
  }

  // Notifications polling
  const $badge = $('#notifCount');
  if ($badge.length) {
    const poll = () => {
      $.ajax({ url: BASE_URL + 'index.php?r=notifications/poll', method: 'GET', dataType: 'json', cache: false })
        .done((res) => {
          const n = (res && typeof res.unread !== 'undefined') ? parseInt(res.unread, 10) : 0;
          if (n > 0) $badge.text(n).removeClass('d-none'); else $badge.text('0').addClass('d-none');
        })
        .fail(() => {});
    };
    poll();
    setInterval(poll, 20000);
  }
});