<div class="space-y-4" >
  @foreach($payment_methods as $gateway)
    @if($gateway->enabled === 'yes')
      <label
        for="payment_{{ $gateway->id }}"
        class="block w-full border rounded-lg p-4 cursor-pointer border-gray-200 has-checked:border-red-600 has-checked:border-2 has-checked:p-[14px]"
        data-payment-gateway="{{ $gateway->id }}"
      >
        <x-forms.radio
          id="payment_{{ $gateway->id }}"
          name="payment_method"
          value="{{ $gateway->id }}"
          wire:model="form.payment_method"
          class="border-gray-300"
        >
          <span>{{ $gateway->title }}</span>
          <span class="ml-auto">{!! $gateway->icon !!}</span>
        </x-forms.radio>

        @if($gateway->has_fields)
          <div x-show="$wire.get('form.payment_method') === '{{ $gateway->id }}'" x-cloak x-transition.opacity.200ms class="mt-4 text-left" @click.stop>
            {!! $gateway->payment_fields() !!}
          </div>
        @endif
      </label>
    @endif
  @endforeach
</div>

@error('form.payment_method')
  <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
@enderror
