<?php $__env->startSection('section', 'Panduan Pengguna'); ?>

<?php $__env->startSection('content'); ?>
    <div
        class="page-admin-user-guides page-admin-user-guides-edit"
        data-user-guides-form
        data-translate-url="<?php echo e(route('admin.utils.translate')); ?>"
    >
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <div class="admin-page-kicker">Dokumen publik</div>
                <h1 class="admin-page-title">Edit Panduan Pengguna</h1>
                <p class="admin-page-subtitle">
                    Perbarui metadata, jenis konten, akses role, serta file atau tautan video panduan pengguna.
                </p>
            </div>
            <div class="admin-page-actions">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('admin.user_guides.index')).'','variant' => 'ghost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('admin.user_guides.index')).'','variant' => 'ghost']); ?>Kembali <?php echo $__env->renderComponent(); ?>
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

        <?php echo $__env->make('admin.user_guides._form', [
            'item' => $item,
            'action' => route('admin.user_guides.update', $item),
            'method' => 'PUT',
            'submitLabel' => 'Simpan Perubahan',
            'backHref' => route('admin.user_guides.index'),
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/admin/user_guides/edit.blade.php ENDPATH**/ ?>