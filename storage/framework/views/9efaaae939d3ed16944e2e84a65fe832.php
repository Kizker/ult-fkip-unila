<?php $__env->startSection('title', __('app.login')); ?>
<?php $__env->startSection('content'); ?>
<?php
  $isEn = app()->getLocale() === 'en';
?>
<div class="page-auth-login">
  <div class="auth-shell auth-shell--single auth-shell--login-single">
    <?php
      $googleEnabled = (bool) (config('services.google.client_id') && config('services.google.client_secret'));
    ?>
    <?php if (isset($component)) { $__componentOriginal53747ceb358d30c0105769f8471417f6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal53747ceb358d30c0105769f8471417f6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.card','data' => ['class' => 'auth-card auth-card--login-single']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'auth-card auth-card--login-single']); ?>
      <div class="auth-card__header auth-card__header--login">
        <h1 class="auth-title"><?php echo e(__('app.login')); ?></h1>
        <p class="auth-subtitle">
          <?php echo e($isEn
              ? 'Sign in to submit services, monitor statuses, and view your request history.'
              : 'Masuk untuk mengajukan layanan, memantau status, dan melihat riwayat pengajuan.'); ?>

        </p>
      </div>

      <form class="auth-form" method="POST" action="<?php echo e(route('login')); ?>">
        <?php echo csrf_field(); ?>
        <?php if (isset($component)) { $__componentOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc2fcfa88dc54fee60e0757a7e0572df1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input','data' => ['name' => 'email','type' => 'email','label' => 'Email','value' => ''.e(old('email')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email','type' => 'email','label' => 'Email','value' => ''.e(old('email')).'','required' => true]); ?>
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
        <div class="auth-password-field">
          <label class="text-sm font-medium" for="password"><?php echo e($isEn ? 'Password' : 'Password'); ?></label>
          <div class="auth-password-control">
            <input
              id="password"
              name="password"
              type="password"
              class="auth-password-input w-full rounded-xl border border-[rgb(var(--c-border))] bg-white/70 px-4 text-sm leading-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[rgb(var(--focus-ring))] dark:bg-zinc-900"
              required
            />
            <button
              type="button"
              class="auth-password-toggle"
              data-auth-action="toggle-password"
              data-target="password"
              aria-pressed="false"
              aria-label="<?php echo e($isEn ? 'Show password' : 'Tampilkan password'); ?>"
            >
              <span class="auth-toolbtn__icon" aria-hidden="true">
                <svg class="auth-icon auth-icon--eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                  <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"></path>
                </svg>
                <svg class="auth-icon auth-icon--eyeoff" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M10.58 10.58A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"></path>
                  <path d="M16.42 16.42C14.98 17.4 13.28 18 12 18 5.5 18 2 12 2 12a18.76 18.76 0 0 1 5.58-6.42"></path>
                  <path d="M9.88 5.12C10.57 4.86 11.28 4.7 12 4.7c6.5 0 10 7.3 10 7.3a18.56 18.56 0 0 1-3.17 4.67"></path>
                  <path d="M3 3l18 18"></path>
                </svg>
              </span>
            </button>
          </div>
        </div>

        <div class="auth-login-meta">
          <label class="auth-check">
            <input type="checkbox" name="remember" class="auth-check__box">
            <span><?php echo e($isEn ? 'Remember me' : 'Ingat saya'); ?></span>
          </label>
          <a class="auth-link auth-link--muted" href="<?php echo e(route('password.request')); ?>">
            <?php echo e($isEn ? 'Forgot password?' : 'Lupa password?'); ?>

          </a>
        </div>

        <div class="auth-actions auth-actions--stack">
          <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','class' => 'w-full justify-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'w-full justify-center']); ?><?php echo e(__('app.login')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>

          <div class="auth-login-divider" aria-hidden="true">
            <span><?php echo e($isEn ? 'or' : 'atau'); ?></span>
          </div>

          <?php if($googleEnabled): ?>
            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['href' => ''.e(route('auth.google.redirect')).'','variant' => 'secondary','class' => 'w-full justify-center']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => ''.e(route('auth.google.redirect')).'','variant' => 'secondary','class' => 'w-full justify-center']); ?>
              <img src="<?php echo e(asset('icons/google.svg')); ?>" alt="" class="h-5 w-5" />
              <span><?php echo e($isEn ? 'Sign in with Google' : 'Masuk dengan Google'); ?></span>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'button','variant' => 'secondary','class' => 'w-full justify-center opacity-60','disabled' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','variant' => 'secondary','class' => 'w-full justify-center opacity-60','disabled' => true]); ?>
              <img src="<?php echo e(asset('icons/google.svg')); ?>" alt="" class="h-5 w-5" />
              <span><?php echo e($isEn ? 'Sign in with Google' : 'Masuk dengan Google'); ?></span>
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
      </form>

      <div class="auth-login-register">
        <span><?php echo e($isEn ? "Don't have an account?" : 'Belum punya akun?'); ?></span>
        <a class="auth-link" href="<?php echo e(route('register')); ?>"><?php echo e($isEn ? 'Register' : 'Daftar'); ?></a>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/auth/login.blade.php ENDPATH**/ ?>