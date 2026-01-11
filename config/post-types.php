<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Post Types
    |--------------------------------------------------------------------------
    |
    | Post types to be registered with Extended CPTs
    | <https://github.com/johnbillion/extended-cpts>
    |
    */

    'post_types' => [
        'klantenservice' => [
            'enter_title_here' => 'Klantenservice',
            'menu_icon' => 'dashicons-list-view',
            'supports' => ['title', 'page-attributes'],
            'show_in_rest' => false,
            'hierarchical' => false,
            'has_archive' => true,
            'menu_position' => '5',
            'names' => [
                'singular' => 'Klantenservice',
                'plural' => 'Klantenservice',
            ],
            'rewrite' => [
                'slug' => 'klantenservice',
                'with_front' => false,
                'pages' => false,
            ]
        ],

        'article' => [
            'enter_title_here' => 'Artikel titel',
            'menu_icon' => 'dashicons-media-document',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions'],
            'show_in_rest' => true,
            'hierarchical' => false,
            'has_archive' => 'sterke-drank',
            'menu_position' => '6',
            'names' => [
                'singular' => 'Artikel',
                'plural' => 'Artikelen',
            ],
            'rewrite' => [
                'slug' => 'sterke-drank/%primary_cat%',
                'with_front' => false,
            ],
        ],

        'cocktail' => [
            'enter_title_here' => 'Cocktail naam',
            'menu_icon' => 'dashicons-coffee',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'show_in_rest' => true,
            'hierarchical' => false,
            'has_archive' => true,
            'menu_position' => '7',
            'names' => [
                'singular' => 'Cocktail',
                'plural' => 'Cocktails',
            ],
            'rewrite' => [
                'slug' => 'cocktails',
                'with_front' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Taxonomies
    |--------------------------------------------------------------------------
    |
    | Taxonomies to be registered with Extended CPTs library
    | <https://github.com/johnbillion/extended-cpts>
    |
    */

    'taxonomies' => [
        'seed_category' => [
            'post_types' => ['seed'],
            'meta_box' => 'radio',
            'names' => [
                'singular' => 'Category',
                'plural' => 'Categories',
            ],
        ],

        'liquor_type' => [
            'post_types' => ['cocktail'],
            'meta_box' => 'simple',
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'cocktails',
                'with_front' => false,
                'hierarchical' => false,
            ],
            'names' => [
                'singular' => 'Dranksoort',
                'plural' => 'Dranksoorten',
            ],
        ],

        'cocktail_type' => [
            'post_types' => ['cocktail'],
            'meta_box' => 'simple',
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => 'cocktail-type',
                'with_front' => false,
            ],
            'names' => [
                'singular' => 'Cocktail Type',
                'plural' => 'Cocktail Types',
            ],
        ],
    ],
];
