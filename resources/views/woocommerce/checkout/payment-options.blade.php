<div class="space-y-4" >
  @foreach($payment_methods as $gateway)
    @if($gateway->enabled === 'yes')
      <label
        for="payment_{{ $gateway->id }}"
        class="block w-full border-2 rounded-lg p-4 cursor-pointer border-gray-200 has-checked:border-gray-700"
      >
        <x-radio
          id="payment_{{ $gateway->id }}"
          name="payment_method"
          value="{{ $gateway->id }}"
          wire:model.defer="form.payment_method"
        >
          <span class="font-medium">{{ $gateway->title }}</span>
          {!! $gateway->icon !!}
        </x-radio>

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
