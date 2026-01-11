@props([
  'header' => null,
  'highlight' => null,
  'title' => null,
  'description' => null,
  'centered' => false,
  'container' => true,
  'theme' => 'white',
])

@php($class = match($theme) {
  'lightgray' => 'bg-gray-100',
  'gray' => 'bg-gray-50',
  default => 'bg-white'
})

<section {{ $attributes->merge(['class' => "py-16 lg:py-24 {$class}"]) }}>
  <div
  @class([
      'grid gap-6 lg:gap-10',
      'container' => $container,
    ])
  >
    @if(!$header)
    <x-section-header :highlight="$highlight" title="{{ $title }}" description="{!! $description !!}"
                      :centered="$centered" />
    @else
      <div {{ $header->attributes }}>
      {{ $header }}
      </div>
    @endif
  {{ $slot }}
  </div>
</section>
