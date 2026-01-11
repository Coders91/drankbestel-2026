@props([
  'position' => 1,
  'product' => null,
  'reason' => '',
  'criteria' => '',
  'prosCons' => [],
])

<div {{ $attributes->merge(['class' => 'list-item bg-white border border-gray-200 rounded-xl p-6 lg:p-8']) }}>
  <div class="flex flex-col lg:flex-row gap-6">
    {{-- Position Badge --}}
    <div class="flex-shrink-0">
      <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white font-bold text-xl">
        {{ $position }}
      </span>
    </div>

    {{-- Product Info --}}
    @if($product)
      <div class="flex flex-col sm:flex-row gap-6 flex-1">
        {{-- Product Image --}}
        <a href="{{ $product->url }}" class="flex-shrink-0 group">
          <div class="w-32 h-32 bg-gray-50 rounded-lg overflow-hidden">
            @if($product->imageId)
              {!! wp_get_attachment_image($product->imageId, 'medium', false, ['class' => 'w-full h-full object-contain group-hover:scale-105 transition-transform']) !!}
            @else
              <div class="w-full h-full flex items-center justify-center text-gray-300">
                @svg('icons.image', 'w-8 h-8')
              </div>
            @endif
          </div>
        </a>

        {{-- Product Details --}}
        <div class="flex-1">
          <a href="{{ $product->url }}" class="hover:text-primary transition-colors">
            <h3 class="text-xl font-bold mb-2">{{ $product->title }}</h3>
          </a>

          @if($criteria)
            <p class="text-sm text-primary font-medium mb-2">{{ $criteria }}</p>
          @endif

          @if($reason)
            <div class="prose prose-sm text-gray-600 mb-4">
              {!! $reason !!}
            </div>
          @endif

          {{-- Pros & Cons --}}
          @if(count($prosCons) > 0)
            <div class="grid sm:grid-cols-2 gap-4 mt-4">
              @php
                $pros = array_filter($prosCons, fn($item) => ($item['type'] ?? '') === 'pro');
                $cons = array_filter($prosCons, fn($item) => ($item['type'] ?? '') === 'con');
              @endphp

              @if(count($pros) > 0)
                <div>
                  <h4 class="text-sm font-semibold text-green-700 mb-2">Voordelen</h4>
                  <ul class="space-y-1">
                    @foreach($pros as $item)
                      <li class="flex items-start gap-2 text-sm text-gray-600">
                        @svg('icons.check-circle', 'w-4 h-4 text-green-600 flex-shrink-0 mt-0.5')
                        {{ $item['text'] ?? '' }}
                      </li>
                    @endforeach
                  </ul>
                </div>
              @endif

              @if(count($cons) > 0)
                <div>
                  <h4 class="text-sm font-semibold text-red-700 mb-2">Nadelen</h4>
                  <ul class="space-y-1">
                    @foreach($cons as $item)
                      <li class="flex items-start gap-2 text-sm text-gray-600">
                        @svg('icons.x-circle', 'w-4 h-4 text-red-600 flex-shrink-0 mt-0.5')
                        {{ $item['text'] ?? '' }}
                      </li>
                    @endforeach
                  </ul>
                </div>
              @endif
            </div>
          @endif

          {{-- Price & CTA --}}
          <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
            <div class="text-xl font-bold text-primary">
              {{ $product->price->regular->formatted() }}
            </div>
            <a href="{{ $product->url }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white font-medium rounded-lg hover:bg-primary-dark transition-colors">
              Bekijk product
              @svg('icons.arrow-right', 'w-4 h-4')
            </a>
          </div>
        </div>
      </div>
    @else
      <div class="flex-1 text-gray-500 italic">
        Product niet beschikbaar
      </div>
    @endif
  </div>
</div>
