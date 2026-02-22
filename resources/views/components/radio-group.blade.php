@props([
    'id' => '',
    'name' => '',
    'value' => '',
    'disabled' => false,
    'text' => false,
])

<label for="{{ $id }}"
  {{ $attributes->merge(['class' => 'relative
  flex items-center justify-start gap-2
  px-4 py-2
  border border-gray-200 has-checked:bg-red-600 has-checked:border-red-600
  text-gray-900 has-checked:text-white leading-8 rounded-lg shadow-md transition-colors
  cursor-pointer peer-disabled:cursor-not-allowed']) }}
>
  <input
    id="{{ $id }}"
    name="{{ $name }}"
    value="{{ $value }}"
    type="radio"
    class="shrink-0 peer before:content[''] relative size-5 appearance-none rounded-full border border-gray-300 bg-white
    before:invisible before:absolute before:left-1/2 before:top-1/2 before:size-2 before:-translate-x-1/2 before:-translate-y-1/2 before:rounded-full before:bg-white
    checked:border-white checked:bg-red-600 checked:before:visible focus:outline-2
    focus:outline-offset-2 focus:outline-red-600 checked:focus:outline-red-600  cursor-pointer disabled:cursor-not-allowed hover:text-red-600 transition-colors"
  >
  @if($text)
    {!! $text !!}
  @else
    <span
      class="font-bold flex justify-between items-center gap-2 w-full">{{ $slot }}</span>
  @endif
</label>
