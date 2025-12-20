<x-button
  type="submit"
  class="flex items-center"
  wire:loading.attr="disabled"
  wire:loading.class="opacity-50 cursor-not-allowed"
  wire:target="save"
>
  <span wire:loading.remove wire:target="save">Bestelling plaatsen</span>
  <span wire:loading wire:target="save" class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Bestelling plaatsen..
        </span>
</x-button>
