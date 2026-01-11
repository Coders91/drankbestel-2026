@props([
    'name' => 'primary_navigation',
    'megaMenuCategories' => null,
    'featuredBrands' => null,
])

@php
    $menu = Navi::build($name);
@endphp

{{-- Mobile Menu Slide-out --}}
<div
    x-show="mobileMenuOpen"
    x-cloak
    class="fixed inset-0 z-[100] lg:hidden"
>
    {{-- Backdrop --}}
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-gray-900/30 backdrop-blur-sm"
        @click="closeMobileMenu()"
    ></div>

    {{-- Panel --}}
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="absolute inset-y-0 left-0 w-full max-w-sm bg-white shadow-xl flex flex-col"
    >
        {{-- Header --}}
        <header class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <a href="{{ home_url('/') }}" class="flex-shrink-0">
                @svg('resources.images.logos.drankbestel', 'h-6 w-auto')
            </a>
            <button
                type="button"
                class="p-2 -mr-2 text-gray-500 hover:text-gray-700 transition-colors"
                @click="closeMobileMenu()"
                aria-label="{{ __('Sluit menu', 'sage') }}"
            >
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4">
            @if ($menu->isNotEmpty())
                <ul class="space-y-1">
                    @foreach ($menu->all() as $item)
                        @php
                            $hasChildren = $item->children && count($item->children) > 0;
                            $parentClasses = 'flex-1 flex items-center px-6 py-3 text-base font-medium transition-colors';
                            $parentClasses .= $item->active ? ' text-red-600 bg-red-50' : ' text-gray-900 hover:bg-gray-50';
                            $simpleClasses = 'block px-6 py-3 text-base font-medium transition-colors';
                            $simpleClasses .= $item->active ? ' text-red-600 bg-red-50' : ' text-gray-900 hover:bg-gray-50';
                        @endphp

                        <li x-data="{ expanded: false }">
                            @if ($hasChildren)
                                {{-- Parent with children --}}
                                <div class="flex items-center">
                                    <a href="{{ $item->url }}" class="{{ $parentClasses }}">
                                        {{ $item->label }}
                                    </a>
                                    <button
                                        type="button"
                                        class="px-4 py-3 text-gray-400 hover:text-gray-600 transition-colors"
                                        @click="expanded = !expanded"
                                        x-bind:aria-expanded="expanded"
                                    >
                                        <svg
                                            class="w-5 h-5 transition-transform duration-200"
                                            x-bind:class="{ 'rotate-180': expanded }"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Children --}}
                                <ul
                                    x-show="expanded"
                                    x-collapse
                                    class="bg-gray-50 py-2"
                                >
                                    @foreach ($item->children as $child)
                                        @php
                                            $childClasses = 'block pl-10 pr-6 py-2.5 text-sm transition-colors';
                                            $childClasses .= $child->active ? ' text-red-600 font-medium' : ' text-gray-600 hover:text-red-600';
                                        @endphp
                                        <li>
                                            <a href="{{ $child->url }}" class="{{ $childClasses }}">
                                                {{ $child->label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                {{-- Simple link --}}
                                <a href="{{ $item->url }}" class="{{ $simpleClasses }}">
                                    {{ $item->label }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Additional Links --}}
            <div class="mt-6 pt-6 border-t border-gray-100 px-6 space-y-4">
                {{-- Categories from WooCommerce --}}
                @if (isset($megaMenuCategories) && $megaMenuCategories->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                            {{ __('Categorieën', 'sage') }}
                        </h3>
                        <ul class="space-y-2">
                            @foreach ($megaMenuCategories->take(6) as $category)
                                <li>
                                    <a
                                        href="{{ $category['url'] }}"
                                        class="text-sm text-gray-600 hover:text-red-600 transition-colors"
                                    >
                                        {{ $category['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Brands --}}
                @if (isset($featuredBrands) && $featuredBrands->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                            {{ __('Populaire Merken', 'sage') }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($featuredBrands->take(6) as $brand)
                                <a
                                    href="{{ $brand['url'] }}"
                                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 rounded-full text-xs font-medium text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors"
                                >
                                    {{ $brand['name'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </nav>

        {{-- Footer --}}
        <footer class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between text-sm">
                <a
                    href="{{ route('favorites') }}"
                    class="flex items-center gap-2 text-gray-600 hover:text-red-600 transition-colors"
                >
                    @svg('resources.images.icons.heart', 'w-5 h-5')
                    <span>{{ __('Favorieten', 'sage') }}</span>
                </a>
                <a
                    href="{{ route('account') }}"
                    class="flex items-center gap-2 text-gray-600 hover:text-red-600 transition-colors"
                >
                    @svg('resources.images.icons.user', 'w-5 h-5')
                    <span>{{ __('Account', 'sage') }}</span>
                </a>
            </div>
        </footer>
    </div>
</div>
