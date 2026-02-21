@php
  /** @var App\View\Models\Order $order */
@endphp

<x-layouts.app :header="false" :hero="false" :breadcrumbs="false">
  <section class="py-12">
    <div class="container mx-auto max-w-screen-lg px-4">
      <div class="flex justify-center mb-6">
        @svg('resources.images.icons.check-circle')
      </div>
      <h1 class="font-heading text-center text-4xl text-gray-900 font-semibold mb-6">Proost! Je bestelling is gelukt </h1>
      <p class="text-center mb-8 text-gray-900 text-lg max-w-lg mx-auto">
        Hartelijk dank voor je bestelling met bestelnummer <span class="font-semibold">{{ $order->number }}</span>. Ter bevestiging sturen we een e-mail naar <span
          class="font-semibold text-gray-900">{{ $order->customer_email }}</span>. Je
        hebt betaald
        met <span class="font-semibold text-gray-900">{{ $order->payment_method_title }}</span>.
      </p>

      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="font-heading text-2xl font-semibold text-rhino-800">Bestelling #{{ $order->number }}</h2>
      </div>

      <div class="flex flex-wrap items-center gap-2 pb-6">
        <span class="text-gray-700 text-sm">Geschatte bezorgdatum: {{ $order->expected_delivery_date }}</span>
      </div>

      <div class="mb-6 border-b border-gray-100">
      @foreach($order->items as $id => $item)
        <div class="py-6 border-t border-gray-100">
          <div class="flex gap-6">
            <div class="w-30 h-30 flex-shrink-0 flex items-center justify-center overflow-hidden">
              <x-image :id="$item->product->imageId" class="p-2 bg-gray-50 mix-blend-multiply max-w-full max-h-full object-contain rounded-xl" />
            </div>
            <div class="flex-1 flex flex-wrap justify-between mt-2 gap-y-1.5">
              <h2 class="text-gray-800 text-lg font-medium">{{ $item->quantity . 'x ' . $item->product->title }}</h2>
              <p class="text-gray-900 text-xl font-bold whitespace-nowrap">{{ $item->subtotal->formatted() }}</p>
            </div>
          </div>
        </div>
      @endforeach
      </div>
      <div class="rounded-xl py-4 px-6 flex items-center justify-between flex-wrap">
        <p class="text-gray-800 text-lg font-medium">Subtotaal ({{ $order->total_quantity }})</p>
        <p class="text-gray-700 font-heading text-xl font-semibold">{{ $order->subtotal->formatted() }}</p>
      </div>
      <div class="bg-gray-50 rounded-xl py-4 px-6 flex items-center justify-between flex-wrap">
        <p class="text-gray-800 text-lg font-medium">Verzending</p>
        <p class="text-gray-700 font-heading text-xl font-semibold">{{ $order->shipping_total->formatted() }}</p>
      </div>
      <div class="py-4 px-6 mb-6 flex items-center justify-between flex-wrap mb-6">
        <p class="text-gray-900 text-lg font-medium">Totaal</p>
        <p class="text-gray-900 font-heading text-xl font-semibold">{{ $order->total->formatted() }}</p>
      </div>
    <div class="flex justify-end">
      <x-button href="{{ home_url() }}">Verder winkelen</x-button>
    </div>
    </div>
  </section>
</x-layouts.app>
