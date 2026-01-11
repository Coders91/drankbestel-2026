@props([
  'title' => '',
])
<div {{ $attributes->merge(['class' => 'w-full']) }}>
  @if($title)
    <div class="px-4 py-3 font-heading font-medium text-gray-900">{{ $title }}</div>
  @endif
  <div class="px-4 py-3 hover:bg-gray-50 transition-colors">
    {{ $slot }}
  </div>
</div>
