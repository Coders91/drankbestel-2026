<x-field-group class="flex gap-4">
  <div class="flex-1">
    <x-form-field id="{{ $type }}_postcode"
                  name="{{ $type }}_postcode"
                  wire:model.blur="form.{{ $type }}_postcode"
                  placeholder="1234AB"
                  autocomplete="postal-code"
                  x-bind:disabled="{{ $type }}_loading"
                  @input="touched[$el.name] = true"
                  @blur="getAddressData('{{ $type }}', $el)"
    >
      Postcode
    </x-form-field>
  </div>
  <div class="basis-1/3">
    <x-form-field id="{{ $type }}_house_number"
                  name="{{ $type }}_house_number"
                  wire:model.blur="form.{{ $type }}_house_number"
                  placeholder="123"
                  x-bind:disabled="{{ $type }}_loading"
                  @input="touched[$el.name] = true"
                  @blur="getAddressData('{{ $type }}', $el)"
    >
      Huisnr.
    </x-form-field>
  </div>
  <div class="basis-1/4">
    <x-form-field id="{{ $type }}_house_number_suffix"
                  name="{{ $type }}_house_number_suffix"
                  wire:model.blur="form.{{ $type }}_house_number_suffix"
                  placeholder="A"
                  x-bind:disabled="{{ $type }}_loading"
                  @input="touched[$el.name] = true"
                  @blur="getAddressData('{{ $type }}', $el)"
    >
      Toev.
    </x-form-field>
  </div>
</x-field-group>
<div x-show="{{ $type }}_loading" x-cloak class="text-sm mt-2">
  <span>Laden...</span>
</div>
@if(!empty($messages[$type . '_address']))
  <p class="text-sm text-red-600" wire:key="message-{{ $type }}">{{ $messages[$type . '_address'] }}</p>
@endif
<x-field-group class="flex gap-4">
  <div class="flex-1">
    <x-form-field readonly
                  :readonly="$this->isAddressReadOnly($type)"
                  class="read-only:bg-gray-100 read-only:border-gray-200 read-only:pointer-events-none read-only:text-gray-700"
                  id="{{ $type }}_street_name"
                  name="{{ $type }}_street_name"
                  wire:model.blur="form.{{ $type }}_street_name"
                  placeholder="Straat"
                  @input="touched[$el.name] = true;"
                  @blur="validateField($el)"
    >
      Straat
    </x-form-field>
  </div>
  <div class="flex-1">
    <x-form-field readonly
                  :readonly="$this->isAddressReadOnly($type)"
                  class="read-only:bg-gray-100 read-only:border-gray-200 read-only:pointer-events-none read-only:text-gray-700"
                  id="{{ $type }}_city"
                  name="{{ $type }}_city"
                  wire:model.blur="form.{{ $type }}_city"
                  placeholder="Plaats"
                  @input="touched[$el.name] = true"
                  @blur="validateField($el)"
    >
      Plaats
    </x-form-field>
  </div>
</x-field-group>
