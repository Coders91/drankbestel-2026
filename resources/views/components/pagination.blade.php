@if ($pagi->hasPages())
  @php $pagi->onEachSide(1); @endphp
  <div
    class="flex items-center justify-between pt-2 lg:pt-10 pb-6 lg:pb-12 mt-12 lg:mt-16 border-t border-gray-200 text-sm">
    {{-- Prev --}}
    @if ($pagi->onFirstPage())
      <span class="flex items-center gap-2 p-2 opacity-50">
        <span class="max-lg:hidden">Vorige</span>
      </span>
    @else
      <a class="group flex items-center gap-2 p-2" href="{{ $pagi->previousPageUrl() }}" wire:navigate>
        <span class="max-lg:hidden group-hover:text-gray-700">Vorige</span>
      </a>
    @endif

    {{-- Numbers --}}
    <div class="max-lg:hidden flex gap-0.5">
      @foreach ($pagi->elements() as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
          <span class="text-base flex items-center justify-center size-10 p-3 text-gray-400 cursor-default">
            {{ $element }}
          </span>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $pagi->currentPage())
              <span
                class="text-base flex items-center justify-center size-10 p-3 rounded-full bg-gray-50 text-primary-600">
                {{ $page }}
              </span>
            @else
              <a href="{{ $url }}" wire:navigate class="text-base flex items-center justify-center size-10 p-3 hover:text-gray-700">
                {{ $page }}
              </a>
            @endif
          @endforeach
        @endif
      @endforeach
    </div>

    {{-- Mobile summary --}}
    <div class="lg:hidden">
      Pagina {{ $pagi->currentPage() }} van {{ $pagi->lastPage() }}
    </div>

    {{-- Next --}}
    @if ($pagi->hasMorePages())
      <a class="group flex items-center gap-2 p-2" href="{{ $pagi->nextPageUrl() }}">
        <span class="max-lg:hidden group-hover:text-gray-700">Volgende</span>
      </a>
    @else
      <span class="flex items-center gap-2 p-2 opacity-50">
        <span class="max-lg:hidden">Volgende</span>
      </span>
    @endif
  </div>
@endif
