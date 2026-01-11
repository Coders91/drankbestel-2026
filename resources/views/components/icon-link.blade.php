@props([
  'icon' => 'arrow-left',
])

<a {{ $attributes->merge(['class' => 'flex items-center gap-2 text-gray-700 hover:text-red-600 transition-colors']) }}>
    @svg('resources.images.icons.' . $icon) {{ $slot ?? 'Verder winkelen' }}
</a>
