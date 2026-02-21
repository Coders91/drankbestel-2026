{{--
  Template Name: Merken (Brands)
--}}

<x-layouts.app>
    @php
        // Get available letters for navigation
        $availableLetters = array_keys($all_brands_grouped_alphabetically);
        $allLetters = array_merge(range('A', 'Z'), ['0-9']);
    @endphp

    {{-- Hero Section with Featured Brands Slider --}}
    <section class="relative bg-gradient-to-b from-gray-50 to-white pt-12 pb-16 lg:pt-16 lg:pb-24 overflow-hidden">
        {{-- Subtle decorative background --}}
        <div class="absolute inset-0 opacity-[0.015]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23000000&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

        <div class="container relative">
            {{-- Section Header --}}
            <div class="text-center mb-10 lg:mb-14">
                <span class="inline-block px-4 py-1.5 mb-4 text-xs font-medium tracking-wider uppercase text-red-600 bg-red-50 rounded-full">
                    {{ __('Ontdek', 'sage') }}
                </span>
                <h1 class="text-3xl lg:text-5xl font-heading font-bold text-gray-900 mb-4">
                    {{ __('Onze Merken', 'sage') }}
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    {{ __('Ontdek ons uitgebreide assortiment van topmerken. Van klassieke favorieten tot exclusieve premium labels.', 'sage') }}
                </p>
            </div>

            {{-- Featured Brands Slider --}}
            @if(count($brands_with_images) > 0)
                <div class="relative px-2 lg:px-0">
                    <x-slider
                        id="featured-brands-slider"
                        class="overflow-hidden"
                        :options="[
                            'slidesPerView' => 2,
                            'spaceBetween' => 16,
                            'navigation' => true,
                            'loop' => count($brands_with_images) > 5,
                            'breakpoints' => [
                                480 => ['slidesPerView' => 3, 'spaceBetween' => 16],
                                640 => ['slidesPerView' => 3, 'spaceBetween' => 20],
                                768 => ['slidesPerView' => 4, 'spaceBetween' => 20],
                                1024 => ['slidesPerView' => 5, 'spaceBetween' => 24],
                            ],
                        ]"
                    >
                        @foreach($brands_with_images as $brand)
                            <div class="swiper-slide h-auto">
                                <a
                                    href="{{ $brand['url'] }}"
                                    class="group flex items-center justify-center h-24 lg:h-28 bg-white rounded-xl border border-gray-200 p-4 transition-all duration-300 hover:border-red-200 hover:shadow-lg hover:shadow-red-500/5"
                                    title="{{ $brand['name'] }}"
                                >
                                    <img
                                        src="{{ $brand['thumbnail_url'] }}"
                                        alt="{{ $brand['name'] }}"
                                        class="max-w-full max-h-full w-auto h-auto object-contain grayscale opacity-60 transition-all duration-300 group-hover:grayscale-0 group-hover:opacity-100 group-hover:scale-110"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </a>
                            </div>
                        @endforeach
                    </x-slider>
                </div>
            @endif
        </div>
    </section>

    {{-- Alphabetic Navigation & Brand Listings --}}
    <section
        class="py-12 lg:py-20 bg-white"
        x-data="{
            scrollToLetter(letter) {
                const element = document.getElementById('letter-' + letter);
                if (element) {
                    const offset = 80;
                    const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
                    window.scrollTo({
                        top: elementPosition - offset,
                        behavior: 'smooth'
                    });
                }
            }
        }"
    >
        <div class="container">
            {{-- Alphabet Navigation Bar --}}
            <nav class="mb-12 lg:mb-16" aria-label="{{ __('Navigatie op letter', 'sage') }}">
                <div class="flex items-center justify-center gap-1 lg:gap-1.5 flex-wrap">
                    @foreach($allLetters as $letter)
                        @php
                            $isAvailable = in_array($letter, $availableLetters);
                        @endphp
                        <button
                            type="button"
                            @if($isAvailable)
                                @click="scrollToLetter('{{ $letter }}')"
                                class="relative w-9 h-9 lg:w-10 lg:h-10 flex items-center justify-center text-sm lg:text-base font-medium text-gray-700 rounded-lg transition-all duration-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            @else
                                disabled
                                class="w-9 h-9 lg:w-10 lg:h-10 flex items-center justify-center text-sm lg:text-base font-medium text-gray-300 cursor-not-allowed"
                            @endif
                            aria-label="{{ __('Ga naar letter', 'sage') }} {{ $letter }}"
                        >
                            {{ $letter }}
                        </button>
                    @endforeach
                </div>

                {{-- Subtle divider --}}
                <div class="mt-8 lg:mt-10 flex items-center gap-4">
                    <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent"></div>
                </div>
            </nav>

            {{-- Grouped Brand Listings --}}
            <div class="space-y-12 lg:space-y-16">
                @foreach($all_brands_grouped_alphabetically as $letter => $brands)
                    <div id="letter-{{ $letter }}" class="scroll-mt-24">
                        {{-- Letter Header --}}
                        <div class="flex items-center gap-4 mb-6 lg:mb-8">
                            <div class="flex items-center justify-center w-12 h-12 lg:w-14 lg:h-14 bg-red-600 rounded-xl shadow-lg shadow-red-600/20">
                                <span class="text-xl lg:text-2xl font-heading font-bold text-white">
                                    {{ $letter }}
                                </span>
                            </div>
                            <div class="flex-1 h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                            <span class="text-sm text-gray-400 font-medium">
                                {{ count($brands) }} {{ count($brands) === 1 ? __('merk', 'sage') : __('merken', 'sage') }}
                            </span>
                        </div>

                        {{-- Brands Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 lg:gap-4">
                            @foreach($brands as $brand)
                                <x-brand-card :brand="$brand" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Empty State --}}
            @if(empty($all_brands_grouped_alphabetically))
                <div class="text-center py-16">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ __('Geen merken gevonden', 'sage') }}
                    </h3>
                    <p class="text-gray-500">
                        {{ __('Er zijn momenteel geen merken beschikbaar.', 'sage') }}
                    </p>
                </div>
            @endif

            {{-- Back to top button --}}
            @if(!empty($all_brands_grouped_alphabetically))
                <div class="mt-16 lg:mt-20 text-center">
                    <button
                        type="button"
                        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-full transition-all duration-200 hover:bg-gray-200 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                    >
                        <svg class="w-4 h-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        {{ __('Terug naar boven', 'sage') }}
                    </button>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
