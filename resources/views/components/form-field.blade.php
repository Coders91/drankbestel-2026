<x-field-label for="{{ $attributes['name'] }}">{{ $slot }}</x-field-label>
<x-forms.input-text {{ $attributes }} x-bind:class="{'border-red-600': errors.{{ $attributes['name'] }}, 'border-gray-300': !errors.{{ $attributes['name'] }} }" />
<template x-if="errors.{{ $attributes['name'] }}">
  <p x-text="errors.{{ $attributes['name'] }}" class="text-red-600 font-medium text-sm mt-2"></p>
</template>
