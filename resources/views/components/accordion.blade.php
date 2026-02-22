@props([
  'id' => substr(Str::uuid()->toString(), 0, 6),
  'title' => null,
  'breakpoint' => false,    // false | sm | md | lg | xl
  'variant' => false,       // false | tip | card
  'isOpen' => false,
])
@php($iconClass = 'shrink-0 w-6 h-6 transition-transform duration-200 stroke-gray-400')

@php($iconClass .= ' ' . match ($breakpoint) {
  'sm' => 'sm:hidden',
  'md' => 'md:hidden',
  'lg' => 'lg:hidden',
  'xl' => 'xl:hidden',
  default => '',
})

@php($iconClass .=  $isOpen ? ' rotate-180' : '')

@php($class = match($breakpoint) {
  'sm' => 'max-sm:not-last:pb-6 max-sm:not-last:border-b max-sm:not-last:border-gray-200 sm:cursor-text sm:select-text',
  'md' => 'max-md:not-last:pb-6 max-md:not-last:border-b max-md:not-last:border-gray-200 md:cursor-text md:select-text',
  'lg' => 'max-lg:not-last:pb-6 max-lg:not-last:border-b max-lg:not-last:border-gray-200 lg:cursor-text lg:select-text',
  'xl' => 'max-xl:not-last:pb-6 max-xl:not-last:border-b max-xl:not-last:border-gray-200 xl:cursor-text xl:select-text',
  default => 'not-last:pb-6 not-last:border-b not-last:border-gray-200',
})

@php($toggleClass = match($breakpoint) {
  'sm' => 'sm:cursor-default',
  'md' => 'md:cursor-default',
  'lg' => 'lg:cursor-default',
  'xl' => 'xl:cursor-default',
  default => '',
})

@php($titleClass = match($breakpoint) {
  'sm' => 'sm:cursor-text sm:select-text',
  'md' => 'md:cursor-text md:select-text',
  'lg' => 'lg:cursor-text lg:select-text',
  'xl' => 'xl:cursor-text xl:select-text',
  default => '',
})

<div {{ $attributes->merge(['class' => "{$class}"]) }} x-data="Accordion(@js($breakpoint), @js($isOpen), '{{ $id }}')">
  @if($title)
    <button
      class="flex items-start justify-between gap-4 w-full text-left {{ $toggleClass}}"
      type="button"
      aria-controls="{{ $id }}"
      @click="isAccordionActive ? activeAccordion = (activeAccordion === accordionId ? '' : accordionId) : null"
      :aria-expanded="isAccordionActive ? (activeAccordion === accordionId).toString() : 'true'"
    >
      <span {{ $title->attributes->class(["block w-fit {$titleClass}"]) }}>
        {{ $title }}
      </span>
      @svg('resources.images.icons.chevron-up', $iconClass, [':class' => "{ 'rotate-180': activeAccordion !==
      accordionId }"])
    </button>
  @endif
  <div data-accordion-content
       class="mr-6"
       style="{{ $isOpen ? 'display:block' : 'display:none' }}"
       id="{{ $id }}"
       x-show="!isAccordionActive || activeAccordion === accordionId"
       x-collapse.duration.200ms
  >
    {{ $slot }}
  </div>
</div>

@pushOnce('scripts')
  <script>
    function Accordion(destroyBreakpoint, isOpen, accordionId) {
      const bps = (() => {
        const s = getComputedStyle(document.documentElement);
        const out = {};
        ["sm", "md", "lg", "xl", "2xl"].forEach(k =>
          out[k] = parseInt(s.getPropertyValue(`--breakpoint-${k}`), 10)
        );
        return out;
      })();

      return {
        accordionId: accordionId,
        activeAccordion: isOpen ? accordionId : "",
        isAccordionActive: true,
        init() {
          this.checkWidth();
          window.addEventListener("resize", () => this.checkWidth());
          window.addEventListener("accordion-open", (e) => {
            if (e.detail?.id === this.accordionId) {
              this.activeAccordion = this.accordionId;
              this.$nextTick(() => this.$el.scrollIntoView({ behavior: "smooth", block: "start" }));
            }
          });
        },
        checkWidth() {
          const v = bps[destroyBreakpoint];
          this.isAccordionActive = v ? window.innerWidth < v : true;
        }
      };
    }
  </script>
@endPushOnce
