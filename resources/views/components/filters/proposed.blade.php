@props(['filters' => []])

@if(!empty($filters))
  <div
    id="proposed-filters"
    class="flex overflow-x-auto gap-2 pb-2 -mx-4 px-4 lg:mx-0 lg:px-0 scrollbar-hide"
  >
    @foreach($filters as $filter)
      <a
        href="{{ $filter['url'] }}"
        @click.prevent="applyFilter('{{ $filter['url'] }}')"
        @class([
          'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full whitespace-nowrap transition-all duration-200',
          'bg-gray-900 text-white hover:bg-gray-800' => $filter['active'],
          'bg-amber-50 text-amber-900 border border-amber-200 hover:bg-amber-100 hover:border-amber-300' => !$filter['active'],
        ])
      >
        <span>{{ $filter['name'] }}</span>
        <span @class([
          'inline-flex items-center justify-center min-w-5 h-5 px-1.5 text-xs font-semibold rounded-full',
          'bg-white/20 text-white' => $filter['active'],
          'bg-amber-200/60 text-amber-800' => !$filter['active'],
        ])>
          {{ $filter['count'] }}
        </span>
      </a>
    @endforeach
  </div>
@endif
