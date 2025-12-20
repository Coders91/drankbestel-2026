@props([
  'type' => 'button',
  'variant' => '',
  'text' => null,
  'size' => '',
])

@php($class = 'flex items-center justify-center text-center gap-2 min-w-fit rounded-lg transition-colors border border-transparent cursor-pointer')

@php($class .= ' ' . match ($variant) {
  'outline' => '',
  'secondary' => 'border-gray-300 bg-white font-bold hover:bg-gray-50 text-gray-500 shadow-xs',
  default => 'text-center text-white font-semibold font-headings bg-red-600 hover:bg-red-800 transition-colors border-0 shadow-xs',
})

@php($class .= ' ' .  match ($size) {
  'small' => 'px-4 py-2.5',
  'regular' => 'px-5 py-3.5',
  default => 'px-4.5 py-3',
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
