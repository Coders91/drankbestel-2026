@props([
    'id' => '',
    'name' => '',
    'disabled' => false,
    'withErrors' => true,
])

<label for="{{ $id }}" class="flex items-start gap-2.5 w-fit text-base has-disabled:cursor-not-allowed has-disabled:opacity-75">
    <span class="relative flex items-center mt-0.5">
        <input
          type="checkbox"
          id="{{ $id }}"
          name={{ $name }}
          class="before:content[''] peer relative size-5 appearance-none overflow-hidden rounded
          border border-gray-800 bg-white before:absolute before:inset-0
          checked:border-gray-800 checked:before:bg-white focus:outline-2 focus:outline-offset-2
          flex-shrink-0 active:outline-offset-0 disabled:cursor-not-allowed"
          {{ $attributes }}
          />
      <svg width="24" height="24" class="pointer-events-none invisible absolute left-1/2 top-1/2 size-4 -translate-x-1/2 -translate-y-1/2 peer-checked:visible text-green-700" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M9.9997 15.1709L19.1921 5.97852L20.6063 7.39273L9.9997 17.9993L3.63574 11.6354L5.04996 10.2212L9.9997 15.1709Z"></path></svg>
    </span>
  {{ $slot }}
</label>
@if($withErrors)
  <template x-if="errors.{{ $name }}">
    <p class="text-red-600 text-sm mt-1" x-text="errors.{{ $name }}"></p>
  </template>
@endif
