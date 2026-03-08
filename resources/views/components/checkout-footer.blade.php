@props([])

<footer {{ $attributes->merge(['class' => 'mt-12 lg:mt-16 border-t border-gray-100']) }}>
  <div class="py-8 grid gap-8">
    {{-- Help & contact --}}
    <div class="flex items-center justify-center gap-2 text-sm">
      <span class="text-gray-500">{{ __('Hulp nodig?', 'sage') }}</span>
      <a href="tel:{{ config('store.contact.phone') }}" class="font-medium text-gray-700 hover:text-gray-900 transition-colors">
        {{ config('store.contact.phone') }}
      </a>
      <span class="text-gray-300">&middot;</span>
      <span class="text-gray-500 text-xs">{{ __('Ma t/m Za 9:00 - 18:00', 'sage') }}</span>
    </div>

    {{-- Business details --}}
    <div class="text-center text-xs text-gray-500 leading-relaxed space-y-1">
      <p>
        {{ config('store.details.name') }}
        -
        {{ config('store.address.street') }}, {{ config('store.address.zipcode') }} {{ config('store.address.city') }}
      </p>
      <p>
        KVK: {{ config('store.details.kvk') }}
        &middot;
        BTW: {{ config('store.details.btw') }}
        &middot;
        {{ config('store.contact.email') }}
      </p>
    </div>
  </div>
</footer>
