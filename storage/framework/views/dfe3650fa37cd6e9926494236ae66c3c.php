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

        $catName = strtolower($s->category?->name_id ?? '');
        
        $svgDocument = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><g fill="currentColor"><path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5zM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15m.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279a9.77 9.77 0 0 0-6.963-6.963"/></g></svg>';
        $svgAcademic = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><g fill="currentColor"><path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.7 60.7 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337a50 50 0 0 0-9.902 3.912l-.003.002l-.34.18a.75.75 0 0 1-.707 0A51 51 0 0 0 7.5 12.173v-.224a.36.36 0 0 1 .172-.311a55 55 0 0 1 4.653-2.52a.75.75 0 0 0-.65-1.352a56 56 0 0 0-4.78 2.589a1.86 1.86 0 0 0-.859 1.228a50 50 0 0 0-4.634-1.527a.75.75 0 0 1-.231-1.337A60.7 60.7 0 0 1 11.7 2.805"/><path d="M13.06 15.473a48.5 48.5 0 0 1 7.666-3.282q.202 2.122.255 4.284a.75.75 0 0 1-.46.711a48 48 0 0 0-8.105 4.342a.75.75 0 0 1-.832 0a48 48 0 0 0-8.104-4.342a.75.75 0 0 1-.461-.71q.053-2.163.255-4.286q1.382.456 2.726.99v1.27a1.5 1.5 0 0 0-.14 2.508c-.09.38-.222.753-.397 1.11q.678.32 1.346.66a6.7 6.7 0 0 0 .551-1.607a1.5 1.5 0 0 0 .14-2.67v-.645a49 49 0 0 1 3.44 1.667a2.25 2.25 0 0 0 2.12 0"/><path d="M4.462 19.462c.42-.419.753-.89 1-1.395q.68.321 1.347.662a6.7 6.7 0 0 1-1.286 1.794a.75.75 0 0 1-1.06-1.06"/></g></svg>';
        $svgGroup = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><g fill="currentColor"><path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0a3.75 3.75 0 0 1-7.5 0m7.5 3a3 3 0 1 1 6 0a3 3 0 0 1-6 0m-13.5 0a3 3 0 1 1 6 0a3 3 0 0 1-6 0m4.06 5.368A6.75 6.75 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498a.75.75 0 0 1-.372.568A12.7 12.7 0 0 1 12 21.75a12.7 12.7 0 0 1-6.337-1.684a.75.75 0 0 1-.372-.568a6.8 6.8 0 0 1 1.019-4.38" clip-rule="evenodd"/><path d="m5.082 14.254l-.036.055a8.3 8.3 0 0 0-1.271 5.08a9.7 9.7 0 0 1-1.765-.44l-.115-.04a.56.56 0 0 1-.373-.487l-.01-.121Q1.5 18.15 1.5 18a3.75 3.75 0 0 1 3.582-3.746m15.144 5.135a8.3 8.3 0 0 0-1.308-5.135a3.75 3.75 0 0 1 3.57 4.047l-.01.121a.56.56 0 0 1-.373.486l-.115.04q-.851.302-1.764.441"/></g></svg>';
        $svgBuilding = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M4.5 2.25a.75.75 0 0 0 0 1.5v16.5h-.75a.75.75 0 0 0 0 1.5h16.5a.75.75 0 0 0 0-1.5h-.75V3.75a.75.75 0 0 0 0-1.5zM9 6a.75.75 0 0 0 0 1.5h1.5a.75.75 0 0 0 0-1.5zm-.75 3.75A.75.75 0 0 1 9 9h1.5a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75M9 12a.75.75 0 0 0 0 1.5h1.5a.75.75 0 0 0 0-1.5zm3.75-5.25A.75.75 0 0 1 13.5 6H15a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75M13.5 9a.75.75 0 0 0 0 1.5H15A.75.75 0 0 0 15 9zm-.75 3.75a.75.75 0 0 1 .75-.75H15a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75M9 19.5v-2.25a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-.75.75h-4.5A.75.75 0 0 1 9 19.5" clip-rule="evenodd"/></svg>';
        $svgIdentification = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M4.5 3.75a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3V6.75a3 3 0 0 0-3-3zm4.125 3a2.25 2.25 0 1 0 0 4.5a2.25 2.25 0 0 0 0-4.5m-3.873 8.703a4.126 4.126 0 0 1 7.746 0a.75.75 0 0 1-.351.92a7.5 7.5 0 0 1-3.522.877a7.5 7.5 0 0 1-3.522-.877a.75.75 0 0 1-.351-.92M15 8.25a.75.75 0 0 0 0 1.5h3.75a.75.75 0 0 0 0-1.5zM14.25 12a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H15a.75.75 0 0 1-.75-.75m.75 2.25a.75.75 0 0 0 0 1.5h3.75a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/></svg>';
        
        $serviceIcon = $svgDocument;
        if (str_contains($catName, 'akademik')) $serviceIcon = $svgAcademic;
        elseif (str_contains($catName, 'kemahasiswaan')) $serviceIcon = $svgGroup;
        elseif (str_contains($catName, 'umum') || str_contains($catName, 'keuangan')) $serviceIcon = $svgBuilding;
        elseif (str_contains($catName, 'kepegawaian')) $serviceIcon = $svgIdentification;
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
            <span class="services-catalog-card__category flex items-center gap-1.5">
                <span class="services-v2-card__icon text-[1.1rem] opacity-80 flex items-center"><?php echo $serviceIcon; ?></span>
                <?php echo e($serviceCategory ?? '-'); ?>

            </span>
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