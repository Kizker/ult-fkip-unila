@props([
  'showValidation' => true,
  'showValidationList' => false,
])

@php
  $titleSuccess = trans()->has('app.success') ? __('app.success') : 'Berhasil';
  $titleInfo = trans()->has('app.info') ? __('app.info') : 'Info';
  $titleWarning = trans()->has('app.warning') ? __('app.warning') : 'Perhatian';
  $titleError = trans()->has('app.error') ? __('app.error') : 'Gagal';

  $validationTitle = trans()->has('app.validation_error_title') ? __('app.validation_error_title') : 'Periksa kembali input';
  $validationHint = trans()->has('app.validation_error_hint')
    ? __('app.validation_error_hint')
    : 'Ada beberapa input yang perlu diperbaiki. Silakan cek kolom yang ditandai.';

  $flashes = [
    ['key' => 'success', 'type' => 'success', 'title' => $titleSuccess],
    ['key' => 'status', 'type' => 'success', 'title' => $titleSuccess],
    ['key' => 'info', 'type' => 'info', 'title' => $titleInfo],
    ['key' => 'warning', 'type' => 'warning', 'title' => $titleWarning],
    ['key' => 'error', 'type' => 'danger', 'title' => $titleError],
  ];
@endphp

@foreach($flashes as $f)
  @if (session()->has($f['key']))
    @php
      $message = session($f['key']);

      if ($f['key'] === 'status' && $message === 'verification-link-sent') {
        $message = trans()->has('app.verification_link_sent')
          ? __('app.verification_link_sent')
          : 'Link verifikasi baru telah dikirim.';
      }
    @endphp

    <x-alert :type="$f['type']" :title="$f['title']" dismissible>
      {{ $message }}
    </x-alert>
  @endif
@endforeach

@if($showValidation && $errors->any())
  <x-alert type="danger" :title="$validationTitle" dismissible>
    <div class="space-y-2">
      <div>{{ $validationHint }}</div>

      @php
        $messages = $errors->getMessages();
        $flatCount = count($errors->all());
        $max = 8;
        $shown = 0;
      @endphp

      @if($showValidationList || $flatCount <= $max)
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach($messages as $field => $errs)
            @foreach($errs as $e)
              @php $shown++; @endphp
              <li>
                @if(is_string($field) && $field !== '')
                  <span class="as-mono text-xs text-muted">{{ $field }}</span> — {{ $e }}
                @else
                  {{ $e }}
                @endif
              </li>
            @endforeach
          @endforeach
        </ul>
      @else
        <ul class="list-disc pl-5 space-y-0.5">
          @foreach($messages as $field => $errs)
            @foreach($errs as $e)
              @php $shown++; @endphp
              @if($shown <= $max)
                <li>
                  <span class="as-mono text-xs text-muted">{{ $field }}</span> — {{ $e }}
                </li>
              @endif
            @endforeach
          @endforeach
        </ul>
        <div class="text-xs text-muted">Menampilkan {{ min($max, $flatCount) }} dari {{ $flatCount }} pesan error.</div>
      @endif
    </div>
  </x-alert>
@endif
