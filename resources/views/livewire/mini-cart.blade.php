@php
  /** @var App\View\Models\Product $lastAddedProduct */
@endphp

<div>
  @if ($showToast && $lastAddedProduct)
    <div
      x-data="{ show: true }"
      x-init="setTimeout(() => { show = false; $wire.hideToast(); }, 5000)"
      x-show="show"
      x-collapse
      class="fixed top-10 inset-x-4 sm:inset-x-auto sm:right-6 sm:left-auto z-50 sm:w-full sm:max-w-lg pointer-events-none"
    >
      <div x-show="show" x-transition class="bg-white rounded-lg ring-1 ring-gray-400 p-4 relative pointer-events-auto">
        <div class="flex items-start gap-3">

          @svg('resources.images.icons.check', 'size-6 stroke-green-600')

          <div class="flex-1 lg:flex lg:items-start min-w-0 pr-6">
            <p class="font-semibold text-base text-gray-900">
              {!! $lastAddedProduct->title !!} toegevoegd aan winkelwagen.
            </p>
          </div>

          <button
            type="button"
            x-on:click="show = false; $wire.hideToast()"
            class="absolute right-3 top-3 p-2"
          >
            @svg('resources.images.icons.x-close', 'size-5')
          </button>

        </div>
      </div>
    </div>
  @endif
</div>
