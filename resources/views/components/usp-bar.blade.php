@props([
  'usps' => [],
])

<div
  {{ $attributes->merge(['class' => 'py-1.5']) }}
>
  <div class="container mx-auto">
    <ul class="flex gap-10">
        @foreach ($usps as $usp)
            <li class="flex items-center gap-2">
              @svg('resources.images.icons.check', 'size-5 stroke-green-600')
              <span>{{ $usp }}</span>
            </li>
        @endforeach
    </ul>
  </div>
</div>
