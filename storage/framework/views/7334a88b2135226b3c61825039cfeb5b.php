<?php $__env->startSection('title', 'Dashboard — Admin'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $totalAll = (int) collect($kpi)->sum('count');
  $queueCount = method_exists($queue, 'count') ? (int) $queue->count() : (int) count($queue);
?>

<div class="page-admin-dashboard-v2" x-data="dashboardFilter()" x-init="init()">

  
  <header class="dash-header">
    <div class="dash-header__text">
      <div class="dash-header__kicker">Analitik & Pelaporan</div>
      <h1 class="dash-header__title">Dashboard</h1>
      <p class="dash-header__subtitle">Pantau performa layanan, tren permohonan, dan KPI status secara real-time.</p>
    </div>
    <div class="dash-header__actions">
      <div class="dash-filter" id="dashboardFilter">
        <label class="dash-filter__label" for="periodSelect">
          <svg class="dash-filter__icon" viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd"/></svg>
          Periode
        </label>
        <select id="periodSelect" class="dash-filter__select" x-model="period" x-on:change="applyFilter()">
          <option value="7d">7 Hari Terakhir</option>
          <option value="this_month">Bulan Ini</option>
          <option value="last_month">Bulan Lalu</option>
          <option value="custom">Kustom</option>
        </select>
        <template x-if="period === 'custom'">
          <div class="dash-filter__custom">
            <input type="date" class="dash-filter__date" x-model="customFrom" x-on:change="applyFilter()">
            <span class="dash-filter__sep">—</span>
            <input type="date" class="dash-filter__date" x-model="customTo" x-on:change="applyFilter()">
          </div>
        </template>
      </div>
      <div class="dash-period-label" x-text="periodLabel"><?php echo e($range['label']); ?></div>
    </div>
  </header>

  
  <section class="dash-summary" aria-label="Ringkasan">
    <div class="dash-summary__card dash-summary__card--violet" id="summaryTotal">
      <div class="dash-summary__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <div class="dash-summary__data">
        <div class="dash-summary__value" x-text="summary.total"><?php echo e($summary['total']); ?></div>
        <div class="dash-summary__label">Total Transaksi</div>
      </div>
    </div>
    <div class="dash-summary__card dash-summary__card--emerald" id="summaryCompleted">
      <div class="dash-summary__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="dash-summary__data">
        <div class="dash-summary__value" x-text="summary.completed"><?php echo e($summary['completed']); ?></div>
        <div class="dash-summary__label">Berhasil / Selesai</div>
      </div>
    </div>
    <div class="dash-summary__card dash-summary__card--amber" id="summaryPending">
      <div class="dash-summary__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="dash-summary__data">
        <div class="dash-summary__value" x-text="summary.pending"><?php echo e($summary['pending']); ?></div>
        <div class="dash-summary__label">Menunggu Proses</div>
      </div>
    </div>
    <div class="dash-summary__card dash-summary__card--rose" id="summaryRejected">
      <div class="dash-summary__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="dash-summary__data">
        <div class="dash-summary__value" x-text="summary.rejected"><?php echo e($summary['rejected']); ?></div>
        <div class="dash-summary__label">Ditolak</div>
      </div>
    </div>
  </section>

  
  <section class="dash-charts" aria-label="Grafik"
    data-dashboard-charts
    data-trend='<?php echo json_encode($trendData, 15, 512) ?>'
    data-status='<?php echo json_encode($statusData, 15, 512) ?>'>
    <div class="dash-chart-card dash-chart-card--trend">
      <div class="dash-chart-card__header">
        <h2 class="dash-chart-card__title">
          <svg class="dash-chart-card__title-icon" viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M1 2.75A.75.75 0 011.75 2h16.5a.75.75 0 010 1.5H18v11.378a2.75 2.75 0 01-1.262 2.318l-5.48 3.548a.75.75 0 01-.516 0l-5.48-3.548A2.75 2.75 0 014 14.878V3.5H1.75A.75.75 0 011 2.75z" clip-rule="evenodd"/></svg>
          Tren Permohonan
        </h2>
        <span class="dash-chart-card__badge" x-text="periodLabel"><?php echo e($range['label']); ?></span>
      </div>
      <div class="dash-chart-card__body">
        <canvas id="dashboardTrendChart"></canvas>
      </div>
    </div>
    <div class="dash-chart-card dash-chart-card--status">
      <div class="dash-chart-card__header">
        <h2 class="dash-chart-card__title">
          <svg class="dash-chart-card__title-icon" viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM10 7a3 3 0 100 6 3 3 0 000-6z"/></svg>
          Distribusi Status
        </h2>
      </div>
      <div class="dash-chart-card__body dash-chart-card__body--doughnut">
        <canvas id="dashboardStatusChart"></canvas>
      </div>
    </div>
  </section>

  
  <section class="dash-insights">
    
    <div class="dash-queue-card">
      <div class="dash-queue-card__header">
        <div>
          <h2 class="dash-queue-card__title">
            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 013.5 2h1.148a1.5 1.5 0 011.465 1.175l.716 3.223a1.5 1.5 0 01-1.052 1.767l-.933.267c-.694.198-.81 1.04-.343 1.502a11.007 11.007 0 005.764 5.764c.462.467 1.304.351 1.502-.343l.267-.933a1.5 1.5 0 011.767-1.052l3.223.716A1.5 1.5 0 0118 15.352V16.5a1.5 1.5 0 01-1.5 1.5H15c-1.149 0-2.263-.15-3.326-.43A13.022 13.022 0 012.43 8.326 13.019 13.019 0 012 5V3.5z" clip-rule="evenodd"/></svg>
            Antrian Terbaru
          </h2>
          <p class="dash-queue-card__subtitle"><?php echo e($queueCount); ?> permohonan terbaru yang perlu diproses</p>
        </div>
        <a href="<?php echo e(route('admin.requests.index')); ?>" class="dash-link-btn">
          Lihat semua
          <svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
        </a>
      </div>
      <div class="dash-queue-list">
        <?php $__empty_1 = true; $__currentLoopData = $queue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <a href="<?php echo e(route('admin.requests.show', $r)); ?>" class="dash-queue-item" id="queueItem<?php echo e($r->id); ?>">
            <div class="dash-queue-item__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
            <div class="dash-queue-item__body">
              <div class="dash-queue-item__title"><?php echo e($r->display_title); ?></div>
              <div class="dash-queue-item__meta">
                <span><?php echo e($r->student->name ?? '-'); ?></span>
                <span class="dash-queue-item__dot">&bull;</span>
                <time datetime="<?php echo e($r->created_at->toIso8601String()); ?>"><?php echo e($r->created_at->diffForHumans()); ?></time>
              </div>
            </div>
            <div class="dash-queue-item__end">
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
            </div>
          </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="dash-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="dash-empty__icon"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
            <p>Belum ada antrian permohonan.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    
    <div class="dash-kpi-card">
      <div class="dash-kpi-card__header">
        <h2 class="dash-kpi-card__title">
          <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path d="M15.98 1.804a1 1 0 00-1.96 0l-.24 1.192a1 1 0 01-.784.785l-1.192.238a1 1 0 000 1.962l1.192.238a1 1 0 01.785.785l.238 1.192a1 1 0 001.962 0l.238-1.192a1 1 0 01.785-.785l1.192-.238a1 1 0 000-1.962l-1.192-.238a1 1 0 01-.785-.785l-.238-1.192zM6.949 5.684a1 1 0 00-1.898 0l-.683 2.051a1 1 0 01-.633.633l-2.051.683a1 1 0 000 1.898l2.051.684a1 1 0 01.633.632l.683 2.051a1 1 0 001.898 0l.683-2.051a1 1 0 01.633-.633l2.051-.683a1 1 0 000-1.898l-2.051-.683a1 1 0 01-.633-.633L6.95 5.684zM13.949 13.684a1 1 0 00-1.898 0l-.184.551a1 1 0 01-.632.633l-.551.183a1 1 0 000 1.898l.551.183a1 1 0 01.633.633l.183.551a1 1 0 001.898 0l.184-.551a1 1 0 01.632-.633l.551-.183a1 1 0 000-1.898l-.551-.184a1 1 0 01-.633-.632l-.183-.551z"/></svg>
          KPI Status
        </h2>
        <span class="dash-kpi-card__total"><?php echo e($totalAll); ?> total</span>
      </div>
      <div class="dash-kpi-grid">
        <?php $__currentLoopData = $kpi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <a href="<?php echo e(route('admin.requests.index', ['status' => $item['status']])); ?>"
             class="dash-kpi-item"
             style="--kpi-color: <?php echo e($item['color']); ?>"
             aria-label="Lihat permohonan <?php echo e($item['label']); ?>">
            <div class="dash-kpi-item__top">
              <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $item['status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['status'])]); ?>
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
              <span class="dash-kpi-item__count"><?php echo e($item['count']); ?></span>
            </div>
            <div class="dash-kpi-item__bar-track">
              <div class="dash-kpi-item__bar-fill" style="width: <?php echo e($item['percentage']); ?>%; background: <?php echo e($item['color']); ?>"></div>
            </div>
            <div class="dash-kpi-item__pct"><?php echo e($item['percentage']); ?>%</div>
          </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
  </section>

  
  <section class="dash-table-section" id="transactionTable">
    <div class="dash-table-card">
      <div class="dash-table-card__header">
        <div>
          <h2 class="dash-table-card__title">
            <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M.99 5.24A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25l.01 9.5A2.25 2.25 0 0116.76 17H3.26A2.25 2.25 0 011 14.75l-.01-9.5zm8.26 9.52v-3.5H3.5v3.5h5.75zm1.5 0h5.75v-3.5h-5.75v3.5zm5.75-5H3.5v-3.5h13v3.5z" clip-rule="evenodd"/></svg>
            Rekapitulasi Transaksi
          </h2>
          <p class="dash-table-card__subtitle">Data detail permohonan dengan pencarian, pengurutan, dan ekspor.</p>
        </div>
        <div class="dash-table-actions">
          <form action="<?php echo e(route('admin.dashboard')); ?>" method="GET" class="dash-table-search">
            <input type="hidden" name="period" value="<?php echo e($period); ?>">
            <?php if(request('from')): ?><input type="hidden" name="from" value="<?php echo e(request('from')); ?>"><?php endif; ?>
            <?php if(request('to')): ?><input type="hidden" name="to" value="<?php echo e(request('to')); ?>"><?php endif; ?>
            <svg class="dash-table-search__icon" viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama atau layanan..." class="dash-table-search__input" id="tableSearch">
          </form>
          <div class="dash-export-dropdown" x-data="{ open: false }">
            <button type="button" class="dash-export-btn" x-on:click="open = !open" aria-label="Export data">
              <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z"/><path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z"/></svg>
              Export
              <svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
            </button>
            <div class="dash-export-menu" x-show="open" x-on:click.away="open = false" x-transition>
              <a href="<?php echo e(route('admin.dashboard.export', ['format' => 'csv', 'period' => $period, 'from' => request('from'), 'to' => request('to')])); ?>" class="dash-export-menu__item">
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5z" clip-rule="evenodd"/></svg>
                CSV
              </a>
              <a href="<?php echo e(route('admin.dashboard.export', ['format' => 'excel', 'period' => $period, 'from' => request('from'), 'to' => request('to')])); ?>" class="dash-export-menu__item">
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5z" clip-rule="evenodd"/></svg>
                Excel
              </a>
              <a href="<?php echo e(route('admin.dashboard.export', ['format' => 'pdf', 'period' => $period, 'from' => request('from'), 'to' => request('to')])); ?>" class="dash-export-menu__item">
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5z" clip-rule="evenodd"/></svg>
                PDF
              </a>
            </div>
          </div>
        </div>
      </div>

      
      <div class="dash-table-wrap">
        <table class="dash-table" id="transactionsDesktopTable">
          <thead>
            <tr>
              <th>No</th>
              <th>
                <a href="<?php echo e(route('admin.dashboard', array_merge(request()->query(), ['sort' => 'id', 'dir' => request('sort') === 'id' && request('dir') === 'asc' ? 'desc' : 'asc']))); ?>" class="dash-table__sort">
                  Kode
                  <?php if(request('sort') === 'id'): ?>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="<?php echo e(request('dir') === 'asc' ? 'M14.77 12.79a.75.75 0 01-1.06-.02L10 8.832 6.29 12.77a.75.75 0 01-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z' : 'M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'); ?>" clip-rule="evenodd"/></svg>
                  <?php endif; ?>
                </a>
              </th>
              <th>Nama Mahasiswa</th>
              <th>Layanan</th>
              <th>
                <a href="<?php echo e(route('admin.dashboard', array_merge(request()->query(), ['sort' => 'current_status', 'dir' => request('sort') === 'current_status' && request('dir') === 'asc' ? 'desc' : 'asc']))); ?>" class="dash-table__sort">
                  Status
                  <?php if(request('sort') === 'current_status'): ?>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="<?php echo e(request('dir') === 'asc' ? 'M14.77 12.79a.75.75 0 01-1.06-.02L10 8.832 6.29 12.77a.75.75 0 01-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z' : 'M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'); ?>" clip-rule="evenodd"/></svg>
                  <?php endif; ?>
                </a>
              </th>
              <th>
                <a href="<?php echo e(route('admin.dashboard', array_merge(request()->query(), ['sort' => 'created_at', 'dir' => request('sort', 'created_at') === 'created_at' && request('dir', 'desc') === 'desc' ? 'asc' : 'desc']))); ?>" class="dash-table__sort">
                  Tanggal
                  <?php if(request('sort', 'created_at') === 'created_at'): ?>
                    <svg viewBox="0 0 20 20" fill="currentColor" width="12" height="12"><path fill-rule="evenodd" d="<?php echo e(request('dir', 'desc') === 'asc' ? 'M14.77 12.79a.75.75 0 01-1.06-.02L10 8.832 6.29 12.77a.75.75 0 01-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z' : 'M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z'); ?>" clip-rule="evenodd"/></svg>
                  <?php endif; ?>
                </a>
              </th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($transactions->firstItem() + $i); ?></td>
                <td class="dash-table__code"><?php echo e($t->request_code); ?></td>
                <td><?php echo e($t->student->name ?? '-'); ?></td>
                <td><?php echo e($t->service->title_id ?? '-'); ?></td>
                <td><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $t->current_status->value ?? $t->current_status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($t->current_status->value ?? $t->current_status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></td>
                <td><?php echo e($t->created_at->format('d/m/Y H:i')); ?></td>
                <td>
                  <a href="<?php echo e(route('admin.requests.show', $t)); ?>" class="dash-table__detail-btn">Detail</a>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="7" class="dash-table__empty">Tidak ada data transaksi pada periode ini.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      
      <div class="dash-table-mobile">
        <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="dash-table-mobile__card">
            <div class="dash-table-mobile__row">
              <span class="dash-table-mobile__label">Kode</span>
              <span class="dash-table-mobile__value font-semibold"><?php echo e($t->request_code); ?></span>
            </div>
            <div class="dash-table-mobile__row">
              <span class="dash-table-mobile__label">Nama</span>
              <span class="dash-table-mobile__value"><?php echo e($t->student->name ?? '-'); ?></span>
            </div>
            <div class="dash-table-mobile__row">
              <span class="dash-table-mobile__label">Layanan</span>
              <span class="dash-table-mobile__value"><?php echo e($t->service->title_id ?? '-'); ?></span>
            </div>
            <div class="dash-table-mobile__row">
              <span class="dash-table-mobile__label">Status</span>
              <span class="dash-table-mobile__value"><?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $t->current_status->value ?? $t->current_status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($t->current_status->value ?? $t->current_status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?></span>
            </div>
            <div class="dash-table-mobile__row">
              <span class="dash-table-mobile__label">Tanggal</span>
              <span class="dash-table-mobile__value"><?php echo e($t->created_at->format('d/m/Y H:i')); ?></span>
            </div>
            <a href="<?php echo e(route('admin.requests.show', $t)); ?>" class="dash-table-mobile__action">Lihat Detail →</a>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <div class="dash-empty">
            <p>Tidak ada data transaksi pada periode ini.</p>
          </div>
        <?php endif; ?>
      </div>

      <?php if($transactions->hasPages()): ?>
        <div class="dash-table-pagination">
          <?php echo e($transactions->links('vendor.pagination.tailwind')); ?>

        </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php echo app('Illuminate\Foundation\Vite')('resources/js/dashboard-charts.js'); ?>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Charts are auto-initialized by the Vite module
    if (typeof window.initDashboardCharts === 'function') {
      window.initDashboardCharts();
    } else {
      // Module will self-initialize via DOMContentLoaded
      const el = document.querySelector('[data-dashboard-charts]');
      if (el) {
        const observer = new MutationObserver(() => {
          if (typeof window.initDashboardCharts === 'function') {
            window.initDashboardCharts();
            observer.disconnect();
          }
        });
        observer.observe(document.body, { childList: true, subtree: true });
        setTimeout(() => observer.disconnect(), 5000);
      }
    }
  });
</script>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('dashboardFilter', () => ({
      period: '<?php echo e($period); ?>',
      customFrom: '<?php echo e(request("from", "")); ?>',
      customTo: '<?php echo e(request("to", "")); ?>',
      periodLabel: '<?php echo e($range["label"]); ?>',
      summary: <?php echo json_encode($summary, 15, 512) ?>,

      init() {},

      applyFilter() {
        const params = new URLSearchParams();
        params.set('period', this.period);
        if (this.period === 'custom') {
          if (this.customFrom) params.set('from', this.customFrom);
          if (this.customTo) params.set('to', this.customTo);
        }
        window.location.href = '<?php echo e(route("admin.dashboard")); ?>?' + params.toString();
      }
    }));
  });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>