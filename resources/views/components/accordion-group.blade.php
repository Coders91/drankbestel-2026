@props([
  'header' => '',
  'breakpoint' => false,
  'firstOpen' => false,
  'multiExpand' => true,
])

@php($class = match($breakpoint) {
  'sm' => 'max-sm:flex max-sm:flex-col max-sm:gap-6',
  'md' => 'max-md:flex max-md:flex-col max-md:gap-6',
  'lg' => 'max-lg:flex max-lg:flex-col max-lg:gap-6',
  'xl' => 'max-xl:flex max-xl:flex-col max-xl:gap-6',
  false => 'flex flex-col gap-6',
})

<div {{ $attributes->merge(['class' => "{$class}"]) }}>
  @if($header)
    <div {{ $header->attributes->class(['flow']) }}>
      {!! $header !!}
    </div>
  @endif
  {{ $slot }}
</div>
