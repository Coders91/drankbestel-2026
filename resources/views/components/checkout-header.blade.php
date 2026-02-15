@props([])

<header {{ $attributes->merge(['class' => 'border-b border-gray-100']) }}>
  {{-- Logo + cart --}}
  <div class="container flex items-center justify-between py-2.5 lg:py-5">
    <a class="block" href="{{ home_url('/') }}">
      @svg('resources.images.logos.drankbestel', 'h-5 lg:h-7 w-auto')
    </a>
    <div class="flex items-center gap-3">
      <span class="hidden sm:inline-flex items-center gap-1.5 text-sm text-gray-500">
        @svg('resources.images.icons.lock-01', 'w-4 h-4 text-green-600')
        {{ __('Beveiligde checkout', 'sage') }}
      </span>
      <livewire:header-cart />
    </div>
  </div>
</header>
