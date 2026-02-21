{{--
The Template for displaying product archives, including the main shop page which is a post type archive

This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.

HOWEVER, on occasion WooCommerce will need to update template files and you
(the theme developer) will need to copy the new files to your theme to
maintain compatibility. We try to do this as little as possible, but it does
happen. When this occurs the version of the template file will be bumped and
the readme will list any important changes.

@see https://docs.woocommerce.com/document/template-structure/
@package WooCommerce/Templates
@version 3.4.0
--}}

<x-layouts.app>
    <x-page-header class="container pt-6 mb-4" title="{!! woocommerce_page_title(false) !!}" description="{{ get_the_archive_description() }}"/>

    {{-- Category Selector (only shows on leaf categories) --}}
    @if(!empty($soortCategories))
      <x-category-selector :categories="$soortCategories" />
    @endif

    {{-- Main content with sidebar layout --}}

  <div class="container">
    <div
        class="flex flex-col lg:flex-row gap-8"
        x-data="productFilters()"
        @popstate.window="handlePopstate()"
        @filter-apply.window="applyFilter($event.detail.url)"
      >
        {{-- Desktop filters target (receives content via DOM move from mobile-sheet) --}}
        <aside id="desktop-filters-target" class="w-full lg:w-68 shrink-0 max-lg:hidden">
          {{-- Filter content moves here on desktop --}}
        </aside>

        {{-- Products grid --}}
        <div id="product-area" class="flex-1" :class="{ 'opacity-50 pointer-events-none': loading }">


          {{-- Proposed filters --}}
          @if(!empty($proposedFilters))
            <div class="mb-6">
              <x-filters.proposed :filters="$proposedFilters" />
            </div>
          @endif

          @php
            woocommerce_product_loop_start();
          @endphp

          {{-- Result count and sorting --}}
          <div id="result-count" class="flex items-center justify-between mb-4 gap-4">
            <p class="text-sm text-gray-600">
              {{ sprintf(
                  _n('%d product', '%d producten', $totalProducts, 'sage'),
                  $totalProducts
              ) }}
            </p>

            @if(!empty($sortOptions))
              <div class="max-w-50">
              <x-forms.select
                name="ordr"
                :value="$currentSort"
                x-on:change="$dispatch('filter-apply', { url: $event.target.selectedOptions[0].dataset.url })"
                class="text-sm"
              >
              @foreach($sortOptions as $option)
                  <option
                    value="{{ $option['value'] ?? '' }}"
                    @if(isset($option['url'])) data-url="{{ $option['url'] }}" @endif
                    @selected(($option['value'] ?? '') == $currentSort)
                  >
                    {{ $option['label'] ?? '' }}
                  </option>
                @endforeach
              </x-forms.select>
              </div>
            @endif
          </div>

          <div id="products-grid" class="products grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            @forelse ($products as $product)
              <x-product :product="$product" />
            @empty
              <div class="col-span-full text-center py-12 text-gray-500">
                {{ __('Geen producten gevonden.', 'sage') }}
              </div>
            @endforelse
          </div>

          @php
            woocommerce_product_loop_end();
          @endphp

          <div class="lg:hidden">
            <livewire:product-load-more
              :query-vars="$queryVars"
              :max-pages="$maxPages"
              :initial-count="$products->count()"
              :total-products="$totalProducts"
            />
          </div>

          <div id="pagination" class="max-lg:hidden mt-8">
            {!! $pagination !!}
          </div>
        </div>

        {{-- Loading overlay --}}
        <div
          x-show="loading"
          x-transition.opacity
          class="fixed inset-0 bg-white/50 z-50 flex items-center justify-center"
        >
          @svg('resources.images.icons.loader', 'w-8 h-8 animate-spin')
        </div>
      </div>

      {{-- Mobile filter bottom sheet --}}
      <x-filters.mobile-sheet
        :filters="$filters"
        :active-count="$activeFilterCount"
        :selected-chips="$selectedChips"
        :reset-url="$resetUrl"
        :total-results="$totalProducts"
        :more-less-count="$moreLessCount"
      />
  </div>

  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('productFilters', () => ({
        loading: false,

        init() {},

        async applyFilter(url) {
          if (this.loading) return;

          this.loading = true;

          try {
            const html = await this.fetchPage(url);
            this.updateContent(html);
            history.pushState({ filterUrl: url }, '', url);

            // Dispatch event to close mobile sheet
            window.dispatchEvent(new CustomEvent('filter-applied'));

            // Scroll to top of products area
            this.scrollToProducts();
          } catch (err) {
            console.error('Filter failed:', err);
          } finally {
            this.loading = false;
          }
        },

        async handlePopstate() {
          this.loading = true;

          try {
            const html = await this.fetchPage(location.href);
            this.updateContent(html);

            // Scroll to top of products area
            this.scrollToProducts();
          } catch (err) {
            console.error('Popstate failed:', err);
          } finally {
            this.loading = false;
          }
        },

        async fetchPage(url) {
          const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
            },
            body: new URLSearchParams({
              flrt_ajax_link: url,
              wpcAjaxAction: 'filter',
            }),
          });

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
          }

          return response.text();
        },

        updateContent(html) {
          const doc = new DOMParser().parseFromString(html, 'text/html');

          this.replaceElement('#filters-sidebar', doc);
          this.replaceElement('#products-grid', doc);
          this.replaceElement('#result-count', doc);
          this.replaceElement('#pagination', doc);
          this.replaceElement('#proposed-filters', doc);

          document.title = doc.title;
        },

        replaceElement(selector, doc) {
          const current = document.querySelector(selector);
          const incoming = doc.querySelector(selector);

          if (current && incoming) {
            current.innerHTML = incoming.innerHTML;
          }
        },

        scrollToProducts() {
          // Only scroll if user has scrolled down the page
          if (window.scrollY <= 1) return;

          const productArea = document.querySelector('#product-area');
          if (productArea) {
            productArea.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        },
      }));
    });
  </script>
</x-layouts.app>
