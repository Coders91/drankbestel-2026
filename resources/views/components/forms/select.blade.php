@props([
    'name' => '',
    'options' => [],
    'label' => '',
])

<div class="relative flex w-full flex-col gap-1">
  {{ $label }}
  <div class="grid items-center">
    @svg('resources.images.icons.chevron-down', 'col-start-1 row-start-1 order-2 justify-self-end size-5 mr-4
    stroke-gray-400 pointer-events-none')
    <select
      id="{{ $name }}"
      name="{{ $name }}"
      {{ $attributes->merge(['class' => 'w-full min-h-12 appearance-none rounded-sm border border-gray-300 bg-white pl-4 pr-12 py-2 cursor-pointer col-start-1 row-start-1 order-1 truncate']) }}
    >
      @if(!empty($options))
        @foreach ($options as $value => $label)
          <option
            value="{{ $value }}"
            {{ $loop->first ? 'disabled selected ' : '' }}
          >{{ $label }}</option>
        @endforeach
      @else
        {{ $slot }}
      @endif
    </select>
  </div>
</div>
