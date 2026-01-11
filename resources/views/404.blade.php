<x-layouts.app :breadcrumbs="false">
    <section class="relative min-h-[70vh] flex items-center justify-center py-16 lg:py-24 overflow-hidden">
        <div class="container relative">
            <div class="max-w-2xl mx-auto text-center">
                <div class="relative mb-8 inline-block">
                    <h1 class="text-[10rem] sm:text-[12rem] lg:text-[14rem] font-heading font-bold leading-none tracking-tighter select-none">
                        <span class="inline-block text-gray-900 animate-[fadeSlideUp_0.6s_ease-out_both]">4</span>
                        <span class="inline-block text-gray-900 animate-[fadeSlideUp_0.6s_ease-out_both]">0</span>
                        <span class="inline-block text-gray-900 animate-[fadeSlideUp_0.6s_ease-out_0.2s_both]">4</span>
                    </h1>
                </div>

                <div class="space-y-4 mb-10 animate-[fadeSlideUp_0.6s_ease-out_0.3s_both]">
                    <h2 class="text-2xl sm:text-3xl font-heading font-semibold text-gray-900">
                        {{ __('Pagina niet gevonden', 'sage') }}
                    </h2>
                    <p class="text-gray-500 text-lg max-w-md mx-auto leading-relaxed">
                        {{ __('Deze pagina lijkt te zijn verdampt... Net als de laatste druppel van een goede fles.', 'sage') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
