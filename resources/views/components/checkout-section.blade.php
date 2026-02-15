@props([
  'titleClass' => 'mb-4',
])

<div>
  <h3 class="text-xl font-semibold {{ $titleClass }}" {!! $attributes->only(['x-text']) !!}>{{ $title }}</h3>

  @isset($header)
    {{ $header }}
  @endisset

  <div class="grid *:min-w-0 p-4 md:p-6 border border-gray-300 rounded-xl bg-white">
    {{ $slot }}
  </div>
</div>
