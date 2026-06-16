<?php $__env->startSection('section', 'Detail Permohonan'); ?>
<?php $__env->startSection('content'); ?>
    <?php
        $status = $req->current_status->value ?? $req->current_status;
        $submittedAt = optional($req->submitted_at ?? $req->created_at)->format('d M Y H:i');
        $historyCount = (int) ($req->histories?->count() ?? 0);
        $generalAttachments = ($req->attachments ?? collect())
            ->filter(fn($a) => (int) ($a->service_field_id ?? 0) === 0 && ($a->kind->value ?? null) === 'input')
            ->values();
        $attachmentCount = (int) $generalAttachments->count();
        $requestSnapshot = is_array($req->data?->document_snapshot_json) ? $req->data->document_snapshot_json : [];
        $snapshotTemplatePath = trim((string) data_get($requestSnapshot, 'template.file_path', ''));
        $hasDocPreview =
            (bool) ($req->service?->usesRequestPptxSource() ||
                $req->service?->templates?->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX) ||
                $snapshotTemplatePath !== '');
        $canEditRequestData = $status === 'PERLU_PERBAIKAN';
        $serviceSigners = $requestSigners ?? ($req->service?->signers ?? collect());
        $isCertificateService = (bool) ($certificateEditorState['is_certificate'] ?? false);
        $customSignatureSigners = $serviceSigners
            ->filter(fn($s) => strtoupper((string) $s->role) === 'CUSTOM')
            ->sortBy('order_index')
            ->values();
        $pemohonSignatureSigners = $serviceSigners
            ->filter(fn($s) => strtoupper((string) $s->role) === 'PEMOHON')
            ->sortBy('order_index')
            ->values();
        $dosenSelectSigners = $serviceSigners
            ->filter(fn($s) => strtoupper((string) $s->role) === 'DOSEN')
            ->sortBy('order_index')
            ->values();
    ?>
    <div class="page-student-requests-show" data-student-requests-show-page>
        <header class="student-page-header">
            <div class="student-page-heading">
                <p class="student-page-kicker">Permohonan <?php echo e($req->request_code); ?></p>
                <h2 class="student-page-title"><?php echo e($req->display_title); ?></h2>
                <p class="student-page-subtitle">Detail permohonan, lampiran, catatan, dan riwayat status.</p>
            </div>
            <div class="student-page-actions">
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => 'student-page-back-btn !border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white','variant' => 'ghost','href' => ''.e(route('student.requests.index')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-page-back-btn !border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white','variant' => 'ghost','href' => ''.e(route('student.requests.index')).'']); ?>Kembali <?php echo $__env->renderComponent(); ?>
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

        <div class="student-show-layout">
            <div class="student-show-main">
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-show-card student-show-card--overview']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-show-card student-show-card--overview']); ?>
                    <div class="student-request-hero">
                        <div class="student-request-hero__left">
                            <div class="student-request-hero__kicker">Status saat ini</div>
                            <div class="student-request-hero__badge">
                                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status)]); ?>
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
                        </div>
                        <div class="student-request-hero__right">
                            <div class="student-request-hero__label">Diajukan</div>
                            <div class="student-request-hero__time"><?php echo e($submittedAt); ?></div>
                        </div>
                    </div>

                    <div class="student-overview-grid">
                        <?php if($req->documentNumber): ?>
                            <div class="student-docnum student-docnum--document">
                                <div class="student-docnum__label">Nomor dokumen</div>
                                <div class="student-docnum__value">
                                    <?php echo e(app(\App\Services\DocumentNumberService::class)->renderNumber($req->documentNumber)); ?>

                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="student-docnum student-docnum--action">
                            <div class="student-docnum__header">
                                <div class="student-docnum__label">Aksi</div>
                            </div>
                            <div class="student-docnum__body">
                            <?php if(($req->current_status->value ?? $req->current_status) === 'PERLU_PERBAIKAN'): ?>
                                <form class="student-revision-form space-y-3" method="POST"
                                    action="<?php echo e(route('student.requests.revision', $req)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['name' => 'note','rows' => '3','label' => 'Catatan perbaikan']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'note','rows' => '3','label' => 'Catatan perbaikan']); ?><?php echo e(old('note')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?>Kirim perbaikan <?php echo $__env->renderComponent(); ?>
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
                            <?php else: ?>
                                <div class="student-action-empty">
                                    <div class="student-action-empty__text">Tidak ada aksi saat ini.</div>
                                </div>
                            <?php endif; ?>
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

                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-show-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-show-card']); ?>
                    <div class="student-card-header">
                        <div class="student-card-title">Data permohonan</div>
                        <div class="student-card-subtitle">
                            <?php echo e($canEditRequestData ? 'Perbaiki data permohonan Anda lalu kirim perbaikan.' : 'Data yang Anda kirim saat pengajuan.'); ?>

                        </div>
                    </div>

                    <?php if($canEditRequestData): ?>
                        <form method="POST" action="<?php echo e(route('student.requests.data.update', $req)); ?>" class="space-y-3"
                            enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="grid gap-3 md:grid-cols-2">
                                <?php $__currentLoopData = ($requestFields ?? collect())->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $name = "fields[{$f->id}]";
                                        $oldKey = "fields.{$f->id}";
                                        $rawOld = old($oldKey);
                                        $fv = $req->fieldValues->firstWhere('service_field_id', $f->id);
                                        $label =
                                            app()->getLocale() === 'en' ? $f->label_en ?? $f->label_id : $f->label_id;
                                        $currentValue = '';
                                        $currentAttachment = null;
                                        $isFull = in_array($f->type, ['textarea', 'richtext', 'json', 'file'], true);

                                        if ($f->type === 'file') {
                                            $currentAttachmentId = null;
                                            if (
                                                is_array($fv?->value_json) &&
                                                isset($fv->value_json['attachment_id']) &&
                                                is_numeric($fv->value_json['attachment_id'])
                                            ) {
                                                $currentAttachmentId = (int) $fv->value_json['attachment_id'];
                                            }
                                            if ($currentAttachmentId) {
                                                $currentAttachment = $req->attachments->firstWhere(
                                                    'id',
                                                    $currentAttachmentId,
                                                );
                                            }
                                        } elseif ($rawOld !== null) {
                                            $currentValue = $rawOld;
                                        } elseif (!$fv) {
                                            $currentValue = '';
                                        } elseif ($f->type === 'number') {
                                            $currentValue = $fv->value_number ?? '';
                                        } elseif ($f->type === 'date') {
                                            $currentValue = optional($fv->value_date)->format('Y-m-d');
                                        } elseif ($f->type === 'json') {
                                            if (is_array($fv->value_json)) {
                                                $currentValue = array_key_exists('value', $fv->value_json)
                                                    ? (string) $fv->value_json['value']
                                                    : json_encode($fv->value_json, JSON_UNESCAPED_UNICODE);
                                            } else {
                                                $currentValue = '';
                                            }
                                        } else {
                                            $currentValue = $fv->value_text ?? '';
                                        }
                                    ?>

                                    <div class="<?php echo e($isFull ? 'md:col-span-2' : ''); ?>">
                                        <?php if($f->type === 'file'): ?>
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
                                            <?php if($currentAttachment): ?>
                                                <div class="text-xs text-muted mt-1">
                                                    File saat ini: <strong><?php echo e($currentAttachment->original_name); ?></strong>
                                                    (<a
                                                        href="<?php echo e(route('attachments.download', $currentAttachment)); ?>">download</a>)
                                                </div>
                                            <?php else: ?>
                                                <div class="text-xs text-muted mt-1">Belum ada file tersimpan untuk field
                                                    ini.</div>
                                            <?php endif; ?>
                                        <?php elseif($f->type === 'richtext'): ?>
                                            <?php if (isset($component)) { $__componentOriginala75492aff34d1af0bd0908127080afc5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala75492aff34d1af0bd0908127080afc5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.tiptap-editor','data' => ['label' => $label,'name' => $name,'value' => $currentValue,'height' => 'min-h-[180px]']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('tiptap-editor'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentValue),'height' => 'min-h-[180px]']); ?>
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
                                        <?php elseif($f->type === 'textarea' || $f->type === 'json'): ?>
                                            <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['label' => $label,'name' => $name,'rows' => '4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'rows' => '4']); ?><?php echo e($currentValue); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
                                        <?php elseif($f->type === 'date'): ?>
                                            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'date','label' => $label,'name' => $name,'value' => $currentValue]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'date','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentValue)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'number','label' => $label,'name' => $name,'value' => $currentValue]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'number','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentValue)]); ?>
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
                                                    <option value="<?php echo e($opt); ?>" <?php if((string) $currentValue === (string) $opt): echo 'selected'; endif; ?>>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'text','label' => $label,'name' => $name,'value' => $currentValue]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($currentValue)]); ?>
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

                            <?php if($isCertificateService): ?>
                                <?php echo $__env->make('student.requests._certificate_fields', [
                                    'certificateEditorState' => $certificateEditorState ?? [],
                                    'certificateInternalSignerOptions' =>
                                        $certificateInternalSignerOptions ?? collect(),
                                    'isRevision' => true,
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endif; ?>

                            <?php if(!$isCertificateService && $pemohonSignatureSigners->isNotEmpty()): ?>
                                <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
                                    <div class="text-sm font-semibold">Tanda tangan pemohon</div>
                                    <div class="text-xs text-muted mt-1">
                                        Unggah file baru untuk mengganti tanda tangan pemohon yang sudah tersimpan.
                                    </div>

                                    <div class="grid gap-5 mt-4">
                                        <?php $__currentLoopData = $pemohonSignatureSigners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $idx = (int) $s->order_index;
                                                $mimeTypes = is_array($s->signature_file_types)
                                                    ? array_values(array_filter($s->signature_file_types))
                                                    : [];
                                                $mimeTypes = array_values(
                                                    array_intersect($mimeTypes, [
                                                        'image/png',
                                                        'image/jpeg',
                                                        'image/webp',
                                                    ]),
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
                                                $existingSignoff = $req->signoffs->first(function ($so) use ($idx) {
                                                    return (int) ($so->order_index ?? 0) === $idx &&
                                                        strtoupper((string) ($so->signer_role ?? '')) === 'PEMOHON';
                                                });
                                                $hasExisting =
                                                    trim((string) ($existingSignoff->signature_file_path ?? '')) !== '';
                                                $helpParts = [];
                                                if (!empty($mimeLabels)) {
                                                    $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                                }
                                                if ($maxKb > 0) {
                                                    $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                                }
                                                $helpParts[] = $hasExisting
                                                    ? 'Saat ini: sudah ada tanda tangan tersimpan.'
                                                    : 'Saat ini: belum ada tanda tangan tersimpan (wajib upload).';
                                                $help = implode(' ', $helpParts);
                                                $previewUrl = $hasExisting
                                                    ? route('student.requests.signature.preview', [
                                                        'request' => $req,
                                                        'signoff' => $existingSignoff,
                                                    ])
                                                    : null;
                                            ?>
                                            <div class="grid gap-4 md:gap-6 md:grid-cols-2 items-start"
                                                data-signature-live-preview-item>
                                                <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'file','name' => 'pemohon_signatures[' . $idx . ']','label' => 'Tanda tangan ' . $labelFromAdmin,'accept' => $accept,'help' => $help,'dataSignatureLivePreviewInput' => '1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('pemohon_signatures[' . $idx . ']'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Tanda tangan ' . $labelFromAdmin),'accept' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accept),'help' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($help),'data-signature-live-preview-input' => '1']); ?>
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
                                                <div class="rounded-xl border border-[rgb(var(--c-border))] bg-white/70 p-3 <?php if(!$previewUrl): ?> hidden <?php endif; ?>"
                                                    data-signature-live-preview-box>
                                                    <div class="text-xs text-muted mb-1" data-signature-live-preview-label>
                                                        <?php if($previewUrl): ?>
                                                            Tanda tangan tersimpan
                                                        <?php else: ?>
                                                            Preview tanda tangan
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="<?php echo e($previewUrl ?: '#'); ?>" target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-block <?php if(!$previewUrl): ?> pointer-events-none <?php endif; ?>"
                                                        data-signature-live-preview-link>
                                                        <img src="<?php echo e($previewUrl ?: ''); ?>"
                                                            data-signature-stored-src="<?php echo e($previewUrl ?: ''); ?>"
                                                            data-signature-live-preview-img
                                                            alt="Preview tanda tangan <?php echo e($labelFromAdmin); ?>"
                                                            class="h-24 w-full object-contain">
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(!$isCertificateService && $customSignatureSigners->isNotEmpty()): ?>
                                <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
                                    <div class="text-sm font-semibold">Penandatangan lain</div>
                                    <div class="text-xs text-muted mt-1">
                                        Anda bisa mengganti tanda tangan penandatangan custom saat perbaikan.
                                    </div>

                                    <div class="grid gap-5 mt-4">
                                        <?php $__currentLoopData = $customSignatureSigners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $idx = (int) $s->order_index;
                                                $mimeTypes = is_array($s->signature_file_types)
                                                    ? array_values(array_filter($s->signature_file_types))
                                                    : [];
                                                $mimeTypes = array_values(
                                                    array_intersect($mimeTypes, [
                                                        'image/png',
                                                        'image/jpeg',
                                                        'image/webp',
                                                    ]),
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
                                                $existingSignoff = $req->signoffs->first(function ($so) use ($idx) {
                                                    return (int) ($so->order_index ?? 0) === $idx &&
                                                        strtoupper((string) ($so->signer_role ?? '')) === 'CUSTOM';
                                                });
                                                $hasExisting =
                                                    trim((string) ($existingSignoff->signature_file_path ?? '')) !== '';
                                                $helpParts = [];
                                                if (!empty($mimeLabels)) {
                                                    $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                                }
                                                if ($maxKb > 0) {
                                                    $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                                }
                                                $helpParts[] = $hasExisting
                                                    ? 'Saat ini: sudah ada tanda tangan tersimpan.'
                                                    : 'Saat ini: belum ada tanda tangan tersimpan (wajib upload).';
                                                $help = implode(' ', $helpParts);
                                                $previewUrl = $hasExisting
                                                    ? route('student.requests.signature.preview', [
                                                        'request' => $req,
                                                        'signoff' => $existingSignoff,
                                                    ])
                                                    : null;
                                            ?>
                                            <div class="grid gap-4 md:gap-6 md:grid-cols-2 items-start"
                                                data-signature-live-preview-item>
                                                <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'file','name' => 'custom_signatures[' . $idx . ']','label' => 'Tanda tangan ' . $labelFromAdmin,'accept' => $accept,'help' => $help,'dataSignatureLivePreviewInput' => '1']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('custom_signatures[' . $idx . ']'),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Tanda tangan ' . $labelFromAdmin),'accept' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($accept),'help' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($help),'data-signature-live-preview-input' => '1']); ?>
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
                                                <div class="rounded-xl border border-[rgb(var(--c-border))] bg-white/70 p-3 <?php if(!$previewUrl): ?> hidden <?php endif; ?>"
                                                    data-signature-live-preview-box>
                                                    <div class="text-xs text-muted mb-1" data-signature-live-preview-label>
                                                        <?php if($previewUrl): ?>
                                                            Tanda tangan tersimpan
                                                        <?php else: ?>
                                                            Preview tanda tangan
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="<?php echo e($previewUrl ?: '#'); ?>" target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-block <?php if(!$previewUrl): ?> pointer-events-none <?php endif; ?>"
                                                        data-signature-live-preview-link>
                                                        <img src="<?php echo e($previewUrl ?: ''); ?>"
                                                            data-signature-stored-src="<?php echo e($previewUrl ?: ''); ?>"
                                                            data-signature-live-preview-img
                                                            alt="Preview tanda tangan <?php echo e($labelFromAdmin); ?>"
                                                            class="h-24 w-full object-contain">
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(!$isCertificateService && $dosenSelectSigners->isNotEmpty()): ?>
                                <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
                                    <div class="text-sm font-semibold">Pemilihan dosen penandatangan</div>
                                    <div class="text-xs text-muted mt-1">
                                        Anda dapat memilih atau mengganti dosen/pimpinan untuk step role DOSEN.
                                    </div>

                                    <div class="grid gap-4 mt-4 lg:grid-cols-2">
                                        <?php $__currentLoopData = $dosenSelectSigners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $idx = (int) $s->order_index;
                                                $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                                if ($labelFromAdmin === '') {
                                                    $labelFromAdmin = 'Dosen #' . $idx;
                                                }
                                                $existingSignoff = $req->signoffs->first(function ($so) use ($idx) {
                                                    return (int) ($so->order_index ?? 0) === $idx &&
                                                        strtoupper((string) ($so->signer_role ?? '')) === 'DOSEN';
                                                });
                                                $existingUserId = (int) ($existingSignoff->signer_user_id ?? 0);
                                                $selectedUserId = (int) old(
                                                    'dosen_signers.' . $idx,
                                                    $existingUserId > 0 ? $existingUserId : 0,
                                                );
                                            ?>
                                            <div class="space-y-1">
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
                                                <div class="text-xs text-muted">
                                                    <?php echo e($existingUserId > 0 ? 'Saat ini: user signer sudah dipilih.' : 'Saat ini: belum ada user signer terpilih.'); ?>

                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="flex flex-wrap items-center gap-2">
                                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?>Simpan perubahan data <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                                <span class="text-xs text-muted">
                                    <?php if($isCertificateService): ?>
                                        Untuk sertifikat/piagam, Anda bisa memperbarui dokumen sumber .pptx dan daftar
                                        signer di form ini.
                                    <?php else: ?>
                                        Semua data form, termasuk pilihan dosen signer, tanda tangan pemohon/custom, dan
                                        lampiran umum, bisa diperbarui dari form ini.
                                    <?php endif; ?>
                                </span>
                            </div>

                            <?php if($req->service?->allow_general_attachments): ?>
                                <div class="student-create-subsection mt-6">
                                    <div class="student-create-subsection__title">Lampiran umum</div>
                                    <div class="student-create-subsection__subtitle">
                                        Tambahkan lampiran pendukung baru jika diperlukan. File lama tetap tersimpan dan
                                        tidak akan diganti.
                                    </div>

                                    <div class="student-create-subsection__grid">
                                        <div class="student-create-subsection__field space-y-3">
                                            <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['type' => 'file','name' => 'attachments[]','label' => 'Tambah lampiran pendukung','multiple' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'file','name' => 'attachments[]','label' => 'Tambah lampiran pendukung','multiple' => true]); ?>
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
                                                Bagian ini opsional dan mendukung lebih dari satu file.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <div class="student-kv-list">
                            <?php $fieldValues = $req->fieldValues->keyBy('service_field_id'); ?>
                            <?php if(filled($req->activity_title)): ?>
                                <div class="student-kv">
                                    <div class="student-kv__label">Judul permohonan</div>
                                    <div class="student-kv__value"><?php echo e($req->display_title); ?></div>
                                </div>
                                <div class="student-kv">
                                    <div class="student-kv__label">Judul kegiatan</div>
                                    <div class="student-kv__value"><?php echo e($req->activity_title); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php $__currentLoopData = ($requestFields ?? collect())->sortBy('sort_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($f->type === 'file') continue; ?>
                                <?php
                                    $fv = $fieldValues->get($f->id);
                                    $isRichText = $f->type === 'richtext';
                                    $display = '-';
                                    if ($fv) {
                                        if ($fv->value_text !== null && $fv->value_text !== '') {
                                            $display = $fv->value_text;
                                        } elseif ($fv->value_number !== null) {
                                            $display = $fv->value_number;
                                        } elseif ($fv->value_date !== null) {
                                            $display = optional($fv->value_date)->format('Y-m-d') ?: '-';
                                        } elseif (is_array($fv->value_json)) {
                                            $display = json_encode($fv->value_json, JSON_UNESCAPED_UNICODE);
                                        }
                                    }
                                ?>
                                <div class="student-kv">
                                    <div class="student-kv__label"><?php echo e($f->label_id); ?></div>
                                    <div class="student-kv__value">
                                        <?php if($isRichText && $display !== '-'): ?>
                                            <div class="content-prose"><?php echo app(\App\Services\HtmlSanitizer::class)->clean((string) $display); ?></div>
                                        <?php else: ?>
                                            <?php echo e($display); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <?php if($req->outputs->first() || $hasDocPreview): ?>
                        <div class="student-data-actions">
                            <div class="student-data-actions__left">
                                <?php if($req->outputs->first()): ?>
                                    <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => '!border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white','variant' => 'secondary','href' => ''.e(route('student.requests.output', $req)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white','variant' => 'secondary','href' => ''.e(route('student.requests.output', $req)).'']); ?>Unduh berkas <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="student-data-actions__right">
                                <?php if($hasDocPreview): ?>
                                    <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => '!border-[rgb(var(--c-primary))] !bg-transparent !text-[rgb(var(--c-primary))] hover:!border-[rgb(var(--c-primary))] hover:!bg-[rgb(var(--c-primary))] hover:!text-white dark:!border-[rgb(var(--c-primary))] dark:!bg-transparent dark:!text-[rgb(var(--c-primary))] dark:hover:!border-[rgb(var(--c-primary))] dark:hover:!bg-[rgb(var(--c-primary))] dark:hover:!text-white','variant' => 'ghost','href' => ''.e(route('requests.preview', $req)).'','target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => '!border-[rgb(var(--c-primary))] !bg-transparent !text-[rgb(var(--c-primary))] hover:!border-[rgb(var(--c-primary))] hover:!bg-[rgb(var(--c-primary))] hover:!text-white dark:!border-[rgb(var(--c-primary))] dark:!bg-transparent dark:!text-[rgb(var(--c-primary))] dark:hover:!border-[rgb(var(--c-primary))] dark:hover:!bg-[rgb(var(--c-primary))] dark:hover:!text-white','variant' => 'ghost','href' => ''.e(route('requests.preview', $req)).'','target' => '_blank']); ?>Buka preview <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
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

                <?php if($req->service?->allow_general_attachments): ?>
                    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-show-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-show-card']); ?>
                        <div class="student-card-header">
                            <div class="student-card-title">Lampiran</div>
                            <div class="student-card-subtitle">Daftar lampiran umum yang sudah diunggah bersama form
                                permohonan.
                            </div>
                        </div>

                        <div class="student-attachment-list">
                            <?php $__empty_1 = true; $__currentLoopData = $generalAttachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="student-attachment-item">
                                    <div class="student-attachment-item__meta">
                                        <div class="student-attachment-item__name"><?php echo e($a->original_name); ?></div>
                                        <div class="student-attachment-item__sub"><?php echo e($a->kind->value); ?> &bull;
                                            <?php echo e(number_format($a->size / 1024, 1)); ?> KB</div>
                                    </div>
                                    <div class="student-attachment-item__actions">
                                        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','href' => ''.e(route('attachments.download', $a)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','href' => ''.e(route('attachments.download', $a)).'']); ?>Download <?php echo $__env->renderComponent(); ?>
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
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="student-empty">Belum ada lampiran umum.</div>
                            <?php endif; ?>
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
                <?php endif; ?>

                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-show-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-show-card']); ?>
                    <div class="student-card-header">
                        <div class="student-card-title">Catatan</div>
                        <div class="student-card-subtitle">Catatan terlihat oleh Anda dan petugas (kecuali catatan
                            internal).</div>
                    </div>
                    <form class="student-note-form" method="POST" action="<?php echo e(route('student.requests.note', $req)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php if (isset($component)) { $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.textarea','data' => ['name' => 'body','rows' => '3','label' => 'Tulis catatan (opsional)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'body','rows' => '3','label' => 'Tulis catatan (opsional)']); ?><?php echo e(old('body')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $attributes = $__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__attributesOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886)): ?>
<?php $component = $__componentOriginal4727f9fd7c3055c2cf9c658d89b16886; ?>
<?php unset($__componentOriginal4727f9fd7c3055c2cf9c658d89b16886); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','class' => 'student-note-submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'student-note-submit']); ?>
                            Kirim Catatan
                         <?php echo $__env->renderComponent(); ?>
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
                    <div class="student-note-list">
                        <?php $__currentLoopData = $req->notes->where('is_internal', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="student-note">
                                <div class="student-note__meta"><?php echo e($n->actor?->name); ?> &bull;
                                    <?php echo e(\Carbon\Carbon::parse($n->created_at)->format('d M Y H:i')); ?></div>
                                <div class="student-note__body"><?php echo e($n->body); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            </div>

            <div class="student-show-side">
                <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'student-show-card']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'student-show-card']); ?>
                    <div class="student-card-header">
                        <div class="student-card-title">Riwayat Status</div>
                    </div>
                    <div class="student-history-list">
                        <?php $__currentLoopData = $req->histories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="student-history">
                                <div class="student-history__meta">
                                    <?php echo e(\Carbon\Carbon::parse($h->created_at)->format('d M Y H:i')); ?> &bull;
                                    <?php echo e($h->actor?->name); ?></div>
                                <div class="student-history__body">
                                    <div class="student-history__line"><?php echo e($h->from_status ?? '-'); ?> &rarr; <span
                                            class="student-history__to"><?php echo e($h->to_status); ?></span></div>
                                    <?php if($h->note): ?>
                                        <div class="student-history__note"><?php echo e($h->note); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/student/requests/show.blade.php ENDPATH**/ ?>