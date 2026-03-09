@props([
    'name' => 'primary_navigation',
    'megaMenuCategories' => null,
    'featuredBrands' => null,
])

@php
    $menu = Navi::build($name);
    $menuItems = $menu->isNotEmpty() ? collect($menu->all()) : collect();
@endphp

{{-- Mobile Menu Slide-out --}}
<div
    x-show="mobileMenuOpen"
    x-cloak
    class="fixed inset-0 z-[100] lg:hidden"
    x-data="{
        activePanel: null,
        activePanelLabel: '',
        navigateTo(index, label) {
            this.activePanel = index;
            this.activePanelLabel = label;
        },
        navigateBack() {
            this.activePanel = null;
            this.activePanelLabel = '';
        },
        close() {
            this.closeMobileMenu();
            setTimeout(() => {
                this.activePanel = null;
                this.activePanelLabel = '';
            }, 300);
        }
    }"
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
        @click="close()"
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
        class="absolute inset-y-0 left-0 w-full max-w-sm bg-white shadow-xl flex flex-col overflow-hidden"
    >
        {{-- Header --}}
        <header class="flex items-center justify-between px-6 py-3 border-b border-gray-200">
            <div class="flex items-center gap-2">
                <button
                    x-show="activePanel !== null"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-x-2"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    type="button"
                    class="text-gray-500 hover:text-gray-700 transition-colors"
                    @click="navigateBack()"
                    aria-label="{{ __('Terug', 'sage') }}"
                >
                    @svg('resources.images.icons.arrow-left')
                </button>
                <h2
                    class="text-lg font-semibold text-gray-900"
                    x-text="activePanel !== null ? activePanelLabel : '{{ __('Menu', 'sage') }}'"
                ></h2>
            </div>
            <button
                type="button"
                class="p-2 -mr-2 text-gray-500 hover:text-gray-700 transition-colors"
                @click="close()"
                aria-label="{{ __('Sluit menu', 'sage') }}"
            >
                @svg('resources.images.icons.x-close')
            </button>
        </header>

        {{-- Panels container --}}
        <div class="relative flex-1 overflow-hidden">

            {{-- Root panel --}}
            <div
                class="absolute inset-0 transition-transform duration-300 ease-in-out"
                :class="activePanel !== null ? '-translate-x-full' : 'translate-x-0'"
            >
                <nav class="h-full overflow-y-auto overscroll-contain py-2">
                    @if ($menuItems->count())
                        <ul>
                            @foreach ($menuItems as $index => $item)
                                @php
                                    $hasChildren = $item->children && count($item->children) > 0;
                                @endphp

                                <li>
                                    @if ($hasChildren)
                                        <button
                                            type="button"
                                            class="flex items-center justify-between w-full px-6 py-3.5 text-base font-medium transition-colors {{ $item->active ? 'text-red-600' : 'text-gray-900 active:bg-gray-50' }}"
                                            @click="navigateTo({{ $index }}, '{{ addslashes($item->label) }}')"
                                        >
                                            <span>{{ $item->label }}</span>
                                            @svg('resources.images.icons.chevron-right', 'w-5 h-5 text-gray-400')
                                        </button>
                                    @else
                                        <a
                                            href="{{ $item->url }}"
                                            class="block px-6 py-3.5 text-base font-medium transition-colors {{ $item->active ? 'text-red-600' : 'text-gray-900 active:bg-gray-50' }}"
                                        >
                                            {{ $item->label }}
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                            <li>
                              <a class="block px-6 py-3.5 text-base text-red-600 font-medium transition-colors" href="/aanbiedingen/">Aanbiedingen</a>
                            </li>
                        </ul>
                    @endif
                </nav>
            </div>

            {{-- Child panels --}}
            @foreach ($menuItems as $index => $item)
                @if ($item->children && count($item->children) > 0)
                    <div
                        class="absolute inset-0 transition-transform duration-300 ease-in-out"
                        :class="activePanel === {{ $index }} ? 'translate-x-0' : 'translate-x-full'"
                    >
                        <div class="h-full overflow-y-auto overscroll-contain py-2">
                            {{-- View all link --}}
                            <a
                                href="{{ $item->url }}"
                                class="flex items-center gap-2 px-6 pt-3 font-semibold text-red-600 active:bg-red-50 transition-colors"
                            >
                                {{ __('Bekijk alles', 'sage') }}
                            </a>
                            <ul class="py-2">
                                @foreach ($item->children as $child)
                                    <li>
                                        <a
                                            href="{{ $child->url }}"
                                            class="block px-6 py-3 text-base transition-colors {{ $child->active ? 'text-red-600 font-medium' : 'text-gray-700 active:bg-gray-50' }}"
                                        >
                                            {{ $child->label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
