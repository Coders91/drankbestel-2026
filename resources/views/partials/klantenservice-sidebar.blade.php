@php
  $currentPostId = is_singular('klantenservice') ? get_the_ID() : null;
  $sidebarPosts = get_posts([
    'post_type' => 'klantenservice',
    'posts_per_page' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC',
  ]);
@endphp

<nav class="w-full lg:w-72 shrink-0">
  <div class="sticky top-24 bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="p-4 bg-gray-50 border-b border-gray-200">
      <h2 class="font-semibold text-gray-900">{{ __('Onderwerpen', 'sage') }}</h2>
    </div>
    <ul class="divide-y divide-gray-100">
      @foreach($sidebarPosts as $sidebarPost)
        @php
          $isActive = $currentPostId === $sidebarPost->ID;
        @endphp
        <li>
          <a
            href="{{ get_permalink($sidebarPost) }}"
            @class([
              'flex items-center gap-3 px-4 py-3 text-sm transition-colors',
              'bg-primary-50 text-primary-700 font-medium' => $isActive,
              'text-gray-700 hover:bg-gray-50' => !$isActive,
            ])
          >
            @if($isActive)
              @svg('resources.images.icons.chevron-right', 'w-4 h-4 shrink-0')
            @endif
            <span>{{ $sidebarPost->post_title }}</span>
          </a>
        </li>
      @endforeach
    </ul>
  </div>
</nav>
