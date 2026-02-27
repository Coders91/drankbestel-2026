@props([
    'name' => 'primary_navigation',
    'featuredBrands' => null,
])

@php
    $menu = Navi::build($name);
@endphp

@if ($menu->isNotEmpty())
    <nav
        class="mega-menu relative hidden lg:block bg-white"
        aria-label="{{ wp_get_nav_menu_name($name) }}"
        x-on:mouseleave="megaMenuLeave()"
    >
        {{-- Search active overlay --}}
        <div
            x-show="searchActive"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 z-10 bg-gray-900/30 pointer-events-none"
        ></div>
        <div class="container mx-auto px-4">
            <ul class="flex items-center gap-8">
                @foreach ($menu->all() as $item)
                    @php
                        $hasChildren = $item->children && count($item->children) > 0;
                        $isMegaPanel = strtolower($item->label) === 'gedestilleerd';
                        $showDropdown = $hasChildren;
                        $itemClasses = 'group relative flex items-center gap-6 py-4.5 text-sm font-medium transition-colors';
                        $itemClasses .= $item->active ? ' text-red-600' : ' text-gray-700 hover:text-red-600';
                        $indicatorClasses = 'absolute bottom-0 left-0 right-0 h-0.5 bg-red-600 transition-transform origin-left';
                    @endphp

                    <li
                        class="relative"
                        x-on:mouseenter="megaMenuEnter({{ $item->id }})"
                    >
                        {{-- Menu Item Trigger --}}
                        <a
                            href="{{ $item->url }}"
                            class="{{ $itemClasses }}"
                            @if($showDropdown)
                            aria-haspopup="true"
                            x-bind:aria-expanded="activeItem === {{ $item->id }}"
                            @endif
                        >
                            <span class="flex items-center gap-2">{{ $item->label }}
                              @if ($showDropdown)
                                  <svg
                                      class="size-4 transition-transform duration-200"
                                      x-bind:class="{ 'rotate-180': activeItem === {{ $item->id }} }"
                                      fill="none"
                                      viewBox="0 0 24 24"
                                      stroke="currentColor"
                                      stroke-width="2"
                                  >
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                  </svg>
                              @endif
                            </span>


                            {{-- Active indicator --}}
                            <span
                                class="{{ $indicatorClasses }}"
                                x-cloak
                                x-bind:class="activeItem === {{ $item->id }} ? 'scale-x-100' : '{{ $item->active ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}'"
                            ></span>
                        </a>

                        {{-- Dropdown / Mega Panel --}}
                        @if ($isMegaPanel && $hasChildren)
                            @php
                                // Build categories from menu children that link to product categories
                                $megaPanelCategories = collect($item->children)
                                    ->filter(fn($child) => $child->object === 'product_cat')
                                    ->map(function($child) {
                                        $termId = (int) $child->objectId;
                                        $childTerms = get_terms([
                                            'taxonomy' => 'product_cat',
                                            'parent' => $termId,
                                            'hide_empty' => true,
                                            'orderby' => 'menu_order',
                                            'order' => 'ASC',
                                        ]);

                                        return [
                                            'id' => $termId,
                                            'name' => $child->label,
                                            'url' => $child->url,
                                            'children' => !is_wp_error($childTerms) ? collect($childTerms)->map(fn($term) => [
                                                'id' => $term->term_id,
                                                'name' => $term->name,
                                                'slug' => $term->slug,
                                                'url' => get_term_link($term),
                                                'count' => $term->count,
                                            ]) : collect(),
                                        ];
                                    });
                            @endphp
                            <x-navigation.mega-panel
                                :item-id="$item->id"
                                :children="$item->children"
                                :categories="$megaPanelCategories"
                                :brands="$featuredBrands ?? collect()"
                            />
                        @elseif ($hasChildren)
                            <x-navigation.dropdown
                                :item-id="$item->id"
                                :children="$item->children"
                            />
                        @endif
                    </li>
                @endforeach
                <a class="ml-auto text-red-600" href="/aanbiedingen">Aanbiedingen</a>
            </ul>
        </div>
    </nav>
@endif
