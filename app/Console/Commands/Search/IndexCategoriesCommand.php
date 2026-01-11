<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class IndexCategoriesCommand extends Command
{
    protected $signature = 'search:index:categories';
    protected $description = 'Create or rebuild the categories search index';

    public function handle(TNTSearch $tnt): int
    {
        $this->info('Creating categories search index...');

        $indexer = $tnt->createIndex(config('search.indexes.categories.file', 'categories.index'));

        if ($tokenizer = config('search.tokenizer')) {
            $indexer->setTokenizer(new $tokenizer);
        }

        global $wpdb;

        $indexer->query("
            SELECT
                t.term_id as id,
                t.name as name,
                t.slug as slug,
                tt.description as description
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = 'product_cat'
            AND tt.count > 0
        ");

        $indexer->run();

        $count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->term_taxonomy}
            WHERE taxonomy = 'product_cat' AND count > 0
        ");

        $this->info("Successfully indexed {$count} categories!");

        return Command::SUCCESS;
    }
}
