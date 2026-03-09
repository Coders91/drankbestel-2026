@props([
  'usps' => [],
])

<div {{ $attributes->merge(['class' => 'py-1.5']) }}>
  {{-- Desktop: static horizontal list --}}
  <div class="hidden sm:block container mx-auto">
    <ul class="flex gap-10">
      @foreach ($usps as $usp)
        <li class="flex items-center gap-2">
          @svg('resources.images.icons.check', 'size-5 stroke-green-600')
          <span>{{ $usp }}</span>
        </li>
      @endforeach
    </ul>
  </div>

  {{-- Mobile: smooth looping marquee --}}
  <div class="sm:hidden overflow-hidden" aria-hidden="true">
    <ul class="flex animate-marquee">
      @foreach (array_merge($usps, $usps) as $usp)
        <li class="flex items-center gap-2 shrink-0 px-8">
          @svg('resources.images.icons.check', 'size-5 stroke-green-600')
          <span class="whitespace-nowrap">{{ $usp }}</span>
        </li>
      @endforeach
    </ul>
  </div>
</div>
