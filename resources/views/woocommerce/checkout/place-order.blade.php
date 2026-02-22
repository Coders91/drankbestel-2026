<x-button
  type="submit"
  class="flex items-center"
  wire:loading.attr="disabled"
  wire:loading.class="opacity-50 cursor-not-allowed"
  wire:target="save"
>
  <span wire:loading.remove wire:target="save">Bestelling plaatsen</span>
  <span wire:loading.flex wire:target="save" class="items-center gap-2">
          @svg('resources.images.icons.loader', 'animate-spin h-4 w-4')
          Bestelling plaatsen..
        </span>
</x-button>
