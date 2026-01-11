{{-- List Article Content --}}
<div class="article-list">
  {{-- List Variant Badge --}}
  @if($listVariant)
    <div class="mb-8">
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
        @switch($listVariant)
          @case('best')
            bg-amber-100 text-amber-800
            @break
          @case('cheapest')
            bg-green-100 text-green-800
            @break
          @case('seasonal')
            bg-blue-100 text-blue-800
            @break
          @default
            bg-gray-100 text-gray-800
        @endswitch
      ">
        @switch($listVariant)
          @case('best')
            Beste keuze
            @break
          @case('cheapest')
            Budget keuze
            @break
          @case('seasonal')
            Seizoen selectie
            @break
          @default
            Top selectie
        @endswitch
      </span>
    </div>
  @endif

  {{-- Intro Content --}}
  @if($article->post_content)
    <div class="prose prose-lg max-w-none mb-12">
      {!! apply_filters('the_content', $article->post_content) !!}
    </div>
  @endif

  {{-- List Items --}}
  @if(count($listItems ?? []) > 0)
    <div class="space-y-8">
      @foreach($listItems as $index => $item)
        <x-list-item
          :position="$item['position'] ?? ($index + 1)"
          :product="$item['product']"
          :reason="$item['reason'] ?? ''"
          :criteria="$item['criteria'] ?? ''"
          :prosCons="$item['pros_cons'] ?? []"
        />
      @endforeach
    </div>
  @endif
</div>
