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
                            <h4 class="text-white font-heading font-semibold text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                {{ $menuItem->label }}
                            </h4>
                            @if ($menuItem->children)
                                <ul class="space-y-4">
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
                    <h4 class="text-white font-heading font-semibold text-sm uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                        {{ __('Contact', 'sage') }}
                    </h4>
                    <ul class="space-y-4">
                        {{-- WhatsApp --}}
                        <li>
                            <a
                                href="{{ config('store.contact.whatsapp') }}"
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
                                href="mailto:{{ config('store.contact.email') }}"
                                class="inline-flex items-center gap-3 text-gray-400 hover:text-white transition-colors duration-200 group"
                            >
                                <span class="flex items-center justify-center w-9 h-9 bg-gray-800 group-hover:bg-red-600/20 rounded-lg transition-colors duration-200">
                                    @svg('resources.images.icons.mail', 'w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors')
                                </span>
                                <span class="text-sm">{{ config('store.contact.email') }}</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Social Links --}}
                    <div class="mt-6 pt-6 border-t border-gray-800/60">
                        <p class="text-gray-500 text-xs uppercase tracking-wider mb-3">{{ __('Volg ons', 'sage') }}</p>
                        <div class="flex gap-2">
                            @if (config('store.social.facebook'))
                                <a
                                    href="{{ config('store.social.facebook') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center w-9 h-9 bg-gray-800 hover:bg-blue-600/20 rounded-lg transition-all duration-200 group"
                                    aria-label="Facebook"
                                >
                                    @svg('resources.images.icons.facebook', 'w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors')
                                </a>
                            @endif
                            @if (config('store.social.instagram'))
                                <a
                                    href="{{ config('store.social.instagram') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center w-9 h-9 bg-gray-800 hover:bg-pink-600/20 rounded-lg transition-all duration-200 group"
                                    aria-label="Instagram"
                                >
                                    @svg('resources.images.icons.instagram', 'w-5 h-5 text-gray-400 group-hover:text-pink-500 transition-colors')
                                </a>
                            @endif
                            @if (config('store.social.x'))
                                <a
                                    href="{{ config('store.social.x') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center w-9 h-9 bg-gray-800 hover:bg-gray-600/20 rounded-lg transition-all duration-200 group"
                                    aria-label="X"
                                >
                                    @svg('resources.images.logos.x', 'w-4 h-4 text-gray-400 group-hover:text-white transition-colors')
                                </a>
                            @endif
                            @if (config('store.social.trustpilot'))
                                <a
                                    href="{{ config('store.social.trustpilot') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-center w-9 h-9 bg-gray-800 hover:bg-green-600/20 rounded-lg transition-all duration-200 group"
                                    aria-label="Trustpilot"
                                >
                                    @svg('resources.images.logos.trustpilot', 'w-5 h-5 text-gray-400 group-hover:text-green-400 transition-colors')
                                </a>
                            @endif
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
