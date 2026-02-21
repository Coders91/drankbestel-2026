<div class="grid gap-4">
  @include('partials.address-fields', ['type' => 'billing'])

  <x-forms.field-group class="mt-1 cursor-pointer">
    <x-forms.checkbox   id="ship_to_different_address"
                  name="ship_to_different_address"
                  @change="form.ship_to_different_address = $event.target.checked"
                  wire:model.boolean.live="form.ship_to_different_address"
    >
      Verzenden naar een ander adres?
    </x-forms.checkbox>
  </x-forms.field-group>

  {{-- Shipping fields  --}}
  <div class="grid gap-4" x-show="$wire.get('form.ship_to_different_address')" x-cloak>

    <x-forms.field-group class="flex gap-4">

      <div class="flex-1">
        <x-form-field id="shipping_first_name"
                      name="shipping_first_name"
                      wire:model.live.blur="form.shipping_first_name"
                      placeholder="Voornaam"
                      autocomplete="given-name"
                      @input="touched[$el.name] = true"
                      @blur="validateField($el)"
        >
          Voornaam
        </x-form-field>
      </div>

      <div class="flex-1">
        <x-form-field id="shipping_last_name"
                      name="shipping_last_name"
                      wire:model.live.blur="form.shipping_last_name"
                      placeholder="Achternaam"
                      autocomplete="family-name"
                      @input="touched[$el.name] = true"
                      @blur="validateField($el)"
        >
          Achternaam
        </x-form-field>
      </div>

    </x-forms.field-group>

    @include('partials.address-fields', ['type' => 'shipping'])

  </div>

</div>
