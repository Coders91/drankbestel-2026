@props(['name' => 'rating', 'value' => 0])

<div
    x-data="{
        hovered: 0,
        selected: {{ $value }}
    }"
    {{ $attributes->merge(['class' => 'flex gap-1']) }}
    x-modelable="selected"
>
    @for ($i = 1; $i <= 5; $i++)
        <label
            @mouseenter="hovered = {{ $i }}"
            @mouseleave="hovered = 0"
            class="cursor-pointer focus-within:outline-none"
        >
            <input
                type="radio"
                name="{{ $name }}"
                value="{{ $i }}"
                class="sr-only"
                x-model="selected"
                @change="$dispatch('input', {{ $i }})"
            />
            <div
                class="transition-colors"
            >
                <template x-if="hovered >= {{ $i }} || (!hovered && selected >= {{ $i }})">
                    @svg('resources.images.icons.star-full', 'size-7')
                </template>
                <template x-if="!(hovered >= {{ $i }} || (!hovered && selected >= {{ $i }}))">
                    @svg('resources.images.icons.star-empty', 'size-7')
                </template>
            </div>
        </label>
    @endfor
</div>
