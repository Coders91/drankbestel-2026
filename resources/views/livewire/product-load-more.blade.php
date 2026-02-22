<div
    x-data
    x-init="
        const match = location.hash.match(/^#page-(\d+)$/);
        if (match && parseInt(match[1]) > 1) {
            $wire.loadUpToPage(parseInt(match[1]));
        }

        Livewire.on('page-updated', ({ page }) => {
            history.replaceState(null, '', '#page-' + page);
        });
    "
>
    @if (count($additionalProducts) > 0)
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mt-4 lg:mt-6">
            @foreach ($additionalProducts as $product)
                <x-product :product="$product" wire:key="product-{{ $product->id }}" />
            @endforeach
        </div>
    @endif

    @if ($hasMore)
        <div class="flex justify-end items-center gap-4 mt-8">
            <p class="text-sm text-gray-700">
                {{ sprintf(__('%d van %d producten bekeken', 'sage'), $shownCount, $totalProducts) }}
            </p>
            <x-button
                type="button"
                class="max-sm:w-full"
                wire:click="loadMore"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-wait"
                variant="secondary"
            >
                <span wire:loading.remove wire:target="loadMore">
                    {{ __('Meer laden', 'sage') }}
                </span>
                <span wire:loading.flex wire:target="loadMore" class="flex items-center gap-2">
                    @svg('resources.images.icons.loader', 'size-5 animate-spin')
                    {{ __('Laden...', 'sage') }}
                </span>
            </x-button>
        </div>
    @endif
</div>
