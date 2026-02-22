@props([
    'itemId' => null,
    'children' => [],
])

<div
    x-show="activeItem === {{ $itemId }}"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-1"
    x-on:mouseenter="megaMenuStay()"
    x-cloak
    class="absolute left-0 top-full z-50 min-w-[220px]"
>
    <div class="bg-white rounded-b-lg shadow-xl border border-t-0 border-gray-100 py-2">
        <ul>
            @foreach ($children as $child)
                @php
                    $linkClasses = 'block px-4 py-2.5 text-sm transition-colors';
                    $linkClasses .= $child->active
                        ? ' text-red-600 bg-red-50 font-medium'
                        : ' text-gray-700 hover:bg-gray-50 hover:text-red-600';
                @endphp
                <li>
                    <a href="{{ $child->url }}" class="{{ $linkClasses }}">
                        {{ $child->label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
