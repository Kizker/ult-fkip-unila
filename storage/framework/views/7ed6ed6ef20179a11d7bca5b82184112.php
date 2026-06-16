<?php if($paginator->hasPages()): ?>
    <?php
        $isEn = app()->getLocale() === 'en';
        $prevLabel = $isEn ? 'Previous' : 'Sebelumnya';
        $nextLabel = $isEn ? 'Next' : 'Berikutnya';
        $summaryTemplate = $isEn
            ? 'Showing :from-:to of :total items'
            : 'Menampilkan :from-:to dari :total data';

        $from = (int) ($paginator->firstItem() ?? 0);
        $to = (int) ($paginator->lastItem() ?? 0);
        $total = method_exists($paginator, 'total') ? (int) $paginator->total() : (int) $paginator->count();
        $summary = strtr($summaryTemplate, [
            ':from' => (string) $from,
            ':to' => (string) $to,
            ':total' => (string) $total,
        ]);
    ?>

    <nav class="public-pagination" role="navigation" aria-label="<?php echo e($isEn ? 'Pagination Navigation' : 'Navigasi halaman'); ?>">
        <div class="public-pagination__summary"><?php echo e($summary); ?></div>

        <ul class="public-pagination__list">
            <?php if($paginator->onFirstPage()): ?>
                <li class="public-pagination__item public-pagination__item--disabled" aria-disabled="true" aria-label="<?php echo e($prevLabel); ?>">
                    <span class="public-pagination__link"><?php echo e($prevLabel); ?></span>
                </li>
            <?php else: ?>
                <li class="public-pagination__item">
                    <a class="public-pagination__link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="<?php echo e($prevLabel); ?>"><?php echo e($prevLabel); ?></a>
                </li>
            <?php endif; ?>

            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_string($element)): ?>
                    <li class="public-pagination__item public-pagination__item--dots" aria-disabled="true">
                        <span class="public-pagination__link"><?php echo e($element); ?></span>
                    </li>
                <?php endif; ?>

                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="public-pagination__item public-pagination__item--active" aria-current="page">
                                <span class="public-pagination__link"><?php echo e($page); ?></span>
                            </li>
                        <?php else: ?>
                            <li class="public-pagination__item">
                                <a class="public-pagination__link" href="<?php echo e($url); ?>" aria-label="<?php echo e($isEn ? 'Page ' . $page : 'Halaman ' . $page); ?>"><?php echo e($page); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($paginator->hasMorePages()): ?>
                <li class="public-pagination__item">
                    <a class="public-pagination__link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="<?php echo e($nextLabel); ?>"><?php echo e($nextLabel); ?></a>
                </li>
            <?php else: ?>
                <li class="public-pagination__item public-pagination__item--disabled" aria-disabled="true" aria-label="<?php echo e($nextLabel); ?>">
                    <span class="public-pagination__link"><?php echo e($nextLabel); ?></span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/public/pagination.blade.php ENDPATH**/ ?>