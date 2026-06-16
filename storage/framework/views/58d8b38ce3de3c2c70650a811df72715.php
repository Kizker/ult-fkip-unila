<?php
  $serviceCategories = \App\Models\CmsCategory::query()
      ->where('type', 'service')
      ->whereIn('slug', [
          'akademik-dan-kerja-sama',
          'umum-dan-keuangan',
          'kemahasiswaan-dan-alumni',
          'lainnya',
      ])
      ->orderBy('id')
      ->get();

  $relatedLinks = [
      ['label' => 'FKIP Unila', 'url' => 'https://fkip.unila.ac.id/'],
      ['label' => 'Universitas Lampung', 'url' => 'https://www.unila.ac.id/'],
      ['label' => 'PLT Unila', 'url' => 'https://plt.fkip.unila.ac.id/'],
      ['label' => 'Legalisir Online', 'url' => 'https://akdfkip.blogspot.com/p/legalisir-online.html'],
      ['label' => 'Portal Akademik', 'url' => 'https://akademik.fkip.unila.ac.id/'],
      ['label' => 'Perpustakaan Unila', 'url' => 'https://library.unila.ac.id/'],
  ];

  $bottomLinks = [
      ['label' => __('app.announcements'), 'url' => route('announcements.index')],
      ['label' => __('app.blog'), 'url' => route('blog.index')],
      ['label' => __('app.user_guides'), 'url' => route('user_guides.index')],
      ['label' => __('app.feedback'), 'url' => route('feedback.create')],
      ['label' => __('app.about'), 'url' => route('about')],
  ];
?>

<style>
  @media (max-width: 639px) {
    .page-public-footer .public-footer__bottom {
      gap: 1rem;
    }

    .page-public-footer .public-footer__copyright {
      max-width: 24rem;
      line-height: 1.6;
    }

    .page-public-footer .public-footer__meta {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .55rem .9rem;
      width: 100%;
    }

    .page-public-footer .public-footer__meta .public-footer__link {
      display: block;
      width: 100%;
      padding-top: .15rem;
      padding-bottom: .15rem;
      line-height: 1.45;
    }
  }
</style>

<div class="page-public-footer">
  <footer class="public-footer" aria-label="Footer">
    <div class="public-footer__mesh" aria-hidden="true">
      <span class="public-footer__mesh-blob public-footer__mesh-blob--a"></span>
      <span class="public-footer__mesh-blob public-footer__mesh-blob--d"></span>
      <span class="public-footer__mesh-blob public-footer__mesh-blob--b"></span>
      <span class="public-footer__mesh-blob public-footer__mesh-blob--c"></span>
    </div>

    <div class="public-container public-footer__inner">
      <div class="public-footer__top">
        <div class="public-footer__brand">
          <a href="<?php echo e(route('home')); ?>" class="public-footer__logo">
            <span class="public-footer__logos" style="display:inline-flex;align-items:center;flex-direction:row;flex-wrap:nowrap;gap:.42rem;white-space:nowrap;line-height:0;">
              <img
                src="<?php echo e(asset('icons/unila.png')); ?>"
                alt="Logo Universitas Lampung"
                class="public-footer__logoimg public-footer__logoimg--unila"
                style="display:block;"
                loading="lazy"
                decoding="async"
              />
              <img
                src="<?php echo e(asset('icons/logo.png')); ?>"
                alt="Logo FKIP Unila"
                class="public-footer__logoimg public-footer__logoimg--fkip"
                style="display:block;"
                loading="lazy"
                decoding="async"
              />
            </span>
            <span>ULT FKIP Unila</span>
          </a>
          <div class="public-footer__tagline"><?php echo e(__('app.footer_tagline')); ?></div>
        </div>

        <div class="public-footer__cols" aria-label="Tautan">
          <div class="public-footer__col" style="display:flex;flex-direction:column;align-items:flex-start;padding:1rem 1.1rem;border:1px solid rgba(255,255,255,.12);border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.03));box-shadow:0 18px 40px rgba(44,7,73,.18);backdrop-filter:blur(10px);">
            <div class="public-footer__title" style="display:flex;align-items:center;gap:.55rem;margin-bottom:.65rem;">
              <span style="display:inline-block;width:.6rem;height:.6rem;border-radius:999px;background:linear-gradient(135deg,#f9a8d4,#c4b5fd);box-shadow:0 0 0 4px rgba(255,255,255,.08);"></span>
              <span><?php echo e(__('app.services')); ?></span>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $serviceCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <a class="public-footer__link" href="<?php echo e(route('services.index', ['category' => $category->id])); ?>" style="display:flex;align-items:center;gap:.65rem;width:100%;padding:.65rem .2rem;border-bottom:1px solid rgba(255,255,255,.08);">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.45rem;height:1.45rem;border-radius:999px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.92);font-size:.78rem;line-height:1;">+</span>
                <span><?php echo e(app()->getLocale() === 'en' ? ($category->name_en ?? $category->name_id) : $category->name_id); ?></span>
              </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <a class="public-footer__link" href="<?php echo e(route('services.index')); ?>" style="display:flex;align-items:center;gap:.65rem;width:100%;padding:.65rem .2rem;">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.45rem;height:1.45rem;border-radius:999px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.92);font-size:.78rem;line-height:1;">+</span>
                <span><?php echo e(__('app.services')); ?></span>
              </a>
            <?php endif; ?>
          </div>

          <div class="public-footer__col" style="display:flex;flex-direction:column;align-items:flex-start;padding:1rem 1.1rem;border:1px solid rgba(255,255,255,.12);border-radius:18px;background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.03));box-shadow:0 18px 40px rgba(44,7,73,.18);backdrop-filter:blur(10px);">
            <div class="public-footer__title" style="display:flex;align-items:center;gap:.55rem;margin-bottom:.65rem;">
              <span style="display:inline-block;width:.6rem;height:.6rem;border-radius:999px;background:linear-gradient(135deg,#fde68a,#c4b5fd);box-shadow:0 0 0 4px rgba(255,255,255,.08);"></span>
              <span><?php echo e(__('app.related_links')); ?></span>
            </div>
            <?php $__currentLoopData = $relatedLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <a class="public-footer__link" href="<?php echo e($link['url']); ?>" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;gap:.65rem;width:100%;padding:.65rem .2rem;border-bottom:1px solid rgba(255,255,255,.08);">
                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.45rem;height:1.45rem;border-radius:999px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.92);font-size:.78rem;line-height:1;">↗</span>
                <span><?php echo e($link['label']); ?></span>
              </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      </div>

      <div class="public-footer__bottom">
        <div class="public-footer__copyright">
          &copy; <?php echo e(now()->year); ?> Andricha Dea Mitra. All rights reserved.
        </div>
        <div class="public-footer__meta">
          <?php $__currentLoopData = $bottomLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a class="public-footer__link" href="<?php echo e($link['url']); ?>"><?php echo e($link['label']); ?></a>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
    </div>
  </footer>
</div>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/public/footer.blade.php ENDPATH**/ ?>