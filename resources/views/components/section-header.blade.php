@props([
  'highlight' => '',
  'title' => '',
  'description' => '',
  'centered' => false,
])

@if(trim($highlight) || trim($title) || trim($description))
  <div
    @class([
      'grid',
      'place-items-center text-center' => $centered,
    ])
  >
    @if($highlight)
      <span class="block mb-1 text-sm font-heading">{{ $highlight }}</span>
    @endif
    @if($title)
      <h2 class="display-2">{!! $title !!}</h2>
    @endif
    @if($description)
      <p class="mt-4 text-gray-600">{!!  $description !!}</p>
    @endif
  </div>
@endif
