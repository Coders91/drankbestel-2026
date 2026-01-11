@props([
    'name'        => 'slide-out',
    'header'      => null,
    'position'    => 'right',       // left | right | top | bottom
    'maxWidth'    => 'max-w-md',
    'closeIcon' => get_svg('resources.images.icons.x')
])

@php
  $positionClasses = [
      'top'    => 'inset-x-0 top-0 h-auto max-h-full',
      'bottom' => 'inset-x-0 bottom-0 h-auto max-h-full',
      'left'   => 'inset-y-0 left-0 w-full',
      'right'  => 'inset-y-0 right-0 w-full',
  ];

  $translateClasses = [
      'top'    => '-translate-y-full',
      'bottom' => 'translate-y-full',
      'left'   => '-translate-x-full',
      'right'  => 'translate-x-full',
  ];
@endphp

{{-- Root --}}
<div
  x-data="OffCanvas('{{ $name }}')"
  x-show="open"
  x-trap.noscroll="open"
  x-on:keydown.escape="close"
  x-cloak
  class="fixed inset-0 z-[100]"
>

  {{-- Backdrop --}}
  <div
    x-show="open"
    x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="absolute inset-0 z-[90] bg-gray-900/20 backdrop-blur-[2.5px]"
    x-on:click="close"
  ></div>

  {{-- Panel --}}
  <div
    x-show="open"
    x-transition:enter="transition-transform ease-out duration-300"
    x-transition:enter-start="{{ $translateClasses[$position] }}"
    x-transition:enter-end="translate-x-0 translate-y-0"
    x-transition:leave="transition-transform ease-in duration-200"
    x-transition:leave-start="translate-x-0 translate-y-0"
    x-transition:leave-end="{{ $translateClasses[$position] }}"
    class="absolute z-[100] h-full bg-white shadow-xl flex flex-col {{ $positionClasses[$position] }} {{ $maxWidth }}"
  >

    {{-- Main slot --}}
    <div {{ $attributes->merge(['class' => 'flex-1 min-h-0 overflow-y-auto']) }}>

      {{-- Header --}}
      @if($header)
        <header {{ $header->attributes->class(['flex justify-between']) }}>
          {{ $header }}
          <button
            type="button"
            x-on:click="close"
          >
            {{ $closeIcon }}
          </button>
        </header>
      @else
        {{-- Close button --}}
        <div class="flex justify-end">
          <button
            type="button"
            x-on:click="close"
          >
            {{ $closeIcon }}
          </button>
        </div>
      @endif

      {{ $slot }}
    </div>
  </div>
</div>

@pushonce('scripts')
  <script>
    function OffCanvas(name) {
      return {
        open: false,
        init() {
          window.addEventListener(`open-${name}`, () => this.open = true);
          window.addEventListener(`close-${name}`, () => this.open = false);
        },
        close() {
          setTimeout(() => {
            this.resetMenu();
          }, 100);
          this.open = false;
        }
      };
    }
  </script>
@endpushonce
