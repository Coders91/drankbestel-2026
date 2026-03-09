@props([
    'itemId' => null,
    'categories' => collect(),
])

@php
    $categoryIds = $categories->pluck('id')->values()->toArray();
@endphp

<div
    x-show="activeItem === {{ $itemId }}"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    x-on:mouseenter="megaMenuStay()"
    x-cloak
    class="absolute left-0 right-0 top-full z-50 w-screen max-w-[1232px]"
>
    <div
        class="bg-white rounded-b-xl shadow-xl border border-t-0 border-gray-100 overflow-hidden"
        x-data="flyoutMenu({ categories: @js($categoryIds) })"
        x-on:mousemove.passive="trackMouse($event)"
    >
        <div class="flex min-h-[400px]">
            {{-- Left column: parent categories --}}
            <div class="w-64 border-r border-gray-100 py-4 flex-shrink-0">
                @foreach ($categories as $category)
                    <button
                        type="button"
                        class="w-full flex items-center justify-between px-6 py-3 transition-colors text-left text-gray-700 hover:bg-gray-50 hover:text-gray-900"
                        x-on:mouseenter="enterCategory({{ $category['id'] }})"
                    >
                        <span>{{ $category['name'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Right area: subcategories --}}
            <div class="flex-1 px-8 py-6" x-on:mouseenter="enterSubmenu()">
                @foreach ($categories as $category)
                    <div
                        x-show="activeCategory === {{ $category['id'] }}"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                    >
                        <a href="{{ $category['url'] }}" class="inline-flex items-center gap-2 text-base font-semibold text-gray-800 hover:text-red-600 transition-colors mb-4">
                          Alle {{ $category['name'] }}
                        </a>
                        @if ($category['children']->isNotEmpty())
                          <div class="grid grid-cols-3 gap-x-8">
                            <ul class="space-y-3">
                              @foreach ($category['children'] as $child)
                                <li>
                                  <a href="{{ $child['url'] }}" class="text-gray-800 hover:text-red-600 transition-colors py-1">
                                    {{ $child['name'] }}
                                  </a>
                                </li>
                              @endforeach
                            </ul>
                          </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="bg-gray-50 px-8 py-4 border-t border-gray-100">
            <a
                href="{{ wc_get_page_permalink('shop') }}"
                class="inline-flex items-center gap-2 font-medium text-red-600 hover:text-red-700 transition-colors"
            >
                <span>{{ __('Bekijk alle producten', 'sage') }}</span>
            </a>
        </div>
    </div>
</div>
