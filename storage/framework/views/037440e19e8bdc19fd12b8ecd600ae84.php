<?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $postTitle = $isEn ? $p->title_en ?? $p->title_id : $p->title_id;
        $postHtml = $isEn ? $p->content_html_en ?? $p->content_html_id : $p->content_html_id;
        $postDesc = filled($postHtml)
            ? $excerpt($postHtml, 130)
            : ($isEn
                ? 'Official announcement from ULT FKIP Unila.'
                : 'Pengumuman resmi dari ULT FKIP Unila.');
        $postImage = filled($p->image_path ?? null)
            ? asset('storage/' . ltrim((string) $p->image_path, '/'))
            : $normalizeImage($extractImage($postHtml));
        $postSearchText = trim(
            implode(
                ' ',
                array_filter([
                    $p->title_id,
                    $p->title_en,
                    $p->slug,
                    $excerpt($p->content_html_id, 220),
                    $excerpt($p->content_html_en, 220),
                ]),
            ),
        );
    ?>
    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'card-item services-catalog-card','dataServicesReveal' => true,'dataRealtimeSearchItem' => true,'dataRealtimeSearchText' => ''.e($postSearchText).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'card-item services-catalog-card','data-services-reveal' => true,'data-realtime-search-item' => true,'data-realtime-search-text' => ''.e($postSearchText).'']); ?>
        <div class="card-cover">
            <?php if($postImage): ?>
                <img class="card-cover__img" src="<?php echo e($postImage); ?>" alt="<?php echo e($postTitle); ?>"
                    loading="lazy">
            <?php else: ?>
                <span class="card-cover__fallback"
                    aria-hidden="true"><?php echo e($isEn ? 'Announcement' : 'Pengumuman'); ?></span>
            <?php endif; ?>
        </div>
        <div class="services-catalog-card__head">
            <span
                class="services-catalog-card__category"><?php echo e($isEn ? 'Announcement' : 'Pengumuman'); ?></span>
            <span
                class="services-catalog-card__index"><?php echo e(optional($p->published_at)->format('d M Y')); ?></span>
        </div>
        <h3 class="services-catalog-card__title">
            <a href="<?php echo e(route('announcements.show', $p)); ?>"><?php echo e($postTitle); ?></a>
        </h3>
        <p class="services-catalog-card__desc"><?php echo e($postDesc); ?></p>
        <div class="services-catalog-card__actions">
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['variant' => 'secondary','class' => 'services-catalog-card__detail-btn','href' => ''.e(route('announcements.show', $p)).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'secondary','class' => 'services-catalog-card__detail-btn','href' => ''.e(route('announcements.show', $p)).'']); ?>
                <?php echo e($isEn ? 'Read' : 'Baca'); ?>

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
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/public/announcements/_items.blade.php ENDPATH**/ ?>