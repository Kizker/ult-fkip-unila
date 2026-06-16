<?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $serviceSearchText = trim(
            implode(
                ' ',
                array_filter([
                    $s->title_id,
                    $s->title_en,
                    $s->slug,
                    $s->summary_id,
                    $s->summary_en,
                    $s->category?->name_id,
                    $s->category?->name_en,
                ]),
            ),
        );
        $serviceTitle = $isEn ? $s->title_en ?? $s->title_id : $s->title_id;
        $serviceSummary = $isEn ? $s->summary_en ?? $s->summary_id : $s->summary_id;
        $serviceSummary = filled($serviceSummary)
            ? $serviceSummary
            : ($isEn
                ? 'Digital service available for online submission.'
                : 'Layanan digital yang dapat diajukan secara online.');
        $serviceCategory = $isEn
            ? $s->category?->name_en ?? $s->category?->name_id
            : $s->category?->name_id;
    ?>
    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'services-catalog-card','dataRealtimeSearchItem' => true,'dataRealtimeSearchText' => ''.e($serviceSearchText).'','dataServicesReveal' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'services-catalog-card','data-realtime-search-item' => true,'data-realtime-search-text' => ''.e($serviceSearchText).'','data-services-reveal' => true]); ?>
        <div class="services-catalog-card__head">
            <span class="services-catalog-card__category"><?php echo e($serviceCategory ?? '-'); ?></span>
            <span
                class="services-catalog-card__index"><?php echo e(str_pad((string) (($loopOffset ?? 0) + $loop->iteration), 2, '0', STR_PAD_LEFT)); ?></span>
        </div>
        <h3 class="services-catalog-card__title">
            <a href="<?php echo e(route('services.show', $s)); ?>"><?php echo e($serviceTitle); ?></a>
        </h3>
        <p class="services-catalog-card__desc"><?php echo e($serviceSummary); ?></p>
        <div class="services-catalog-card__actions">
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','class' => 'services-catalog-card__detail-btn','href' => ''.e(route('services.show', $s)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','class' => 'services-catalog-card__detail-btn','href' => ''.e(route('services.show', $s)).'']); ?><?php echo e($isEn ? 'Details' : 'Detail'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            <?php if(auth()->guard()->check()): ?>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => 'services-catalog-card__apply-btn','href' => ''.e(route('student.requests.create', $s)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'services-catalog-card__apply-btn','href' => ''.e(route('student.requests.create', $s)).'']); ?><?php echo e($isEn ? 'Apply' : 'Ajukan'); ?> <?php echo $__env->renderComponent(); ?>
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
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/public/services/_items.blade.php ENDPATH**/ ?>