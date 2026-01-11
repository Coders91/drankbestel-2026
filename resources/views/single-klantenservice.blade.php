<x-layouts.app>
  <div class="container pt-6">
    <x-page-header class="mb-4" :title="get_the_title()" />
  </div>
  <div class="container py-10">
    <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
      {{-- Sidebar --}}
      @include('partials.klantenservice-sidebar')

      {{-- Main content --}}
      <div class="flex-1 min-w-0">
        @while(have_posts()) @php(the_post())
          @if(have_rows('accordion'))
            <x-accordion-group class="max-w-screen-md">
              @while(have_rows('accordion'))
                @php(the_row())
                @php($question = get_sub_field('question'))
                @php($answer = get_sub_field('answer', false, false))
                @php($uniqueId = 'faq-' . get_the_ID() . '-' . get_row_index())

                <x-accordion :id="$uniqueId">
                  <x-slot:title class="text-base font-medium text-gray-900">
                    {{ $question }}
                  </x-slot:title>
                  <div class="pt-4 prose prose-sm max-w-none text-gray-600">
                    {!! $answer !!}
                  </div>
                </x-accordion>
              @endwhile
            </x-accordion-group>
          @else
            <p class="text-gray-600">{{ __('Geen vragen gevonden voor dit onderwerp.', 'sage') }}</p>
          @endif
        @endwhile
      </div>
    </div>
  </div>
</x-layouts.app>
