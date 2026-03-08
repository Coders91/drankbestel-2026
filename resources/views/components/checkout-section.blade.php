@props([
  'titleClass' => 'mb-4',
])

<div>
  <h3 class="text-xl font-semibold {{ $titleClass }}" {!! $attributes->only(['x-text']) !!}>{{ $title }}</h3>

  @isset($header)
    {{ $header }}
  @endisset

  <div class="grid *:min-w-0 md:p-4 md:p-6 md:border md:border-gray-300 md:rounded-xl bg-white">
    {{ $slot }}
  </div>
</div>
