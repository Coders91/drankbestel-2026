@if (count($crumbs) > 1)
  <nav aria-label="Breadcrumb">
    <ol class="flex gap-2 text-sm">
      @foreach ($crumbs as $crumb)
        <li class="flex items-center gap-2 *:hover:text-red-600">
          @if (!$loop->last && !empty($crumb['url']))
            <a href="{{ $crumb['url'] }}" class="no-underline whitespace-nowrap text-gray-700">
                {{ $crumb['name'] }}
            </a>
            @svg('resources.images.icons.chevron-right', 'size-4 stroke-gray-700')
          @else
            <span class="text-gray-900 font-medium whitespace-nowrap" aria-current="page">{{ $crumb['name'] }}</span>
          @endif
        </li>
      @endforeach
    </ol>
  </nav>
@endif
