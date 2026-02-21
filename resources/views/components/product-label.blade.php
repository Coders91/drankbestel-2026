@props([
  'color' => 'cyan',
])

@php
  $colors = [
    'cyan' => 'bg-cyan-600 text-white',
    'amber' => 'bg-amber-600 text-white',
  ];
@endphp

<span {{ $attributes->class([
  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
  $colors[$color] ?? $colors['cyan'],
]) }}>
  {{ $slot }}
</span>
