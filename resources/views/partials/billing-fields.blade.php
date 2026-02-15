<div class="grid gap-4">
  <h3 class="text-lg font-semibold mb-4" x-show="form.is_business_order" x-cloak>Persoonsgegevens</h3>
  <x-field-group class="flex gap-4">
    {{-- First name --}}
    <div class="flex-1">
      <x-form-field id="billing_first_name"
                    name="billing_first_name"
                    wire:model.live.blur="form.billing_first_name"
                    placeholder="Voornaam"
                    autocomplete="given-name"
                    @input="touched[$el.name] = true"
                    @blur="validateField($el)"
      >
        Voornaam
      </x-form-field>
    </div>

    {{-- Last name --}}
    <div class="flex-1">
      <x-form-field id="billing_last_name"
                    name="billing_last_name"
                    wire:model.live.blur="form.billing_last_name"
                    placeholder="Achternaam"
                    autocomplete="family-name"
                    @input="touched[$el.name] = true"
                    @blur="validateField($el)"
      >
        Achternaam
      </x-form-field>
    </div>

  </x-field-group>

  {{-- E-mail --}}
  <x-field-group>
    <x-form-field id="billing_email"
                  name="billing_email"
                  wire:model.live.blur="form.billing_email"
                  placeholder="E-mailadres"
                  autocomplete="email"
                  @input="touched[$el.name] = true"
                  @blur="validateField($el)"
    >
      E-mailadres
    </x-form-field>
  </x-field-group>

  {{-- Phone --}}
  <x-field-group>
    <x-form-field id="billing_phone"
                  name="billing_phone"
                  wire:model.live.blur="form.billing_phone"
                  type="tel"
                  placeholder="06123456789"
                  autocomplete="tel"
                  @input="touched[$el.name] = true"
                  @blur="validateField($el)"
    >
      Telefoonnummer
    </x-form-field>
  </x-field-group>
</div>
