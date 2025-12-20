@props([
  'title' => '',
])

<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
  <h3 class="mb-4 text-xl font-headings font-semibold">{{ $title }}</h3>
  <div class="grid p-6 border border-gray-300 rounded-xl">
    {{ $slot }}
  </div>
</div>
