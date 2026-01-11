@props([
  'title' => '',
  'description' => ''
])

<section {{ $attributes->merge(['class' => '']) }}>
  <h1 class="display-1 mb-4">{!! $title !!}</h1>
  @if($description)
    <p class="text-gray-700 max-w-2xl">{{ $description }}</p>
  @endif
</section>
