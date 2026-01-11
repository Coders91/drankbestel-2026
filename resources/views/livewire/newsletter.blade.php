<div x-data="newsletterForm()">
    @if ($submitted)
        <div class="flex items-center gap-3 p-4 bg-green-900/30 border border-green-700/50 rounded-lg">
            <svg class="w-5 h-5 text-green-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-green-300 text-sm">
                {{ __('Bedankt voor je aanmelding!', 'sage') }}
            </p>
        </div>
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
            <button
                type="submit"
                class="px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-heading font-semibold text-sm uppercase tracking-wide rounded-lg transition-all duration-200 hover:shadow-lg hover:shadow-red-600/20 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="submit"
            >
                <span wire:loading.remove wire:target="submit">
                    {{ __('Aanmelden', 'sage') }}
                </span>
                <span wire:loading wire:target="submit">
                    @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
                </span>
            </button>
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
