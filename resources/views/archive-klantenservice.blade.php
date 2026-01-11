<x-layouts.app>
    <div class="container pt-6">
      <x-page-header class="mb-4" title="Klantenservice" />
      <p class="mt-4 text-gray-600">{{ __('Veelgestelde vragen en antwoorden', 'sage') }}</p>
    </div>
  <div class="container py-10">
    <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
      {{-- Sidebar --}}
      @include('partials.klantenservice-sidebar')

      {{-- Main content --}}
      <div class="flex-1 min-w-0">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Veelgestelde vragen', 'sage') }}</h2>

        @php
          $featuredFaqs = [];
          $servicePosts = get_posts([
            'post_type' => 'klantenservice',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
          ]);

          foreach ($servicePosts as $servicePost) {
            if (have_rows('accordion', $servicePost->ID)) {
              while (have_rows('accordion', $servicePost->ID)) {
                the_row();
                $isFeatured = get_sub_field('featured');
                if ($isFeatured) {
                  $featuredFaqs[] = [
                    'question' => get_sub_field('question'),
                    'answer' => get_sub_field('answer', false, false),
                    'category' => $servicePost->post_title,
                    'category_link' => get_permalink($servicePost),
                  ];
                }
              }
            }
          }
        @endphp

        @if(count($featuredFaqs) > 0)
          <x-accordion-group class="max-w-screen-md">
            @foreach($featuredFaqs as $index => $faq)
              <x-accordion :id="'faq-' . $index">
                <x-slot:title class="text-base font-medium text-gray-900">
                  {{ $faq['question'] }}
                </x-slot:title>
                <div class="pt-4 prose prose-sm max-w-none text-gray-600">
                  {!! $faq['answer'] !!}
                  <p class="mt-4 text-sm">
                    <a href="{{ $faq['category_link'] }}" class="text-primary-600 hover:text-primary-700">
                      {{ __('Meer over', 'sage') }} {{ $faq['category'] }} &rarr;
                    </a>
                  </p>
                </div>
              </x-accordion>
            @endforeach
          </x-accordion-group>
        @else
          <p class="text-gray-600">{{ __('Geen veelgestelde vragen gevonden.', 'sage') }}</p>
        @endif
      </div>
    </div>
  </div>
</x-layouts.app>
