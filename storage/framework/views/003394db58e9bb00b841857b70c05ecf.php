<?php $__env->startSection('section','Peran'); ?>
<?php $__env->startSection('content'); ?>
<?php
  $totalRoles = method_exists($roles, 'total') ? (int) $roles->total() : $roles->count();
  $totalPerms = $permissions->count();
  $permissionLabel = static fn (?string $name): string => \App\Support\PermissionLabel::make($name);
  $shownRoles = (int) $roles->count();
?>

<div class="page-admin-roles-index">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akses</div>
      <h1 class="admin-page-title">Peran</h1>
      <p class="admin-page-subtitle">Siapkan role beserta permission-nya. Saat menambah user, cukup pilih role yang sudah ada.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total roles">
          <div class="admin-meta-pill__label">Roles</div>
          <div class="admin-meta-pill__value"><?php echo e($totalRoles); ?></div>
        </div>
        <div class="admin-meta-pill" aria-label="Total permissions">
          <div class="admin-meta-pill__label">Permissions</div>
          <div class="admin-meta-pill__value"><?php echo e($totalPerms); ?></div>
        </div>
      </div>

      <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','href' => ''.e(route('admin.roles.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','href' => ''.e(route('admin.roles.create')).'']); ?>Tambah Role <?php echo $__env->renderComponent(); ?>
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
  </header>

  <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'admin-search-card roles-toolbar','dataAdminSearchCard' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'admin-search-card roles-toolbar','data-admin-search-card' => true]); ?>
    <div class="admin-search" role="search" aria-label="Pencarian role">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-roles-live-search" class="sr-only">Cari role</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-roles-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".roles-grid"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear admin-search__clear--disabled" aria-label="Hapus pencarian" data-admin-search-clear data-reset-url="<?php echo e(route('admin.roles.index')); ?>">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>
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
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan <?php echo e($shownRoles); ?> dari <?php echo e($totalRoles); ?> role">Menampilkan <?php echo e($shownRoles); ?> dari <?php echo e($totalRoles); ?> role</div>
  </div>

  <div class="grid gap-4 mt-6 roles-grid">
    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $permCount = $r->permissions->count();
        $assigned = (int) (($assignedCounts[$r->id] ?? 0));
        $cannotDelete = $r->name === 'Superadmin' || $assigned > 0;
        $previewPerms = $r->permissions->sortBy('name')->take(10);
        $permissionNames = $r->permissions->pluck('name');
        $permissionLabels = $permissionNames->map(static fn (string $name): string => $permissionLabel($name))->implode(' ');
        $searchText = trim($r->name . ' ' . $permissionNames->implode(' ') . ' ' . $permissionLabels . ' ' . $assigned);
      ?>
      <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'roles-item','dataRealtimeSearchItem' => true,'dataRealtimeSearchText' => ''.e($searchText).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'roles-item','data-realtime-search-item' => true,'data-realtime-search-text' => ''.e($searchText).'']); ?>
        <div class="roles-item__row flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="roles-item__main min-w-0">
            <div class="flex items-start gap-3">
              <div class="h-10 w-10 rounded-2xl bg-[rgb(var(--c-primary)/.10)] border border-[rgb(var(--c-primary)/.18)] flex items-center justify-center flex-shrink-0">
                <span class="text-[rgb(var(--c-primary))] font-semibold text-sm">
                  <?php echo e(mb_substr($r->name, 0, 1)); ?>

                </span>
              </div>

              <div class="min-w-0">
                <div class="text-sm font-semibold"><?php echo e($r->name); ?></div>
                <div class="text-xs text-muted mt-1 flex flex-wrap gap-x-2 gap-y-1">
                  <span><?php echo e($permCount); ?> permission</span>
                  <span class="text-zinc-300 dark:text-zinc-700" aria-hidden="true">&bull;</span>
                  <span><?php echo e($assigned); ?> user</span>
                </div>
              </div>
            </div>

            <details class="mt-3 role-perms">
              <summary class="cursor-pointer select-none list-none flex items-center justify-between gap-3">
                <div class="text-xs font-semibold text-muted">Permissions</div>
                <div class="text-xs text-[rgb(var(--c-primary))] font-semibold">
                  <span class="role-perms__toggle role-perms__toggle--see">
                    <?php echo e($permCount > 10 ? 'Lihat semua' : 'Lihat'); ?>

                  </span>
                  <span class="role-perms__toggle role-perms__toggle--close">Tutup</span>
                </div>
              </summary>

              <div class="mt-3">
                <div class="flex flex-wrap gap-2">
                  <?php $__empty_1 = true; $__currentLoopData = $previewPerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['variant' => 'primary','title' => ''.e($p->name).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'primary','title' => ''.e($p->name).'']); ?><?php echo e($permissionLabel($p->name)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <span class="text-xs text-muted">Belum ada permission.</span>
                  <?php endif; ?>
                  <?php if($permCount > 10): ?>
                    <span class="text-xs text-muted">+<?php echo e($permCount - 10); ?> lagi</span>
                  <?php endif; ?>
                </div>

                <?php if($permCount > 10): ?>
                  <div class="roles-perms__all mt-3 max-h-56 overflow-auto rounded-xl border border-[rgb(var(--c-border))] bg-white/60 dark:bg-zinc-900/40 p-3">
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                      <?php $__currentLoopData = $r->permissions->sortBy('name'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="text-xs text-zinc-700 dark:text-zinc-200 truncate" title="<?php echo e($p->name); ?>">
                          <?php echo e($permissionLabel($p->name)); ?>

                        </div>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </details>
          </div>

          <div class="roles-item__actions flex items-center gap-2 justify-end flex-wrap">
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','href' => ''.e(route('admin.roles.edit', $r)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','href' => ''.e(route('admin.roles.edit', $r)).'']); ?>Edit <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            <form method="POST" action="<?php echo e(route('admin.roles.destroy', $r)); ?>" onsubmit="return confirm('Hapus role ini?')">
              <?php echo csrf_field(); ?>
              <?php echo method_field('DELETE'); ?>
              <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'danger','type' => 'submit','disabled' => $cannotDelete]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','type' => 'submit','disabled' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cannotDelete)]); ?>Hapus <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </form>

            <?php if($cannotDelete): ?>
              <div class="roles-item__note w-full text-xs text-muted text-right">
                <?php if($r->name === 'Superadmin'): ?>
                  Tidak bisa menghapus role Superadmin.
                <?php else: ?>
                  Tidak bisa dihapus karena dipakai <?php echo e($assigned); ?> user.
                <?php endif; ?>
              </div>
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
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($roles->count() > 0): ?>
      <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'hidden','dataRealtimeSearchEmpty' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'hidden','data-realtime-search-empty' => true]); ?>
        <div class="admin-empty">Tidak ada role yang cocok dengan pencarian.</div>
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

  <?php if(method_exists($roles, 'hasPages') && $roles->hasPages()): ?>
    <div class="admin-pagination mt-6">
      <?php echo e($roles->onEachSide(1)->links('components.public.pagination')); ?>

    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/admin/roles/index.blade.php ENDPATH**/ ?>