<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | The directory where TNTSearch index files will be stored.
    |

    */

    'storage_path' => WP_CONTENT_DIR . '/tntsearch',

    /*
    |--------------------------------------------------------------------------
    | Fuzzy Matching
    |--------------------------------------------------------------------------
    |
    | Configuration for fuzzy search to handle typos and similar spellings.
    |
    */

    'fuzzy' => [
        'enabled' => env('SEARCH_FUZZY_ENABLED', false),
        'prefix_length' => env('SEARCH_FUZZY_PREFIX', 2),
        'max_expansions' => env('SEARCH_FUZZY_MAX_EXPANSIONS', 50),
        'distance' => env('SEARCH_FUZZY_DISTANCE', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Result Limits
    |--------------------------------------------------------------------------
    |
    | Default number of results to return for each search type.
    |
    */

    'limits' => [
        'products' => 12,
        'categories' => 5,
        'tags' => 5,
        'brands' => 5,
        'articles' => 5,
        'cocktails' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Query Length
    |--------------------------------------------------------------------------
    |
    | Minimum number of characters required before search is triggered.
    |
    */

    'min_query_length' => 2,

    /*
    |--------------------------------------------------------------------------
    | Index Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for each search index including file name and field weights.
    |
    */

    'indexes' => [
        'products' => [
            'file' => 'products.index',
        ],
        'categories' => [
            'file' => 'categories.index',
            'taxonomy' => 'product_cat',
        ],
        'tags' => [
            'file' => 'tags.index',
            'taxonomy' => 'product_tag',
        ],
        'brands' => [
            'file' => 'brands.index',
            'taxonomies' => ['product_brand'],
        ],
        'articles' => [
            'file' => 'articles.index',
            'post_type' => 'article',
        ],
        'cocktails' => [
            'file' => 'cocktails.index',
            'post_type' => 'cocktail',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Tokenizer
    |--------------------------------------------------------------------------
    |
    | Optional custom tokenizer class implementing TokenizerInterface.
    | Set to null to use the default tokenizer.
    |
    */

    'tokenizer' => \App\Support\Search\PrefixTokenizer::class,

    /*
    |--------------------------------------------------------------------------
    | Synonyms
    |--------------------------------------------------------------------------
    |
    | Define synonym groups for search expansion. Each line is a group of
    | synonyms separated by commas. The first term is the "canonical" form.
    | Lines starting with # are comments.
    |
    | Example: "whisky, whiskey, wisky" means all three match the same products.
    |
    */

    'synonyms' => [
        // Spelling variations
        'whisky' => ['whiskey', 'wisky'],
        'vodka' => ['wodka'],

        // Brand variations (test with simple ones first)
        'bacardi' => ['barcadi', 'bakardi'],
        'jagermeister' => ['jägermeister', 'jager'],

        // Simple drink types
        'rum' => ['rhum'],
        'gin' => ['jenever'],
    ],
];
