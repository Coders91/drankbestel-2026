@props([
  'type' => 'button',
  'variant' => '',
  'text' => null,
  'size' => '',
])

@php($class = 'flex items-center justify-center text-center gap-2 min-w-fit rounded-lg transition-colors')

@php($class .= ' ' . match ($variant) {
  'outline' => 'border border-gray-300 bg-white disabled:bg-gray-100 font-bold text-gray-600 shadow-xs',
  'secondary' => 'text-white font-semibold bg-gray-900 transition-colors border-0 shadow-xs',
  default => 'text-white font-semibold bg-red-600 disabled:opacity-50 not-disabled:hover:bg-red-800 transition-colors border-0 shadow-xs',
})

@php($class .= ' ' .  match ($size) {
  'small' => 'px-4 py-2',
  'regular' => 'px-6 py-4 text-lg',
  default => 'px-4 py-3',
})

@if ($attributes->has('href'))
  <a {{ $attributes->merge(['class' => "{$class}"]) }}>
    {!! $text ?? $slot !!}
  </a>
@else
  <button {{ $attributes->merge(['class' => "{$class}", 'type' => "{$type}"]) }}>
    {!! $text ?? $slot !!}
  </button>
@endif
