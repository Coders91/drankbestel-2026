<x-app :header="false" :breadcrumbs="false" :hero="false" sidebar="primary">
  @while(have_posts()) @php(the_post())
  <livewire:checkout />
  @endwhile
</x-app>
