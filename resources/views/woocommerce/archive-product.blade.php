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

    {{-- Main content with sidebar layout --}}

  <div class="container">
    <div
        class="flex flex-col lg:flex-row gap-8"
        x-data="productFilters()"
        @popstate.window="handlePopstate()"
        @filter-apply.window="applyFilter($event.detail.url)"
      >
        <aside id="desktop-filters-target" class="w-full lg:w-68 shrink-0 max-lg:hidden">
          <div id="filters-sidebar">
            <x-filters.sidebar
              :filters="$filters"
              :active-count="$activeFilterCount"
              :selected-chips="$selectedChips"
              :reset-url="$resetUrl"
              :more-less-count="$moreLessCount"
            />
          </div>
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

          <div id="products-grid" class="products grid grid-cols-2 lg:grid-cols-3 gap-3 lg:gap-6">
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
            @if($maxPages > 1 && $currentPage < $maxPages)
              <div class="flex flex-col justify-end items-center gap-4 mt-8" id="mobile-load-more">
                <x-button
                  type="button"
                  class="max-sm:w-full"
                  variant="secondary"
                  @click="loadMore('{{ $nextPageUrl }}')"
                >
                  {{ __('Toon meer', 'sage') }}
                  @svg('resources.images.icons.arrow-down')
                </x-button>
                <p class="text-sm text-gray-700">
                  {{ min($currentPage * $productsPerPage, $totalProducts) }} van {{ $totalProducts }} producten gezien
                </p>
              </div>
            @endif
          </div>

          <div id="pagination" class="max-lg:hidden mt-8"
               @click="if ($event.target.closest('a')) { $event.preventDefault(); applyFilter($event.target.closest('a').href) }">
            {!! $pagination !!}
          </div>
        </div>

        {{-- Loading overlay --}}
        <div
          x-show="loading"
          x-transition.opacity
          x-cloak
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
        desktopMq: window.matchMedia('(min-width: 1024px)'),

        init() {},

        isMobile() {
          return !this.desktopMq.matches;
        },

        async applyFilter(url) {
          if (this.loading) return;

          if (!this.isMobile()) {
            this.scrollToProducts();
          }

          const loaderTimeout = setTimeout(() => { this.loading = true; }, 150);

          try {
            const html = await this.fetchPage(url);
            this.updateContent(html);
            this.dispatchCounts();
            history.pushState({ filterUrl: url }, '', url);

            if (!this.isMobile()) {
              window.dispatchEvent(new CustomEvent('filter-applied'));
            }
          } catch (err) {
            console.error('Filter failed:', err);
          } finally {
            clearTimeout(loaderTimeout);
            this.loading = false;
          }
        },

        async loadMore(url) {
          if (this.loading) return;

          const loaderTimeout = setTimeout(() => { this.loading = true; }, 150);

          try {
            const html = await this.fetchPage(url);
            const doc = new DOMParser().parseFromString(html, 'text/html');

            // Append new products instead of replacing
            const incomingGrid = doc.querySelector('#products-grid');
            const currentGrid = document.querySelector('#products-grid');
            if (currentGrid && incomingGrid) {
              currentGrid.insertAdjacentHTML('beforeend', incomingGrid.innerHTML);
            }

            // Replace supporting elements
            this.replaceElement('#mobile-load-more', doc);
            this.replaceElement('#filters-sidebar', doc);
            this.replaceElement('#result-count', doc);
            this.replaceElement('#proposed-filters', doc);

            history.pushState({ filterUrl: url }, '', url);
          } catch (err) {
            console.error('Load more failed:', err);
          } finally {
            clearTimeout(loaderTimeout);
            this.loading = false;
          }
        },

        async handlePopstate() {
          if (!this.isMobile()) {
            this.scrollToProducts();
          }

          const loaderTimeout = setTimeout(() => { this.loading = true; }, 150);

          try {
            const html = await this.fetchPage(location.href);
            this.updateContent(html);
            this.dispatchCounts();
          } catch (err) {
            console.error('Popstate failed:', err);
          } finally {
            clearTimeout(loaderTimeout);
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
          this.replaceElement('#mobile-load-more', doc);
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

        dispatchCounts() {
          const sidebar = document.querySelector('#filters-sidebar [data-active-count]');
          const activeCount = sidebar ? parseInt(sidebar.getAttribute('data-active-count'), 10) : 0;

          const resultCountEl = document.querySelector('#result-count p');
          let totalResults = 0;
          if (resultCountEl) {
            const match = resultCountEl.textContent.match(/(\d+)/);
            if (match) totalResults = parseInt(match[1], 10);
          }

          window.dispatchEvent(new CustomEvent('filter-counts-updated', {
            detail: { activeCount, totalResults }
          }));
        },

        scrollToProducts() {
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
