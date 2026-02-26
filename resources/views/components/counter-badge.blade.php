@props([
  'color' => 'bg-red-600 text-white',
])

<span
  {{ $attributes->merge(['class' => "absolute -top-0.5 -right-0.5 lg:-top-1 lg:-right-1 {$color} text-xs font-headings font-semibold rounded-full size-4.5 lg:size-5 flex items-center justify-center px-1"]) }}
>{{ $slot }}</span>
