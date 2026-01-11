<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class IndexTagsCommand extends Command
{
    protected $signature = 'search:index:tags';
    protected $description = 'Create or rebuild the tags search index';

    public function handle(TNTSearch $tnt): int
    {
        $this->info('Creating tags search index...');

        $indexer = $tnt->createIndex(config('search.indexes.tags.file', 'tags.index'));

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
            WHERE tt.taxonomy = 'product_tag'
            AND tt.count > 0
        ");

        $indexer->run();

        $count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->term_taxonomy}
            WHERE taxonomy = 'product_tag' AND count > 0
        ");

        $this->info("Successfully indexed {$count} tags!");

        return Command::SUCCESS;
    }
}
