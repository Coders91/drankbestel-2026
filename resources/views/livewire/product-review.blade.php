<div x-data="reviewForm()">
    @if ($submitted)
        <x-alert type="success">
            <div class="flex items-center gap-3">
                @svg('resources.images.icons.check-circle', 'w-5 h-5 text-green-500 shrink-0')
                <p class="text-green-700 font-medium">
                    {{ __('Bedankt voor je review! Deze wordt na goedkeuring geplaatst.', 'sage') }}
                </p>
            </div>
        </x-alert>
    @else
        <form class="space-y-6" @submit.prevent="if(validateAll('reviewForm')) $wire.submit()" id="reviewForm">
            <h2 class="text-xl font-semibold font-heading text-gray-900">
                {{ __('Schrijf een review', 'sage') }}
            </h2>

            {{-- Rating Input --}}
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Je beoordeling', 'sage') }} <span class="text-red-600">*</span></label>
                <x-star-rating-input
                    wire:model="form.rating"
                    name="rating"
                    class="mt-2"

                    x-on:input="form.rating = $event.detail; markTouched('rating'); validateField('rating')"
                />
                <template x-if="errors.rating">
                    <p x-text="errors.rating" class="text-red-600 text-sm mt-2"></p>
                </template>
                @error('form.rating')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name & Email (only for non-logged-in users) --}}
            @unless ($isLoggedIn)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700">{{ __('Naam', 'sage') }} <span class="text-red-600">*</span></label>
                        <x-forms.input-text
                            id="author"
                            name="author"
                            wire:model="form.author"
                            placeholder="{{ __('Je naam', 'sage') }}"
                            class="mt-1"
                            x-bind:class="{'border-red-600 focus:ring-red-600': errors.author}"
                            @input="markTouched('author')"
                            @blur="validateField($el)"
                        />
                        <template x-if="errors.author">
                            <p x-text="errors.author" class="text-red-600 text-sm mt-2"></p>
                        </template>
                        @error('form.author')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('E-mailadres', 'sage') }} <span class="text-red-600">*</span></label>
                        <x-forms.input-text
                            id="email"
                            name="email"
                            type="email"
                            wire:model="form.email"
                            placeholder="{{ __('je@email.nl', 'sage') }}"
                            class="mt-1"
                            x-bind:class="{'border-red-600 focus:ring-red-600': errors.email}"
                            @input="markTouched('email')"
                            @blur="validateField($el)"
                        />
                        <template x-if="errors.email">
                            <p x-text="errors.email" class="text-red-600 text-sm mt-2"></p>
                        </template>
                        @error('form.email')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endunless

            {{-- Review Content --}}
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">{{ __('Je review', 'sage') }} <span class="text-red-600">*</span></label>
                <x-forms.text-area
                    id="content"
                    name="content"
                    wire:model="form.content"
                    rows="4"
                    placeholder="{{ __('Wat vind je van dit product?', 'sage') }}"
                    class="mt-1"
                    x-bind:class="{'border-red-600 focus:ring-red-600': errors.content}"
                    @input="markTouched('content')"
                    @blur="validateField($el)"
                />
                <template x-if="errors.content">
                    <p x-text="errors.content" class="text-red-600 text-sm mt-2"></p>
                </template>
                @error('form.content')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <x-button type="submit" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">{{ __('Review plaatsen', 'sage') }}</span>
                <span wire:loading.flex wire:target="submit" class="items-center gap-2">
                    @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
                    {{ __('Bezig...', 'sage') }}
                </span>
            </x-button>

            @error('form')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </form>
    @endif
</div>

@pushonce('scripts')
<script>
    function reviewForm() {
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
