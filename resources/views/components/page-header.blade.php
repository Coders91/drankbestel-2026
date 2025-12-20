@props([
  'title' => '',
])

<div {{ $attributes->merge(['class' => '']) }}>
  <h1 class="display-1">{!! $title !!}</h1>
</div>
