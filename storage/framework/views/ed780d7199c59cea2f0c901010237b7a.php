<div
  x-data
  class="fixed bottom-4 right-4 z-50 space-y-2"
>
  <template x-for="t in $store.toast.toasts" :key="t.id">
    <div class="w-80 rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] shadow-lg p-4">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="text-sm font-semibold" x-text="t.title || 'Info'"></div>
          <div class="text-sm text-muted mt-1" x-text="t.message"></div>
        </div>
        <button class="text-muted hover:opacity-80" @click="$store.toast.dismiss(t.id)">&times;</button>
      </div>
    </div>
  </template>
</div>
<?php /**PATH C:\laragon\www\ult-fkip-unila\resources\views/components/toast.blade.php ENDPATH**/ ?>