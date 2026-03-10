<div x-data="newsletterForm()">
    @if ($submitted)
        <x-alert type="success">
            <div class="flex items-center gap-3">
                @svg('resources.images.icons.check', 'w-5 h-5 text-green-600 shrink-0')
                <p class="text-green-700 text-sm">
                    {{ __('Bedankt voor je aanmelding!', 'sage') }}
                </p>
            </div>
        </x-alert>
    @else
        <form
            class="flex gap-2"
            @submit.prevent="if(validateAll('newsletterForm')) $wire.submit()"
            id="newsletterForm"
        >
            <div class="flex-1 relative">
                <input
                    type="email"
                    id="email"
                    name="email"
                    wire:model="form.email"
                    placeholder="{{ __('Uw e-mailadres', 'sage') }}"
                    class="w-full py-3 px-4 bg-gray-800/80 border border-gray-700/50 rounded-lg text-white placeholder-gray-500 text-sm focus:outline-none focus:border-red-600/50 focus:ring-1 focus:ring-red-600/30 transition-all duration-200"
                    x-bind:class="{'border-red-500 focus:border-red-500 focus:ring-red-500/30': errors.email}"
                    @input="markTouched('email')"
                    @blur="validateField($el)"
                >
            </div>
            <x-button
                type="submit"
                class="relative"
                wire:loading.attr="disabled"
                wire:target="submit"
            >
                <span wire:loading.class="invisible" wire:target="submit">
                    {{ __('Aanmelden', 'sage') }}
                </span>
                <span wire:loading.flex wire:target="submit" x-cloak class="absolute inset-0 items-center justify-center">
                    @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
                </span>
            </x-button>
        </form>

        {{-- Error messages --}}
        <template x-if="errors.email">
            <p x-text="errors.email" class="text-red-400 text-sm mt-2"></p>
        </template>
        @error('form.email')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
        @error('form')
            <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
        @enderror
    @endif
</div>

@pushonce('scripts')
<script>
    function newsletterForm() {
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
