@props([
  'usps' => [],
])

<div
  {{ $attributes->merge(['class' => 'py-1.5']) }}
  x-data="uspSlider({{ json_encode(array_values($usps)) }})"
  x-init="start()"
>
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

  {{-- Mobile: sliding one-by-one --}}
  <div class="sm:hidden overflow-hidden relative h-7">
    <template x-for="(usp, index) in usps" :key="index">
      <div
        class="absolute inset-0 flex items-center justify-center gap-2 transition-transform duration-700 ease-in-out"
        :style="getStyle(index)"
      >
        @svg('resources.images.icons.check', 'size-5 stroke-green-600 shrink-0')
        <span class="whitespace-nowrap" x-text="usp"></span>
      </div>
    </template>
  </div>
</div>

<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('uspSlider', (usps) => ({
      usps: usps,
      current: 0,
      previous: 0,
      timer: null,

      start() {
        this.timer = setInterval(() => {
          this.previous = this.current;
          this.current = (this.current + 1) % this.usps.length;
        }, 4500);
      },

      getStyle(index) {
        const diff = index - this.current;
        const total = this.usps.length;

        // Normalize: previous slides go left, next slides wait on right
        let offset = diff;
        if (diff > total / 2) offset = diff - total;
        if (diff < -total / 2) offset = diff + total;

        // Only animate current and outgoing slides, others jump instantly
        const shouldAnimate = index === this.current || index === this.previous;
        const transition = shouldAnimate ? '' : 'transition-duration: 0ms;';

        return `transform: translateX(${offset * 100}%); ${transition}`;
      },

      destroy() {
        if (this.timer) clearInterval(this.timer);
      }
    }));
  });
</script>
