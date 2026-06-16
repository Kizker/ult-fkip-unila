<?php $__env->startSection('section','Permohonan'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $filterCount = filled($status) ? 1 : 0;
  $statusLabels = [
    'DIAJUKAN' => 'Diajukan',
    'PERLU_PERBAIKAN' => 'Perlu Perbaikan',
    'DIVERIFIKASI_UNIT' => 'Diverifikasi Unit',
    'MENUNGGU_TTD_UNIT' => 'Menunggu TTD Unit',
    'REVIEW_ULT' => 'Review ULT',
    'MENUNGGU_TTD_FAKULTAS' => 'Menunggu TTD Fakultas',
    'NOMOR_DOKUMEN_TERBIT' => 'Nomor Dokumen Terbit',
    'DIPROSES' => 'Diproses',
    'SELESAI' => 'Selesai',
    'DITOLAK' => 'Ditolak',
    'GATE_VERIFIED' => 'Gate Verified',
    'NOMOR_SURAT_FILLED' => 'Nomor Surat Diisi',
    'IN_SIGNING' => 'Penandatangan',
    'REJECTED_IN_SIGNING' => 'Ditolak TTD',
    'READY_FOR_FINAL' => 'Penandatangan',
    'COMPLETED' => 'Selesai',
    'DITOLAK_ADMIN' => 'Ditolak Admin',
  ];
?>

<div class="page-admin-requests-index" data-admin-requests-index-page>
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Permohonan</div>
      <h1 class="admin-page-title">Daftar Permohonan</h1>
      <p class="admin-page-subtitle">Cari permohonan mahasiswa secara realtime berdasarkan nama, layanan, nama kegiatan, unit, status, atau nomor surat.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total permohonan">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value"><?php echo e($total); ?></div>
        </div>
        <div class="admin-meta-pill" aria-label="Permohonan tampil">
          <div class="admin-meta-pill__label">Tampil</div>
          <div class="admin-meta-pill__value" data-admin-requests-visible-count><?php echo e($items->count()); ?></div>
        </div>
      </div>
    </div>
  </header>

  <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'admin-search-card ar-filter-card','dataAdminSearchCard' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'admin-search-card ar-filter-card','data-admin-search-card' => true]); ?>
    <div class="admin-search" role="search" aria-label="Pencarian permohonan admin">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-requests-live-search" class="sr-only">Cari permohonan</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-requests-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".ar-grid"
              data-realtime-search-item-selector="[data-request-search-item]"
              data-realtime-search-empty-selector="[data-admin-requests-empty]"
            >
            <button
              type="button"
              class="admin-search__clear <?php echo e($filterCount > 0 ? '' : 'admin-search__clear--disabled'); ?>"
              aria-label="Reset pencarian dan filter"
              data-admin-requests-clear-search
              data-reset-url="<?php echo e(route('admin.requests.index')); ?>"
            >
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <details class="admin-search-filter-menu">
          <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter permohonan">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
            </svg>
          </summary>

          <div class="admin-search-filter-menu__panel">
            <form method="GET" class="admin-search-filter-menu__form">
              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-requests-status">Status tracking</label>
                <select id="admin-requests-status" name="status" data-admin-search-track onchange="this.form.requestSubmit()">
                  <option value="">Semua</option>
                  <?php $__currentLoopData = \App\Enums\RequestStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($st->value); ?>" <?php if($status===$st->value): echo 'selected'; endif; ?>><?php echo e($statusLabels[$st->value] ?? $st->value); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            </form>
          </div>
        </details>
      </div>
    </div>
   <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>

  <div class="admin-search-resultbar">
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-admin-requests-live-count data-default-count-text="Menampilkan <?php echo e($items->count()); ?> dari <?php echo e($total); ?> permohonan">Menampilkan <?php echo e($items->count()); ?> dari <?php echo e($total); ?> permohonan</div>
    <?php if($filterCount > 0): ?>
      <div class="admin-search-resultbar__chips">
        <?php if($status): ?>
          <span class="admin-search-result-chip">Status: <?php echo e($statusLabels[$status] ?? $status); ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="ar-grid">
    <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <?php
        $searchText = implode(' ', array_filter([
          $r->activity_title,
          $r->display_title,
          $r->service?->title_id,
          $r->request_code,
          $r->nomor_surat,
          $r->student?->name,
          $r->student?->email,
          $r->student?->student_number,
          $r->currentUnit?->name,
          $r->current_status?->value ?? $r->current_status,
        ]));
        $unitName = trim((string) ($r->currentUnit?->name ?? '-'));
        $unitPrefix = match ($r->currentUnit?->type) {
          \App\Enums\UnitType::jurusan => 'Jurusan',
          \App\Enums\UnitType::prodi => 'Program Studi',
          \App\Enums\UnitType::fakultas => 'Fakultas',
          default => 'Unit',
        };
        $unitLabel = $unitPrefix.': '.$unitName;
      ?>
      <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'ar-card','role' => 'article','dataRequestSearchItem' => true,'dataRequestSearchText' => ''.e($searchText).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'ar-card','role' => 'article','data-request-search-item' => true,'data-request-search-text' => ''.e($searchText).'']); ?>
        <div class="ar-card__row">
          <div class="ar-card__meta">
            <div class="ar-card__title">
              <a class="ar-card__title-link" href="<?php echo e(route('admin.requests.show',$r)); ?>">
                <?php echo e($r->display_title); ?>

                <span class="ar-card__id"><?php echo e($r->request_code); ?></span>
              </a>
            </div>
            <div class="ar-card__sub">
              <span class="ar-card__student"><?php echo e($r->student->name); ?></span>
              <span class="ar-sep" aria-hidden="true">&bull;</span>
              <span class="ar-card__unit"><?php echo e($unitLabel); ?></span>
              <span class="ar-sep" aria-hidden="true">&bull;</span>
              <span class="ar-card__date">Dibuat: <?php echo e(optional($r->created_at)->format('d M Y H:i')); ?></span>
            </div>
          </div>
          <div class="ar-card__actions">
            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $r->current_status->value ?? $r->current_status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($r->current_status->value ?? $r->current_status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','href' => ''.e(route('admin.requests.show',$r)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','href' => ''.e(route('admin.requests.show',$r)).'']); ?>Detail <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
          </div>
        </div>
       <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
        <div class="admin-empty">Tidak ada permohonan untuk ditampilkan saat ini.</div>
       <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
    <?php endif; ?>

    <?php if($items->count() > 0): ?>
      <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'hidden','dataAdminRequestsEmpty' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'hidden','data-admin-requests-empty' => true]); ?>
        <div class="admin-empty">Tidak ada permohonan yang cocok dengan pencarian.</div>
       <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $attributes = $__attributesOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__attributesOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal53747ceb358d30c0105769f8471417f6)): ?>
<?php $component = $__componentOriginal53747ceb358d30c0105769f8471417f6; ?>
<?php unset($__componentOriginal53747ceb358d30c0105769f8471417f6); ?>
<?php endif; ?>
    <?php endif; ?>
  </div>

  <div class="admin-pagination"><?php echo e($items->onEachSide(1)->links('components.public.pagination')); ?></div>
</div>
<script>
  (() => {
    const root = document.querySelector('.page-admin-requests-index[data-admin-requests-index-page]');
    if (!root) return;

    const searchInput = root.querySelector('#admin-requests-live-search');
    const statusSelect = root.querySelector('#admin-requests-status');
    const clearButton = root.querySelector('[data-admin-requests-clear-search]');
    const countEl = root.querySelector('[data-admin-requests-live-count]');
    const visibleCountEl = root.querySelector('[data-admin-requests-visible-count]');
    const grid = root.querySelector('.ar-grid');
    const emptyEl = grid ? grid.querySelector('[data-admin-requests-empty]') : null;
    if (!searchInput || !clearButton || !grid) return;

    const defaultCountText = String(countEl?.getAttribute('data-default-count-text') || countEl?.textContent || '').trim();
    const normalize = (value) => String(value || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim();

    const setShown = (el, shown) => {
      el.classList.toggle('hidden', !shown);
      el.hidden = !shown;
      el.style.display = shown ? '' : 'none';
    };

    const items = () => Array.from(grid.querySelectorAll('[data-request-search-item]'));
    const hasKeyword = () => !!String(searchInput.value || '').trim();
    const hasFilters = () => !!String(statusSelect?.value || '').trim();
    const hasActiveQueryFilters = () => {
      const params = new URLSearchParams(window.location.search || '');
      return !!String(params.get('status') || '').trim();
    };

    const syncClearState = () => {
      clearButton.classList.toggle('admin-search__clear--disabled', !hasKeyword() && !hasFilters() && !hasActiveQueryFilters());
    };

    const applySearch = () => {
      const q = normalize(searchInput.value);
      let shown = 0;

      items().forEach((item) => {
        const haystack = normalize(item.getAttribute('data-request-search-text') || item.textContent || '');
        const match = q === '' || haystack.includes(q);
        setShown(item, match);
        if (match) shown += 1;
      });

      if (emptyEl) setShown(emptyEl, q !== '' && shown === 0);
      if (countEl) countEl.textContent = q ? `Menampilkan ${shown} dari <?php echo e($total); ?> permohonan` : defaultCountText;
      if (visibleCountEl) visibleCountEl.textContent = q ? String(shown) : '<?php echo e($items->count()); ?>';
    };

    searchInput.addEventListener('input', () => {
      applySearch();
      syncClearState();
    });

    clearButton.addEventListener('click', (event) => {
      event.preventDefault();

      if (hasKeyword()) {
        searchInput.value = '';
        applySearch();
        syncClearState();
        searchInput.focus();
        return;
      }

      if (hasFilters() || hasActiveQueryFilters()) {
        const resetUrl = clearButton.getAttribute('data-reset-url') || '';
        if (resetUrl) window.location.href = resetUrl;
      }
    });

    statusSelect?.addEventListener('change', syncClearState);

    applySearch();
    syncClearState();
  })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/admin/requests/index.blade.php ENDPATH**/ ?>