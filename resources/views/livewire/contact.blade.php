<div class="container py-12" x-data="contactForm()">
    <x-page-header class="mb-4" title="{{ __('Contact', 'sage') }}" />

    <p class="text-gray-600 mb-8 max-w-2xl">
        {{ __('Heb je een vraag, opmerking of suggestie? Neem gerust contact met ons op. We helpen je graag verder.', 'sage') }}
    </p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
        {{-- Contact Info Sidebar --}}
        <div class="lg:col-span-1 space-y-4">
            {{-- Phone --}}
            @if(config('store.contact.phone'))
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('store.contact.phone')) }}"
               class="group block bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:border-red-200 hover:shadow-md transition-all duration-200">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-red-50 rounded-lg group-hover:bg-red-100 transition-colors">
                        @svg('resources.images.icons.phone', 'w-5 h-5 text-red-600')
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-0.5">{{ __('Telefoon', 'sage') }}</h3>
                        <span class="text-gray-600 group-hover:text-red-600 transition-colors">
                            {{ config('store.contact.phone') }}
                        </span>
                    </div>
                </div>
            </a>
            @endif

            {{-- Email --}}
            @if(config('store.contact.email'))
            <a href="mailto:{{ config('store.contact.email') }}"
               class="group block bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:border-red-200 hover:shadow-md transition-all duration-200">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-red-50 rounded-lg group-hover:bg-red-100 transition-colors">
                        @svg('resources.images.icons.mail', 'w-5 h-5 text-red-600')
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-0.5">{{ __('E-mail', 'sage') }}</h3>
                        <span class="text-gray-600 group-hover:text-red-600 transition-colors break-all">
                            {{ config('store.contact.email') }}
                        </span>
                    </div>
                </div>
            </a>
            @endif

            {{-- WhatsApp --}}
            @if(config('store.contact.whatsapp'))
            <a href="{{ config('store.contact.whatsapp') }}"
               target="_blank"
               rel="noopener noreferrer"
               class="group block bg-white rounded-lg p-6 border border-gray-200 shadow-sm hover:border-green-200 hover:shadow-md transition-all duration-200">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-green-50 rounded-lg group-hover:bg-green-100 transition-colors">
                        @svg('resources.images.logos.whatsapp', 'w-5 h-5 text-green-600')
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-0.5">{{ __('WhatsApp', 'sage') }}</h3>
                        <span class="text-gray-600 group-hover:text-green-600 transition-colors">
                            {{ config('store.contact.phone') }}
                        </span>
                    </div>
                </div>
            </a>
            @endif

            {{-- Business Hours --}}
            @if(config('store.hours'))
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-lg">
                        @svg('resources.images.icons.clock', 'w-5 h-5 text-gray-600')
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ __('Openingstijden', 'sage') }}</h3>
                        <div class="space-y-2">
                            @foreach(config('store.hours') as $day => $time)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ $day }}</span>
                                    <span class="font-medium text-gray-900">{{ $time }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Address --}}
            @if(config('store.address'))
                <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-lg">
                            @svg('resources.images.icons.map-pin', 'w-5 h-5 text-gray-600')
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-1">{{ __('Adres', 'sage') }}</h3>
                            <address class="text-gray-600 not-italic text-sm leading-relaxed">
                                {{ config('store.address.street') }}<br>
                                {{ config('store.address.zipcode') }} {{ config('store.address.city') }}
                            </address>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Contact Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:p-8">
                <h2 class="text-xl font-semibold font-heading text-gray-900 mb-2">
                    {{ __('Stuur ons een bericht', 'sage') }}
                </h2>
                <p class="text-gray-600 text-sm mb-6">
                    {{ __('Vul onderstaand formulier in en we nemen zo snel mogelijk contact met je op.', 'sage') }}
                </p>

                @if ($submitted)
                    {{-- Success State --}}
                    <x-alert type="success" class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-full shrink-0">
                                @svg('resources.images.icons.check', 'w-5 h-5 text-green-600')
                            </div>
                            <div>
                                <h3 class="font-semibold text-green-800 mb-1">
                                    {{ __('Bericht verzonden!', 'sage') }}
                                </h3>
                                <p class="text-green-700">
                                    {{ __('Bedankt voor je bericht. We nemen zo snel mogelijk contact met je op.', 'sage') }}
                                </p>
                            </div>
                        </div>
                    </x-alert>
                @else
                    <form class="space-y-5" @submit.prevent="if(validateAll('contactForm')) $wire.submit()" id="contactForm">
                        {{-- Name Field --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('Naam', 'sage') }} <span class="text-red-600">*</span>
                            </label>
                            <x-forms.input-text
                                id="name"
                                name="name"
                                wire:model="form.name"
                                placeholder="{{ __('Je naam', 'sage') }}"
                                x-bind:class="{'border-red-600 focus:ring-red-600': errors.name}"
                                @input="markTouched('name')"
                                @blur="validateField($el)"
                            />
                            <template x-if="errors.name">
                                <p x-text="errors.name" class="text-red-600 text-sm mt-1.5"></p>
                            </template>
                            @error('form.name')
                                <p class="text-red-600 text-sm mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email Field --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('E-mailadres', 'sage') }} <span class="text-red-600">*</span>
                            </label>
                            <x-forms.input-text
                                id="email"
                                name="email"
                                type="email"
                                wire:model="form.email"
                                placeholder="{{ __('je@email.nl', 'sage') }}"
                                x-bind:class="{'border-red-600 focus:ring-red-600': errors.email}"
                                @input="markTouched('email')"
                                @blur="validateField($el)"
                            />
                            <template x-if="errors.email">
                                <p x-text="errors.email" class="text-red-600 text-sm mt-1.5"></p>
                            </template>
                            @error('form.email')
                                <p class="text-red-600 text-sm mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Message Field --}}
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ __('Bericht', 'sage') }} <span class="text-red-600">*</span>
                            </label>
                            <x-forms.text-area
                                id="message"
                                name="message"
                                wire:model="form.message"
                                rows="5"
                                placeholder="{{ __('Hoe kunnen we je helpen?', 'sage') }}"
                                x-bind:class="{'border-red-600 focus:ring-red-600': errors.message}"
                                @input="markTouched('message')"
                                @blur="validateField($el)"
                            />
                            <template x-if="errors.message">
                                <p x-text="errors.message" class="text-red-600 text-sm mt-1.5"></p>
                            </template>
                            @error('form.message')
                                <p class="text-red-600 text-sm mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-2">
                            <x-button type="submit" class="w-full sm:w-auto" wire:loading.attr="disabled" wire:target="submit">
                                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                                    {{ __('Verstuur bericht', 'sage') }}
                                    @svg('resources.images.icons.arrow-right', 'w-4 h-4')
                                </span>
                                <span wire:loading.flex wire:target="submit" class="items-center gap-2">
                                    @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
                                    {{ __('Bezig...', 'sage') }}
                                </span>
                            </x-button>
                        </div>

                        {{-- General Form Error --}}
                        @error('form')
                            <x-alert type="warning">
                                <div class="flex items-center gap-3">
                                    @svg('resources.images.icons.alert-circle', 'w-5 h-5 text-red-500 shrink-0')
                                    <p class="text-red-600 text-sm">{{ $message }}</p>
                                </div>
                            </x-alert>
                        @enderror
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@pushonce('scripts')
<script>
    function contactForm() {
        return {
            ...formValidator({
                form: @json($form),
                rules: @json($form->rules()),
                messages: @json($form->messages())
            })
        };
    }
</script>
@endpushonce
