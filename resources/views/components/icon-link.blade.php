@props([
  'icon' => 'arrow-left',
])

<a {{ $attributes->merge(['class' => 'flex items-center gap-2 text-gray-700']) }}>
    @svg('resources.images.icons.' . $icon) {{ $slot ?? 'Verder winkelen' }}
</a>
