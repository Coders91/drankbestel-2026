<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class IndexBrandsCommand extends Command
{
    protected $signature = 'search:index:brands';
    protected $description = 'Create or rebuild the brands search index';

    public function handle(TNTSearch $tnt): int
    {
        $this->info('Creating brands search index...');

        $indexer = $tnt->createIndex(config('search.indexes.brands.file', 'brands.index'));

        if ($tokenizer = config('search.tokenizer')) {
            $indexer->setTokenizer(new $tokenizer);
        }

        global $wpdb;

        // Get configured brand taxonomies
        $taxonomies = config('search.indexes.brands.taxonomies', ['brand', 'pa_brand']);
        $taxonomyList = "'" . implode("','", array_map('esc_sql', $taxonomies)) . "'";

        $indexer->query("
            SELECT
                t.term_id as id,
                t.name as name,
                t.slug as slug,
                tt.description as description,
                tt.taxonomy as taxonomy
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy IN ({$taxonomyList})
            AND tt.count > 0
        ");

        $indexer->run();

        $count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->term_taxonomy}
            WHERE taxonomy IN ({$taxonomyList}) AND count > 0
        ");

        $this->info("Successfully indexed {$count} brands!");

        return Command::SUCCESS;
    }
}
