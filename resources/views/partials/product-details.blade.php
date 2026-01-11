<x-accordion-group>
  {{-- Description Accordion --}}
  @if ($product->description)
    <x-accordion :isOpen="true">
      <x-slot:title class="text-lg font-semibold text-gray-900 py-4">
        {{ __('Beschrijving', 'sage') }}
      </x-slot:title>
      <div class="prose prose-sm text-gray-600 pb-6">
        {!! $product->description !!}
      </div>
    </x-accordion>
  @endif

  {{-- Product Details Accordion --}}
  @if (!empty($product->attributes))
    <x-accordion>
      <x-slot:title class="text-lg font-semibold text-gray-900">
        {{ __('Productdetails', 'sage') }}
      </x-slot:title>

      <div class="pt-4">
          @foreach ($product->attributes as $attribute)
            @isset($attribute['value'])
              <dl class="flex py-2">
                <dt class="text-gray-700 min-w-48 basis-1/2">
                  {{ $attribute['label'] }}
                </dt>
                <dd class="flex items-center gap-2 text-gray-900">
                  @isset($attribute['url'])
                    <a href="{{ $attribute['url'] }}"
                       class="text-red-600 underline">
                      {!! $attribute['value'] !!}
                    </a>
                  @else
                    {!! $attribute['value'] !!}
                  @endif
                </dd>
              </dl>
            @endisset
          @endforeach
      </div>
    </x-accordion>
  @endif

  {{-- Reviews Accordion --}}
  <x-accordion id="reviews">
    <x-slot:title class="text-lg font-semibold text-gray-900">
      {{ __('Reviews', 'sage') }}
      @if ($product->hasReviews())
        <span class="ml-1 text-sm font-normal text-gray-500">({{ $product->reviewCount }})</span>
      @endif
    </x-slot:title>
    <div class="pb-6">
      @if ($product->hasReviews())
        <div class="mb-6">
          <div class="flex items-center gap-4">
            <div class="text-4xl font-bold text-gray-900">{{ number_format($product->rating, 1) }}</div>
            <div>
              <x-star-rating :rating="$product->rating" size="md" />
              <p class="text-sm text-gray-500 mt-1">
                {{ __('Gebaseerd op', 'sage') }} {{ $product->reviewCount }} {{ $product->reviewCount === 1 ? __('review', 'sage') : __('reviews', 'sage') }}
              </p>
            </div>
          </div>
        </div>
        <div class="space-y-4">
          @foreach ($product->displayReviews as $comment)
            @php $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true)); @endphp
            <div class="border-b border-gray-100 pb-4 last:border-0">
              <div class="flex items-center gap-2 mb-2">
                <x-star-rating :rating="$rating" size="sm" />
                <span class="text-sm font-medium text-gray-900">{{ $comment->comment_author }}</span>
                <span class="text-xs text-gray-400">{{ date_i18n(get_option('date_format'), strtotime($comment->comment_date)) }}</span>
              </div>
              <p class="text-sm text-gray-600">{{ $comment->comment_content }}</p>
            </div>
          @endforeach
        </div>

        {{-- Review Form --}}
        <div class="mt-8 pt-8 border-t border-gray-200">
          <livewire:product-review :product-id="$product->id" />
        </div>
      @else
        <p class="text-gray-500 mb-6">{{ __('Er zijn nog geen reviews voor dit product.', 'sage') }}</p>

        {{-- Review Form --}}
        <livewire:product-review :product-id="$product->id" />
      @endif
    </div>
  </x-accordion>

  {{-- Shipping & Returns Accordion --}}
  <x-accordion>
    <x-slot:title class="text-lg font-semibold text-gray-900">
      {{ __('Verzending & Retour', 'sage') }}
    </x-slot:title>
    <div class="prose prose-sm text-gray-600 pb-6">
      <h4>{{ __('Verzending', 'sage') }}</h4>
      <ul>
        <li>{{ __('Gratis verzending vanaf', 'sage') }} &euro;50</li>
        <li>{{ __('Standaard verzending:', 'sage') }} &euro;4,95</li>
        <li>{{ __('Levertijd: 1-3 werkdagen', 'sage') }}</li>
      </ul>
      <h4>{{ __('Retourneren', 'sage') }}</h4>
      <ul>
        <li>{{ __('14 dagen bedenktijd', 'sage') }}</li>
        <li>{{ __('Retourneren is gratis', 'sage') }}</li>
        <li>{{ __('Ongeopende producten kunnen geretourneerd worden', 'sage') }}</li>
      </ul>
    </div>
  </x-accordion>
</x-accordion-group>
