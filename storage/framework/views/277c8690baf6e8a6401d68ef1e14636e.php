<?php $__env->startSection('section','Layanan'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-admin-services-edit" data-services-form data-translate-url="<?php echo e(route('admin.utils.translate')); ?>">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master layanan</div>
      <h1 class="admin-page-title">Edit: <?php echo e($service->title_id); ?></h1>
      <p class="admin-page-subtitle">Perbarui konten layanan. Setup dokumen (template/placeholder/signers) bersifat wajib untuk layanan dokumen.</p>
    </div>
    <div class="admin-page-actions">
      <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','href' => ''.e(route('services.show',$service)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','href' => ''.e(route('services.show',$service)).'']); ?>Preview publik <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
      <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','href' => '#setup-dokumen']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','href' => '#setup-dokumen']); ?>Setup Dokumen <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
      <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','href' => ''.e(route('admin.layanan.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','href' => ''.e(route('admin.layanan.index')).'']); ?>Kembali <?php echo $__env->renderComponent(); ?>
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

  <div class="as-form-layout">
    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'as-form-card xl:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'as-form-card xl:col-span-2']); ?>
      <form class="as-form" method="POST" action="<?php echo e(route('admin.layanan.update',$service)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <?php
          $docErrorsFlat = \Illuminate\Support\Arr::flatten($readinessErrors ?? []);
          $docReady = empty($docErrorsFlat);
          $selectedDocumentSourceType = (string) old('document_source_type', $service->document_source_type?->value ?? \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value);
          $isCertificateMode = $selectedDocumentSourceType === \App\Enums\DocumentSourceType::REQUEST_PPTX->value;
          $docFlowDisabled = $isCertificateMode;
        ?>
        <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="min-w-0">
              <div class="text-sm font-semibold">Status dokumen</div>
              <?php if($docReady): ?>
                <div class="text-sm text-muted mt-1">Readiness: siap publish.</div>
              <?php else: ?>
                <div class="text-sm text-muted mt-1">
                  Readiness: belum siap (<?php echo e(count($docErrorsFlat)); ?> item).
                  <?php echo e($isCertificateMode ? 'Cek konfigurasi gate/workflow mode sertifikat.' : 'Upload template dan lengkapi mapping.'); ?>

                </div>
              <?php endif; ?>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','href' => '#setup-dokumen']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','href' => '#setup-dokumen']); ?>Buka setup dokumen <?php echo $__env->renderComponent(); ?>
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
        </div>

        <div class="as-form-grid">
          <?php if (isset($component)) { $__componentOriginaled2cde6083938c436304f332ba96bb7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaled2cde6083938c436304f332ba96bb7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.select','data' => ['name' => 'category_id','label' => 'Kategori Layanan','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'category_id','label' => 'Kategori Layanan','required' => true]); ?>
            <option value="">Pilih kategori</option>
            <?php $__currentLoopData = ($serviceCategories ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($cat->id); ?>" <?php if((string) old('category_id', $service->category_id) === (string) $cat->id): echo 'selected'; endif; ?>>
                <?php echo e($cat->name_id); ?>

              </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
           <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $attributes = $__attributesOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__attributesOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $component = $__componentOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__componentOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>

          <?php if (isset($component)) { $__componentOriginaled2cde6083938c436304f332ba96bb7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaled2cde6083938c436304f332ba96bb7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.select','data' => ['name' => 'document_source_type','label' => 'Sumber Dokumen Awal','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document_source_type','label' => 'Sumber Dokumen Awal','required' => true]); ?>
            <option value="<?php echo e(\App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value); ?>" <?php if(old('document_source_type', $service->document_source_type?->value ?? \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value) === \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value): echo 'selected'; endif; ?>>
              DOCX admin
            </option>
            <option value="<?php echo e(\App\Enums\DocumentSourceType::REQUEST_PPTX->value); ?>" <?php if(old('document_source_type', $service->document_source_type?->value) === \App\Enums\DocumentSourceType::REQUEST_PPTX->value): echo 'selected'; endif; ?>>
              PPTX pemohon
            </option>
           <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $attributes = $__attributesOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__attributesOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaled2cde6083938c436304f332ba96bb7c)): ?>
<?php $component = $__componentOriginaled2cde6083938c436304f332ba96bb7c; ?>
<?php unset($__componentOriginaled2cde6083938c436304f332ba96bb7c); ?>
<?php endif; ?>
        </div>

        <div class="as-form-grid">
          <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['name' => 'title_id','label' => 'Judul (ID)','value' => ''.e(old('title_id',$service->title_id)).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'title_id','label' => 'Judul (ID)','value' => ''.e(old('title_id',$service->title_id)).'','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $attributes = $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $component = $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
          <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['name' => 'title_en','label' => 'Title (EN)','value' => ''.e(old('title_en',$service->title_en)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'title_en','label' => 'Title (EN)','value' => ''.e(old('title_en',$service->title_en)).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $attributes = $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1)): ?>
<?php $component = $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1; ?>
<?php unset($__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1); ?>
<?php endif; ?>
        </div>

        <div class="as-form-grid">
          <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['name' => 'summary_id','label' => 'Ringkasan (ID)','rows' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'summary_id','label' => 'Ringkasan (ID)','rows' => '2']); ?><?php echo e(old('summary_id',$service->summary_id)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
          <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['name' => 'summary_en','label' => 'Summary (EN)','rows' => '2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'summary_en','label' => 'Summary (EN)','rows' => '2']); ?><?php echo e(old('summary_en',$service->summary_en)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
        </div>

        <div class="as-activation">
          <div class="as-activation__meta">
            <div class="as-activation__title">Status layanan</div>
            <div class="as-activation__desc">Aktifkan jika layanan sudah siap ditampilkan untuk pemohon.</div>
          </div>

          <label class="as-activation__toggle">
            <input type="checkbox" name="is_active" value="1" <?php if(old('is_active',$service->is_active)): echo 'checked'; endif; ?> class="as-activation__input">
            <span class="as-activation__track" aria-hidden="true">
              <span class="as-activation__thumb" aria-hidden="true"></span>
            </span>
            <span class="as-activation__state">
              <span class="as-activation__on">Active</span>
              <span class="as-activation__off">Inactive</span>
            </span>
          </label>
        </div>

        <label class="as-check">
          <input type="checkbox" name="allow_general_attachments" value="1" <?php if(old('allow_general_attachments', $service->allow_general_attachments)): echo 'checked'; endif; ?> class="as-check__box">
          <span class="as-check__label">Tampilkan lampiran umum pada form pengajuan <span class="text-xs text-muted ml-2">(opsional, multi-file)</span></span>
        </label>

        <?php if (isset($component)) { $__componentOriginala75492aff34d1af0bd0908127080afc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala75492aff34d1af0bd0908127080afc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tiptap-editor','data' => ['name' => 'requirements_html_id','label' => 'Persyaratan','localeHint' => 'ID','value' => old('requirements_html_id',$service->requirements_html_id),'placeholder' => 'Tulis persyaratan (opsional). Gunakan list untuk poin.','help' => 'Opsional. Gunakan list (bullet/numbered) untuk poin.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tiptap-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'requirements_html_id','label' => 'Persyaratan','localeHint' => 'ID','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('requirements_html_id',$service->requirements_html_id)),'placeholder' => 'Tulis persyaratan (opsional). Gunakan list untuk poin.','help' => 'Opsional. Gunakan list (bullet/numbered) untuk poin.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala75492aff34d1af0bd0908127080afc5)): ?>
<?php $attributes = $__attributesOriginala75492aff34d1af0bd0908127080afc5; ?>
<?php unset($__attributesOriginala75492aff34d1af0bd0908127080afc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala75492aff34d1af0bd0908127080afc5)): ?>
<?php $component = $__componentOriginala75492aff34d1af0bd0908127080afc5; ?>
<?php unset($__componentOriginala75492aff34d1af0bd0908127080afc5); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginala75492aff34d1af0bd0908127080afc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala75492aff34d1af0bd0908127080afc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tiptap-editor','data' => ['name' => 'sop_html_id','label' => 'SOP','localeHint' => 'ID','value' => old('sop_html_id',$service->sop_html_id),'placeholder' => 'Tulis SOP (opsional).','help' => 'Opsional.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tiptap-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'sop_html_id','label' => 'SOP','localeHint' => 'ID','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(old('sop_html_id',$service->sop_html_id)),'placeholder' => 'Tulis SOP (opsional).','help' => 'Opsional.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala75492aff34d1af0bd0908127080afc5)): ?>
<?php $attributes = $__attributesOriginala75492aff34d1af0bd0908127080afc5; ?>
<?php unset($__attributesOriginala75492aff34d1af0bd0908127080afc5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala75492aff34d1af0bd0908127080afc5)): ?>
<?php $component = $__componentOriginala75492aff34d1af0bd0908127080afc5; ?>
<?php unset($__componentOriginala75492aff34d1af0bd0908127080afc5); ?>
<?php endif; ?>

        <div class="as-workflow space-y-4 doc-flow" data-doc-flow>
          <div class="as-workflow__head">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <div class="admin-card-title font-extrabold">Alur dokumen</div>
              </div>
              <div class="admin-card-subtitle">Atur tahapan tambahan untuk layanan dokumen dan lihat preview alur secara realtime.</div>
            </div>
          </div>

          <div class="doc-flow__card">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
              <div class="min-w-0">
                <div class="text-sm font-semibold">Preview alur dokumen</div>
              <div class="text-xs text-muted mt-1">
                Default: Admin Jurusan &rarr; Review ULT &rarr; Penandatangan Fakultas (Dekan/WD). Petugas gate awal bisa diubah ke Staf ULT.
                <?php if($isCertificateMode): ?>
                  Untuk mode Sertifikat/Piagam, sumber dokumen berasal dari upload .pptx pemohon.
                <?php endif; ?>
              </div>
              </div>
              <div class="doc-flow__meta">
                <span class="doc-flow__meta-pill">Nomor Surat: <span class="doc-mono">NOMOR_SURAT</span></span>
              </div>
            </div>

            <div class="doc-flow__disabled-note <?php echo e($docFlowDisabled ? '' : 'hidden'); ?>" data-doc-flow-disabled-note>
              Bagian ini nonaktif karena sumber awal dokumen memakai PPTX pemohon.
            </div>

            <div class="doc-flow__preview">
              <div class="doc-flow__preview-label">Preview realtime</div>
              <div class="doc-flow__preview-value" data-doc-flow-preview aria-live="polite">Memuat preview…</div>
            </div>

            <div class="mt-5">
              <div class="text-sm font-semibold">Opsi alur tambahan (opsional)</div>
              <div class="text-xs text-muted mt-1">Centang sesuai kebutuhan. Opsi yang dipilih akan masuk ke preview alur dokumen.</div>
            </div>

            <div class="as-workflow__grid mt-4 doc-flow__options">
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_pemohon_signature]" value="1" <?php if(!$docFlowDisabled && old('workflow_flags.require_pemohon_signature',$service->workflow?->require_pemohon_signature)): echo 'checked'; endif; ?> <?php if($docFlowDisabled): echo 'disabled'; endif; ?> class="as-check__box" data-doc-flow-flag="pemohon">
                <span class="as-check__label">TTD Pemohon <span class="text-xs text-muted ml-2">(diisi pemohon saat permohonan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_org_secretary_signature]" value="1" <?php if(!$docFlowDisabled && old('workflow_flags.require_org_secretary_signature',$service->workflow?->require_org_secretary_signature)): echo 'checked'; endif; ?> <?php if($docFlowDisabled): echo 'disabled'; endif; ?> class="as-check__box" data-doc-flow-flag="org_secretary">
                <span class="as-check__label">TTD Sekretaris Organisasi <span class="text-xs text-muted ml-2">(sebelum verifikasi Admin Jurusan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_org_chair_signature]" value="1" <?php if(!$docFlowDisabled && old('workflow_flags.require_org_chair_signature',$service->workflow?->require_org_chair_signature)): echo 'checked'; endif; ?> <?php if($docFlowDisabled): echo 'disabled'; endif; ?> class="as-check__box" data-doc-flow-flag="org_chair">
                <span class="as-check__label">TTD Ketua Organisasi <span class="text-xs text-muted ml-2">(sebelum verifikasi Admin Jurusan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_other_lecturer_signature]" value="1" <?php if(!$docFlowDisabled && old('workflow_flags.require_other_lecturer_signature',$service->workflow?->require_other_lecturer_signature)): echo 'checked'; endif; ?> <?php if($docFlowDisabled): echo 'disabled'; endif; ?> class="as-check__box" data-doc-flow-flag="other_lecturer">
                <span class="as-check__label">TTD Dosen lainnya</span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_kaprodi_signature]" value="1" <?php if(!$docFlowDisabled && old('workflow_flags.require_kaprodi_signature',$service->workflow?->require_kaprodi_signature)): echo 'checked'; endif; ?> <?php if($docFlowDisabled): echo 'disabled'; endif; ?> class="as-check__box" data-doc-flow-flag="kaprodi">
                <span class="as-check__label">TTD Ketua Prodi (Kaprodi)</span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_kajur_signature]" value="1" <?php if(!$docFlowDisabled && old('workflow_flags.require_kajur_signature',$service->workflow?->require_kajur_signature)): echo 'checked'; endif; ?> <?php if($docFlowDisabled): echo 'disabled'; endif; ?> class="as-check__box" data-doc-flow-flag="kajur">
                <span class="as-check__label">TTD Ketua Jurusan (Kajur)</span>
              </label>
            </div>

            <div class="doc-flow__hint">
              Nomor surat diisi pada tahap verifikasi petugas gate awal sebelum proses signing.
            </div>
          </div>
        </div>

        <div class="as-form-actions">
          <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?>Simpan <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
          <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','href' => ''.e(route('admin.layanan.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','href' => ''.e(route('admin.layanan.index')).'']); ?>Batal <?php echo $__env->renderComponent(); ?>
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
      </form>
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
  </div>

  <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'mt-4 as-note-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-4 as-note-card']); ?>
    <div class="admin-card-title">Catatan</div>
    <ul class="as-help">
      <li>Preview publik membantu cek tampilan sebelum layanan diaktifkan.</li>
      <li>Opsi alur tambahan hanya mengubah urutan tahapan dokumen (preview & konfigurasi layanan).</li>
      <li>Setup dokumen ada di bawah halaman ini dan wajib untuk layanan dokumen.</li>
    </ul>
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

  <div id="setup-dokumen" class="mt-6">
    <div class="page-admin-services-doc" data-services-doc-page>
      <div class="doc-embed-bar">
        <div class="doc-embed-bar__left">
          <div class="doc-embed-bar__title">Setup Layanan Dokumen</div>
          <div class="doc-embed-bar__sub">
            <?php echo e($isCertificateMode ? 'Mode Sertifikat/Piagam: pemohon upload .pptx, output akhir tetap PDF.' : 'Template DOCX wajib diupload sebelum layanan dipublish.'); ?>

          </div>
        </div>
      </div>

      <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('doc_services.manage')): ?>
        <?php echo $__env->make('admin.services.documents._setup_grid', [
          'service' => $service,
          'readinessErrors' => $readinessErrors,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'doc-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'doc-card']); ?>
          <div class="admin-card-title">Akses dibatasi</div>
          <div class="admin-card-subtitle">Anda tidak memiliki permission untuk mengelola setup dokumen layanan.</div>
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
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/admin/services/edit.blade.php ENDPATH**/ ?>