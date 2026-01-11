@props([
    'itemId' => null,
    'children' => [],
    'categories' => null,
    'brands' => null,
])

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
    <div class="bg-white rounded-b-xl shadow-xl border border-t-0 border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="grid grid-cols-12 gap-8">
                {{-- Category Columns --}}
                <div class="col-span-9">
                    @if ($categories && $categories->isNotEmpty())
                        <div class="grid grid-cols-3 gap-8">
                            @foreach ($categories->take(6) as $category)
                                <x-navigation.mega-column
                                    :title="$category['name']"
                                    :url="$category['url']"
                                    :items="$category['children'] ?? collect()"
                                />
                            @endforeach
                        </div>
                    @elseif (count($children) > 0)
                        {{-- Fallback to menu children --}}
                        @php
                            $childCount = count($children);
                            $chunkSize = $childCount > 0 ? (int) ceil($childCount / 3) : 1;
                        @endphp
                        <div class="grid grid-cols-3 gap-8">
                            @foreach (collect($children)->chunk($chunkSize) as $chunk)
                                <ul class="space-y-2">
                                    @foreach ($chunk as $child)
                                        @php
                                            $childClasses = 'block py-1.5 text-sm transition-colors';
                                            $childClasses .= $child->active
                                                ? ' text-red-600 font-medium'
                                                : ' text-gray-600 hover:text-red-600';
                                        @endphp
                                        <li>
                                            <a href="{{ $child->url }}" class="{{ $childClasses }}">
                                                {{ $child->label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Featured Brands --}}
                <div class="col-span-3 border-l border-gray-100 pl-8">
                    <x-navigation.brand-grid :brands="$brands" />
                </div>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="bg-gray-50 px-8 py-4 border-t border-gray-100">
            <a
                href="{{ wc_get_page_permalink('shop') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-red-600 hover:text-red-700 transition-colors"
            >
                <span>{{ __('Bekijk alle producten', 'sage') }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</div>
