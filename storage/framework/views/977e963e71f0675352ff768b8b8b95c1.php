<?php $__currentLoopData = $guides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $title = $isEn ? ($guide->title_en ?: $guide->title_id) : $guide->title_id;
        $summary = $isEn ? ($guide->summary_en ?: $guide->summary_id) : $guide->summary_id;
        $summary = filled($summary)
            ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', (string) $summary)), 160, '...')
            : ($guide->isVideo()
                ? ($isEn ? 'Video tutorial guide.' : 'Panduan dalam bentuk video tutorial.')
                : ($isEn ? 'PDF user guide document.' : 'Dokumen panduan pengguna dalam format PDF.'));
        $roles = $guide->roles->pluck('name')->values()->all();
        $detailUrl = $guide->isVideo() ? route('user_guides.show', $guide->slug) : route('user_guides.file', $guide->slug);
        $searchText = trim(
            implode(' ', array_filter([
                $guide->title_id,
                $guide->title_en,
                $guide->summary_id,
                $guide->summary_en,
                $guide->video_url,
                $guide->isVideo() ? 'video youtube tutorial' : 'pdf dokumen file',
                implode(' ', $roles),
                $guide->is_public ? 'umum public guest' : 'role login',
            ])),
        );
    ?>
    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'card-item services-catalog-card user-guide-card','dataServicesReveal' => true,'dataRealtimeSearchItem' => true,'dataRealtimeSearchText' => ''.e($searchText).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'card-item services-catalog-card user-guide-card','data-services-reveal' => true,'data-realtime-search-item' => true,'data-realtime-search-text' => ''.e($searchText).'']); ?>
        <div class="services-catalog-card__head">
            <span class="services-catalog-card__category"><?php echo e($guide->isVideo() ? ($isEn ? 'Video Guide' : 'Panduan Video') : ($isEn ? 'User Guide' : 'Panduan')); ?></span>
            <?php if($guide->is_public): ?>
                <span class="services-catalog-card__index"><?php echo e($isEn ? 'Public' : 'Umum'); ?></span>
            <?php else: ?>
                <span class="services-catalog-card__index"><?php echo e($isEn ? 'Role-based' : 'Berdasarkan role'); ?></span>
            <?php endif; ?>
        </div>
        <h3 class="services-catalog-card__title">
            <a href="<?php echo e($detailUrl); ?>" target="_blank"><?php echo e($title); ?></a>
        </h3>
        <p class="services-catalog-card__desc"><?php echo e($summary); ?></p>
        <?php if(!empty($roles)): ?>
            <div class="user-guide-card__roles">
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roleName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="user-guide-card__role"><?php echo e($roleName); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <div class="services-catalog-card__actions user-guide-card__actions">
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','class' => 'services-catalog-card__detail-btn','href' => ''.e($detailUrl).'','target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','class' => 'services-catalog-card__detail-btn','href' => ''.e($detailUrl).'','target' => '_blank']); ?>
                <?php echo e($guide->isVideo() ? ($isEn ? 'Watch guide' : 'Tonton panduan') : ($isEn ? 'Open guide' : 'Buka panduan')); ?>

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
            <?php if($guide->isPdf()): ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','class' => 'services-catalog-card__detail-btn','href' => ''.e(route('user_guides.download', $guide->slug)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => 'services-catalog-card__detail-btn','href' => ''.e(route('user_guides.download', $guide->slug)).'']); ?>
                    <?php echo e($isEn ? 'Download' : 'Unduh'); ?>

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
            <?php else: ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'ghost','class' => 'services-catalog-card__detail-btn','href' => ''.e($guide->videoWatchUrl()).'','target' => '_blank']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'ghost','class' => 'services-catalog-card__detail-btn','href' => ''.e($guide->videoWatchUrl()).'','target' => '_blank']); ?>
                    <?php echo e($isEn ? 'Open YouTube' : 'Buka YouTube'); ?>

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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/public/user_guides/_items.blade.php ENDPATH**/ ?>