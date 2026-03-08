<div class="flex items-start justify-between gap-2 py-2">

  <div class="flex gap-4">
    <div class="shrink-0 size-18 bg-gray-50 rounded-lg flex items-center justify-center">
      <x-image
        class="w-full h-full object-contain p-1 rounded-lg"
        size="thumbnail"
        alt="{{ $item->product->title }}"
        id="{{ $item->product->imageId }}"
      />
    </div>
    <div class="w-full">
      <h3 class="font-medium text-gray-900 line-clamp-2">{!! $item->product->name !!}</h3>
      @if ($item->product->contents)
        <p class="text-sm leading-6 text-gray-600">{{ $item->product->contents }}</p>
      @endif
    </div>
  </div>
  <div class="flex flex-col gap-1 items-end justify-end">
    <span class="text-sm leading-6 text-gray-800">{{ $item->quantity }}x</span>
    <span class="text-gray-900 font-medium">{{ $item->lineSubtotal->formatted() }}</span>
  </div>
</div>
