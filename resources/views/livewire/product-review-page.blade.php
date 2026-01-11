<x-section>
  <div class="max-w-2xl mx-auto">
    {{-- Product Info Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
      <div class="flex gap-6">
        {{-- Product Image --}}
        <div class="shrink-0 w-24 h-24 bg-gray-50 rounded-lg overflow-hidden">
          <x-image
            :id="$product->imageId"
            size="thumbnail"
            class="w-full h-full object-contain"
          />
        </div>

        {{-- Product Details --}}
        <div class="flex-1 min-w-0">
          <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $product->name }}</h1>
          @if ($product->contents)
            <p class="text-gray-600 mb-2">{{ $product->contents }}</p>
          @endif
          @if ($product->reviewCount > 0)
            <div class="flex items-center gap-2">
              <x-star-rating :rating="$product->rating" size="sm" />
              <span class="text-sm text-gray-500">
                ({{ $product->reviewCount }} {{ $product->reviewCount === 1 ? __('review', 'sage') : __('reviews', 'sage') }})
              </span>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Review Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      @if ($submitted)
        <div class="text-center py-8">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">
            {{ __('Bedankt voor je review!', 'sage') }}
          </h2>
          <p class="text-gray-600 mb-6">
            {{ __('Je review wordt na goedkeuring geplaatst op de productpagina.', 'sage') }}
          </p>
          <a href="{{ $product->url }}" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-medium">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('Terug naar product', 'sage') }}
          </a>
        </div>
      @else
        <div x-data="reviewPageForm()">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
          {{ __('Schrijf een review', 'sage') }}
        </h2>

        <form class="space-y-6" @submit.prevent="if(validateAll()) $wire.submit()">
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
                  x-bind:class="{'border-red-600': errors.author}"
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
                  x-bind:class="{'border-red-600': errors.email}"
                  @input="markTouched('email')"
                  @blur="validateField($el)"
                />
                <p class="text-xs text-gray-500 mt-1">{{ __('Je e-mailadres wordt niet getoond bij de review.', 'sage') }}</p>
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
              rows="5"
              placeholder="{{ __('Vertel ons wat je van dit product vindt...', 'sage') }}"
              class="mt-1"
              x-bind:class="{'border-red-600': errors.content}"
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

          <div class="flex flex-col sm:flex-row gap-4">
            <x-button type="submit" wire:loading.attr="disabled" wire:target="submit" class="flex-1 sm:flex-none">
              <span wire:loading.remove wire:target="submit">{{ __('Review plaatsen', 'sage') }}</span>
              <span wire:loading wire:target="submit" class="flex items-center justify-center gap-2">
                @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
                {{ __('Bezig...', 'sage') }}
              </span>
            </x-button>

            <a href="{{ $product->url }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 text-gray-600 hover:text-gray-900 font-medium transition-colors">
              {{ __('Annuleren', 'sage') }}
            </a>
          </div>

          @error('form')
            <p class="text-red-600 text-sm">{{ $message }}</p>
          @enderror
        </form>
        </div>
      @endif
    </div>
  </div>
</x-section>

@pushonce('scripts')
<script>
    function reviewPageForm() {
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
