@props([
    'id' => '',
    'name' => '',
    'value' => null,
    'disabled' => false,
    'label' => false,
    'checked' => false,
])

<div class="relative flex items-center justify-start gap-2.5">
  <input
    id="{{ $id }}"
    type="radio"
    name="{{ $name }}"
    value="{{ $value }}"
    {{ $attributes->merge([
        'class' => "flex-shrink-0 peer before:content[''] relative size-5 appearance-none rounded-full border bg-white
        before:invisible before:absolute before:left-1/2 before:top-1/2 before:size-2 before:-translate-x-1/2 before:-translate-y-1/2 before:rounded-full before:bg-gray-50
        checked:border-red-600 checked:bg-red-600 checked:before:visible focus:outline-0
        focus:outline-offset-2 disabled:cursor-not-allowed"
    ]) }}
    @if($attributes->has('wire:model') || $attributes->has('wire:model.live'))
      @checked(old($name, $attributes->wire('model')->value()) == ($value ?? ''))
    @elseif($checked)
      checked="checked"
    @endif
  >

  @if($label)
    {{ $label }}
  @else
    <label for="{{ $id }}" class="text-base cursor-pointer peer-disabled:cursor-not-allowed flex items-center gap-2 w-full">
      {{ $slot }}
    </label>
  @endif
</div>
