@if (count($crumbs) > 1)
  <nav aria-label="Breadcrumb">
    <ol class="flex gap-2 text-sm">
      @foreach ($crumbs as $crumb)
        <li class="flex items-center gap-2">
          @if (!$loop->last && !empty($crumb['url']))
            <a href="{{ $crumb['url'] }}" class="no-underline">
              @if ($loop->first)
                {!! $crumb['name'] !!}
                <span class="sr-only">{{ __('Home', 'sage') }}</span>
              @else
                {{ $crumb['name'] }}
              @endif
            </a>
            @svg('resources.images.icons.site.chevron-right', 'size-4 stroke-gray-400')
          @else
            <span class="text-gray-900 font-medium" aria-current="page">{{ $crumb['name'] }}</span>
          @endif
        </li>
      @endforeach
    </ol>
  </nav>
@endif
