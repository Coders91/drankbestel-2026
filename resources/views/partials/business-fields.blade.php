<div class="grid gap-4" x-show="form.is_business_order" x-cloak>
  <x-forms.field-group class="flex gap-4">
    <div class="flex-1">
      <x-form-field
                    id="billing_company"
                    name="billing_company"
                    wire:model.live.blur="form.billing_company"
                    placeholder="Bedrijfsnaam"
                    @blur="validateField($el)"
      >
        Bedrijfsnaam
      </x-form-field>
    </div>
    <div class="flex-1">
      <x-form-field
                      id="vat_number"
                      name="vat_number"
                      wire:model.live.blur="form.vat_number"
                      placeholder="NL123456789B01"
                      @blur="validateField($el)"
        >
          BTW-nummer
        </x-form-field>
    </div>
  </x-forms.field-group>
  <div>
    <x-form-field id="customer_reference"
                  name="customer_reference"
                  wire:model.live.blur="form.customer_reference"
                  placeholder="Referentie"
                  @blur="validateField($el)"
    >
      Referentie
    </x-form-field>
  </div>
</div>
