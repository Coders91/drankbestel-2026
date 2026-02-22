@props([
  'type' => null,
  'message' => null,
])

@php($class = match ($type) {
  'success' => 'text-green-800 bg-green-50 border-green-200',
  'caution' => 'text-yellow-800 bg-yellow-50 border-yellow-200',
  'warning' => 'text-red-800 bg-red-50 border-red-200',
  default => 'text-gray-800 bg-gray-50 border-gray-200',
})

<div {{ $attributes->merge(['class' => "p-4 border rounded-lg {$class}"]) }}>
  {!! $message ?? $slot !!}
</div>
