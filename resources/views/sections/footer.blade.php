<x-usps :usps="\App\Services\UspService::productUsps()" variant="boxed" :columns="1" />

<footer class="bg-gray-900">
    <div class="container">
        <div class="py-16 lg:py-20">
            {{-- Top Section: Logo + Newsletter --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-10 pb-12 border-b border-gray-800/60">
                {{-- Logo & Tagline --}}
                <div class="max-w-sm">
                    <a href="{{ home_url('/') }}" class="inline-block mb-4 transition-opacity hover:opacity-80">
                        <span class="block h-8">
                            @svg('resources.images.logos.drankbestel-gray', 'h-full w-auto')
                        </span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        {{ __('Uw vertrouwde online drankenspecialist. Kwaliteitswijnen, premium spirits en craft bieren, snel bij u thuisbezorgd.', 'sage') }}
                    </p>
                </div>

                {{-- Newsletter --}}
                <div class="lg:max-w-md w-full">
                    <h3 class="text-white font-heading font-semibold text-sm uppercase tracking-wider mb-3">
                        {{ __('Nieuwsbrief', 'sage') }}
                    </h3>
                    <p class="text-gray-400 text-sm mb-4">
                        {{ __('Ontvang exclusieve aanbiedingen en nieuws over nieuwe wijnen en spirits.', 'sage') }}
                    </p>
                    <livewire:newsletter />
                </div>
            </div>

            {{-- Middle Section: Navigation Columns --}}
            <div class="py-12 grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12 border-b border-gray-800/60">
                {{-- Footer Navigation --}}
                @php($footerMenu = Navi::build('footer_navigation'))
                @if ($footerMenu->isNotEmpty())
                    @foreach ($footerMenu->all() as $menuItem)
                        <div>
                            <h4 class="text-white font-heading font-semibold text-sm uppercase tracking-wider mb-5 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                {{ $menuItem->label }}
                            </h4>
                            @if ($menuItem->children)
                                <ul class="space-y-3">
                                    @foreach ($menuItem->children as $child)
                                        <li>
                                            <a
                                                href="{{ $child->url }}"
                                                class="text-gray-400 text-sm hover:text-white transition-colors duration-200 inline-flex items-center group"
                                            >
                                                <span class="w-0 group-hover:w-2 h-px bg-red-600 transition-all duration-200 mr-0 group-hover:mr-2"></span>
                                                {{ $child->label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                @endif

                {{-- Contact Column --}}
                <div>
                    <h4 class="text-white font-heading font-semibold text-sm uppercase tracking-wider mb-5 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                        {{ __('Contact', 'sage') }}
                    </h4>
                    <ul class="space-y-4">
                        {{-- WhatsApp --}}
                        <li>
                            <a
                                href="https://wa.me/31612345678"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-3 text-gray-400 hover:text-white transition-colors duration-200 group"
                            >
                                <span class="flex items-center justify-center w-9 h-9 bg-gray-800 group-hover:bg-green-600/20 rounded-lg transition-colors duration-200">
                                    @svg('resources.images.logos.whatsapp', 'w-5 h-5 text-green-500')
                                </span>
                                <span class="text-sm">WhatsApp</span>
                            </a>
                        </li>
                        {{-- Email --}}
                        <li>
                            <a
                                href="mailto:info@drankbestel.nl"
                                class="inline-flex items-center gap-3 text-gray-400 hover:text-white transition-colors duration-200 group"
                            >
                                <span class="flex items-center justify-center w-9 h-9 bg-gray-800 group-hover:bg-red-600/20 rounded-lg transition-colors duration-200">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </span>
                                <span class="text-sm">info@drankbestel.nl</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Social Links --}}
                    <div class="mt-6 pt-6 border-t border-gray-800/60">
                        <p class="text-gray-500 text-xs uppercase tracking-wider mb-3">{{ __('Volg ons', 'sage') }}</p>
                        <div class="flex gap-2">
                            <a
                                href="#"
                                class="flex items-center justify-center w-9 h-9 bg-gray-800 hover:bg-blue-600/20 rounded-lg transition-all duration-200 group"
                                aria-label="Facebook"
                            >
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a
                                href="#"
                                class="flex items-center justify-center w-9 h-9 bg-gray-800 hover:bg-pink-600/20 rounded-lg transition-all duration-200 group"
                                aria-label="Instagram"
                            >
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-pink-500 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Section: Trust Signals & Legal --}}
            <div class="pt-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                    {{-- Payment & Trust Badges --}}
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 lg:gap-10">
                        {{-- Payment Methods --}}
                        <div>
                            <p class="text-gray-500 text-xs uppercase tracking-wider mb-3">{{ __('Betaalmethoden', 'sage') }}</p>
                            <div class="flex items-center gap-3 opacity-60 hover:opacity-100 transition-opacity duration-300">
                                @include('partials.payment-icons')
                            </div>
                        </div>

                        {{-- NIX18 Badge --}}
                        <div class="flex items-center gap-3 pl-0 sm:pl-6 sm:border-l border-gray-800">
                            <span class="block h-8 opacity-80">
                                @svg('resources.images.logos.nix18', 'h-full w-auto')
                            </span>
                            <span class="text-gray-500 text-xs max-w-32 leading-tight">
                                {{ __('Geen alcohol onder de 18 jaar', 'sage') }}
                            </span>
                        </div>
                    </div>

                    {{-- Copyright --}}
                    <div class="text-gray-500 text-sm">
                        <p>© {{ date('Y') }} DrankBestel.nl — {{ __('Drink met mate', 'sage') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
