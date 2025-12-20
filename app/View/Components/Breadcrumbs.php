<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Closure;

class Breadcrumbs extends Component
{
    /**
     * The breadcrumb items.
     *
     * @var array
     */
    public array $crumbs = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->crumbs = $this->generate_breadcrumbs();
    }

    /**
     * Generate the breadcrumbs.
     *
     * @return array
     */
    protected function generate_breadcrumbs(): array
    {
        $url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', $url_path))); // remove empty segments
        $home_url = home_url('/');

        $crumbs = [
            [
                'name' => get_svg('resources.images.icons.site.home', 'w-5 h-5 stroke-gray-500', ['aria-hidden' => 'true']),
                'url'  => $home_url,
            ],
        ];

        $path = '';
        $total_segments = count($segments);

        foreach ($segments as $i => $segment) {
            // Handle pagination URLs
            if ($segment === 'page' && isset($segments[$i + 1]) && is_numeric($segments[$i + 1])) {
                $page_num = $segments[$i + 1];

                $display_title = 'Pagina ' . $page_num;

                $crumbs[] = [
                    'name' => $display_title,
                    'url'  => null,
                ];
                break;
            }

            $path .= $segment . '/';
            $is_last = ($i === $total_segments - 1);

            $display_title = ucfirst(str_replace('-', ' ', $segment));

            if ($is_last && !is_archive() && get_the_title() !== '') {
                $display_title = get_the_title();
            }

            $crumbs[] = [
                'name' => $display_title,
                'url'  => $is_last ? null : trailingslashit($home_url . $path),
            ];
        }

        return $crumbs;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|\Closure|string
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumbs');
    }
}
