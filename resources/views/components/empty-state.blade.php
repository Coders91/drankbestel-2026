@props(['icon', 'title', 'description'])

<div {{ $attributes->merge(['class' => 'border-t-2 border-red-600 pt-12 pb-16 text-center']) }}>
  <h2 class="text-2xl font-bold font-heading mb-3 flex items-center justify-center gap-2">
    @svg('resources.images.icons.' . $icon, 'size-6 text-gray-400')
    {{ $title }}
  </h2>
  <p class="text-gray-500 mb-8 max-w-md mx-auto">{{ $description }}</p>
  {{ $action }}
</div>
