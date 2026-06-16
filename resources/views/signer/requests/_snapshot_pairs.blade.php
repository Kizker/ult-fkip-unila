@php
  $value = is_array($value ?? null) ? $value : [];
  $snapshotLabel = $snapshotLabel ?? static fn ($raw) => (string) $raw;
  $snapshotScalar = $snapshotScalar ?? static fn ($raw) => is_scalar($raw) ? (string) $raw : '[data]';
  $snapshotIsAssoc = $snapshotIsAssoc ?? static fn (array $arr) => array_keys($arr) !== range(0, count($arr) - 1);
  $isAssoc = $snapshotIsAssoc($value);
@endphp

@if(empty($value))
  <div class="ss-snapshot-item__value">-</div>
@else
  <div class="ars-kvgrid mt-2">
    @foreach($value as $itemKey => $itemValue)
      @php
        $itemLabel = $isAssoc ? $snapshotLabel($itemKey) : 'Item '.((int) $itemKey + 1);
        $itemDisplay = '';
        if (is_array($itemValue)) {
          $count = count($itemValue);
          $itemDisplay = $count.' '.($snapshotIsAssoc($itemValue) ? 'properti' : 'item');
        } else {
          $itemDisplay = $snapshotScalar($itemValue);
        }
      @endphp
      <div class="ars-kv">
        <div class="ars-kv__label">{{ $itemLabel }}</div>
        <div class="ars-kv__value">{{ $itemDisplay }}</div>
      </div>
    @endforeach
  </div>
@endif
