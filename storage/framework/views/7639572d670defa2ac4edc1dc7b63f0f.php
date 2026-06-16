<?php $__env->startSection('section', 'Ajukan'); ?>
<?php $__env->startSection('content'); ?>
    <?php
        $fieldCount = (int) count($fields ?? []);
    ?>
    <div class="page-student-requests-create" data-student-requests-create-page>
        <header class="student-page-header student-create-hero">
            <div class="student-page-heading student-create-hero__heading">
                <div class="student-create-hero__kicker-row">
                    <div class="student-page-kicker">Ajukan layanan</div>
                </div>
                <h2 class="student-page-title"><?php echo e($service->title_id); ?></h2>
                <p class="student-page-subtitle">Lengkapi data dengan benar sebelum mengirim permohonan.
                </p>
            </div>
            <div class="student-page-actions student-create-hero__actions">
                <div class="student-meta student-create-hero__meta">
                    <div class="student-meta-pill" aria-label="Jumlah field">
                        <div class="student-meta-pill__label">Field</div>
                        <div class="student-meta-pill__value"><?php echo e($fieldCount); ?></div>
                    </div>
                </div>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','class' => 'student-page-detail-btn','href' => ''.e(route('services.show', $service)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','class' => 'student-page-detail-btn','href' => ''.e(route('services.show', $service)).'']); ?>Lihat detail <?php echo $__env->renderComponent(); ?>
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

        <div class="student-create-layout">
            <div class="student-create-main">
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-create-form']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-create-form']); ?>
                    <div class="student-card-header student-create-form__header">
                        <div>
                            <div class="student-card-title">Form permohonan</div>
                            <div class="student-card-subtitle">Field ditampilkan sesuai kebutuhan layanan.</div>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo e(route('student.requests.store')); ?>" class="student-form"
                        enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="service_id" value="<?php echo e($service->id); ?>">

                        <?php if($fieldCount > 0): ?>
                            <div class="student-form-fields">
                                <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $name = "fields[{$f->id}]";
                                        $oldKey = "fields.{$f->id}";
                                        $oldValue = old($oldKey);
                                        $label = app()->getLocale() === 'en' ? $f->label_en ?? $f->label_id : $f->label_id;
                                        $isFull = in_array($f->type, ['textarea', 'richtext'], true);
                                    ?>

                                    <div class="student-form-field <?php echo e($isFull ? 'is-full' : ''); ?>">
                                        <?php if($f->type === 'richtext'): ?>
                                            <?php if (isset($component)) { $__componentOriginala75492aff34d1af0bd0908127080afc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala75492aff34d1af0bd0908127080afc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tiptap-editor','data' => ['label' => $label,'name' => $name,'value' => $oldValue,'height' => 'min-h-[180px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tiptap-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oldValue),'height' => 'min-h-[180px]']); ?>
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
                                        <?php elseif($isFull): ?>
                                            <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['label' => $label,'name' => $name,'rows' => '4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'rows' => '4']); ?><?php echo e($oldValue); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
                                        <?php elseif($f->type === 'file'): ?>
                                            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'file','label' => $label,'name' => $name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name)]); ?>
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
                                        <?php elseif($f->type === 'date'): ?>
                                            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'date','label' => $label,'name' => $name,'value' => $oldValue]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oldValue)]); ?>
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
                                        <?php elseif($f->type === 'number'): ?>
                                            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'number','label' => $label,'name' => $name,'value' => $oldValue]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oldValue)]); ?>
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
                                        <?php elseif($f->type === 'select'): ?>
                                            <?php if (isset($component)) { $__componentOriginaled2cde6083938c436304f332ba96bb7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaled2cde6083938c436304f332ba96bb7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.select','data' => ['label' => $label,'name' => $name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name)]); ?>
                                                <option value="">-- pilih --</option>
                                                <?php $__currentLoopData = $f->options_json ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($opt); ?>" <?php if((string) $oldValue === (string) $opt): echo 'selected'; endif; ?>>
                                                        <?php echo e($opt); ?></option>
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
                                        <?php else: ?>
                                            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'text','label' => $label,'name' => $name,'value' => $oldValue]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($oldValue)]); ?>
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
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($isCertificateService)): ?>
                            <?php echo $__env->make('student.requests._certificate_fields', [
                                'certificateEditorState' => $certificateEditorState ?? [],
                                'certificateInternalSignerOptions' =>
                                    $certificateInternalSignerOptions ?? collect(),
                                'isRevision' => false,
                                'hasBaseFields' => $fieldCount > 0,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?>

                        <?php if(empty($isCertificateService) && !empty($pemohonSignatureSigners) && count($pemohonSignatureSigners) > 0): ?>
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Tanda tangan pemohon</div>
                                <div class="student-create-subsection__subtitle">
                                    Upload tanda tangan pemohon agar proses penandatanganan lebih cepat.
                                </div>

                                <div class="student-create-subsection__grid">
                                    <?php $__currentLoopData = $pemohonSignatureSigners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $idx = (int) $s->order_index;
                                            $mimeTypes = is_array($s->signature_file_types)
                                                ? array_values(array_filter($s->signature_file_types))
                                                : [];
                                            $mimeTypes = array_values(
                                                array_intersect($mimeTypes, ['image/png', 'image/jpeg', 'image/webp']),
                                            );
                                            if (empty($mimeTypes)) {
                                                $mimeTypes = ['image/png', 'image/jpeg', 'image/webp'];
                                            }
                                            $mimeLabels = [];
                                            foreach ($mimeTypes as $mime) {
                                                $mimeLabels[] = match ($mime) {
                                                    'image/png' => 'PNG',
                                                    'image/jpeg' => 'JPG/JPEG',
                                                    'image/webp' => 'WEBP',
                                                    default => (string) $mime,
                                                };
                                            }
                                            $mimeLabels = array_values(array_unique(array_filter($mimeLabels)));
                                            $accept = implode(',', $mimeTypes);
                                            $maxKb = (int) ($s->signature_max_size_kb ?? 0);
                                            if ($maxKb <= 0) {
                                                $maxKb = 256;
                                            }
                                            $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                            if ($labelFromAdmin === '') {
                                                $labelFromAdmin = 'Pemohon #' . $idx;
                                            }
                                            $helpParts = [];
                                            if (!empty($mimeLabels)) {
                                                $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                            }
                                            if ($maxKb > 0) {
                                                $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                            }
                                            $help = implode(' ', $helpParts);
                                        ?>
                                        <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'file','name' => 'pemohon_signatures[' . $idx . ']','label' => 'Tanda tangan ' . $labelFromAdmin,'accept' => $accept,'help' => $help]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('pemohon_signatures[' . $idx . ']'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Tanda tangan ' . $labelFromAdmin),'accept' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accept),'help' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($help)]); ?>
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
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(empty($isCertificateService) && !empty($customSigners) && count($customSigners) > 0): ?>
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Penandatangan lain</div>
                                <div class="student-create-subsection__subtitle">
                                    Layanan ini membutuhkan unggah tanda tangan tambahan untuk penandatangan lain. Label
                                    ditentukan oleh admin.
                                </div>

                                <div class="student-create-subsection__grid">
                                    <?php $__currentLoopData = $customSigners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $idx = (int) $s->order_index;
                                            $mimeTypes = is_array($s->signature_file_types)
                                                ? array_values(array_filter($s->signature_file_types))
                                                : [];
                                            $mimeTypes = array_values(
                                                array_intersect($mimeTypes, ['image/png', 'image/jpeg', 'image/webp']),
                                            );
                                            if (empty($mimeTypes)) {
                                                $mimeTypes = ['image/png', 'image/jpeg', 'image/webp'];
                                            }
                                            $mimeLabels = [];
                                            foreach ($mimeTypes as $mime) {
                                                $mimeLabels[] = match ($mime) {
                                                    'image/png' => 'PNG',
                                                    'image/jpeg' => 'JPG/JPEG',
                                                    'image/webp' => 'WEBP',
                                                    default => (string) $mime,
                                                };
                                            }
                                            $mimeLabels = array_values(array_unique(array_filter($mimeLabels)));
                                            $accept = implode(',', $mimeTypes);
                                            $maxKb = (int) ($s->signature_max_size_kb ?? 0);
                                            if ($maxKb <= 0) {
                                                $maxKb = 256;
                                            }
                                            $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                            if ($labelFromAdmin === '') {
                                                $labelFromAdmin = 'Penandatangan #' . $idx;
                                            }
                                            $helpParts = [];
                                            if (!empty($mimeLabels)) {
                                                $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                            }
                                            if ($maxKb > 0) {
                                                $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                            }
                                            $help = implode(' ', $helpParts);
                                        ?>
                                        <div class="student-create-subsection__field space-y-3">
                                            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'file','name' => 'custom_signatures[' . $idx . ']','label' => 'Tanda tangan ' . $labelFromAdmin,'accept' => $accept,'help' => $help]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('custom_signatures[' . $idx . ']'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Tanda tangan ' . $labelFromAdmin),'accept' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accept),'help' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($help)]); ?>
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
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(empty($isCertificateService) && !empty($dosenSelectSigners) && count($dosenSelectSigners) > 0): ?>
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Pemilihan dosen penandatangan</div>
                                <div class="student-create-subsection__subtitle">
                                    Pilih dosen/pimpinan yang akan menjadi penandatangan untuk tahapan role DOSEN.
                                </div>

                                <div class="student-create-subsection__grid">
                                    <?php $__currentLoopData = $dosenSelectSigners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $idx = (int) $s->order_index;
                                            $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                            if ($labelFromAdmin === '') {
                                                $labelFromAdmin = 'Dosen #' . $idx;
                                            }
                                            $selectedUserId = (int) old('dosen_signers.' . $idx, 0);
                                        ?>

                                        <?php if (isset($component)) { $__componentOriginaled2cde6083938c436304f332ba96bb7c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaled2cde6083938c436304f332ba96bb7c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.select','data' => ['name' => 'dosen_signers[' . $idx . ']','label' => 'Pilih ' . $labelFromAdmin,'dataScrollableUserSelect' => '1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('dosen_signers[' . $idx . ']'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Pilih ' . $labelFromAdmin),'data-scrollable-user-select' => '1']); ?>
                                            <option value="">-- pilih user --</option>
                                            <?php $__currentLoopData = $dosenSignerOptions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($u->id); ?>" <?php if($selectedUserId === (int) $u->id): echo 'selected'; endif; ?>>
                                                    <?php echo e($u->name); ?></option>
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
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if($service->allow_general_attachments): ?>
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Lampiran umum</div>
                                <div class="student-create-subsection__subtitle">
                                    Unggah file pendukung tambahan jika diperlukan. Bagian ini opsional dan Anda dapat memilih lebih dari satu file.
                                </div>

                                <div class="student-create-subsection__grid">
                                    <div class="student-create-subsection__field space-y-3">
                                        <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'file','name' => 'attachments[]','label' => 'File lampiran pendukung','multiple' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','name' => 'attachments[]','label' => 'File lampiran pendukung','multiple' => true]); ?>
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
                                        <div class="text-xs text-muted">
                                            Kosongkan jika layanan ini tidak membutuhkan lampiran tambahan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="student-form-actions">
                            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','class' => 'student-form-actions__primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'student-form-actions__primary']); ?>Submit <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','class' => 'student-form-actions__secondary','href' => ''.e(route('services.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => 'student-form-actions__secondary','href' => ''.e(route('services.index')).'']); ?>Batal <?php echo $__env->renderComponent(); ?>
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

            <aside class="student-create-aside">
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-create-help-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-create-help-card']); ?>
                    <div class="student-card-title">Panduan singkat</div>
                    <ul class="student-help-list">
                        <li>Pastikan data sesuai dokumen resmi.</li>
                        <li>Cek kembali ejaan dan format tanggal/angka.</li>
                        <?php if($service->allow_general_attachments): ?>
                            <li>Lampiran umum bisa diunggah langsung dari form ini dan bisa lebih dari satu file.</li>
                        <?php endif; ?>
                        <?php if(!empty($isCertificateService)): ?>
                            <li>Untuk sertifikat/piagam, wajib upload dokumen sumber <span class="font-mono">.pptx</span>
                                dan isi signer sesuai token.</li>
                            <li>Gunakan font umum (Times New Roman/Arial/Calibri), hindari mirror/flip, dan cek file yang dipilih sebelum submit.</li>
                        <?php endif; ?>
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

                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-create-note student-create-note-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-create-note student-create-note-card']); ?>
                    <div class="student-card-title">Catatan</div>
                    <div class="student-create-note__body">
                        Permohonan akan diproses sesuai antrian. Anda dapat memantau status dari halaman permohonan.
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
            </aside>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/student/requests/create.blade.php ENDPATH**/ ?>