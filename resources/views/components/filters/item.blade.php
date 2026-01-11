@props([
  'count' => '',
])

<span {{ $attributes->merge(['class' => 'flex items-center gap-2 w-full']) }}>
  {{ $slot }}
  @if($count)
    <span class="text-sm text-gray-800/80">
      ({{ $count }})
    </span>
  @endif
</span>
