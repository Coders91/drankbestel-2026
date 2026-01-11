<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class IndexCocktailsCommand extends Command
{
    protected $signature = 'search:index:cocktails';

    protected $description = 'Create or rebuild the cocktails search index';

    public function handle(TNTSearch $tnt): int
    {
        $this->info('Creating cocktails search index...');

        $indexer = $tnt->createIndex(config('search.indexes.cocktails.file', 'cocktails.index'));

        if ($tokenizer = config('search.tokenizer')) {
            $indexer->setTokenizer(new $tokenizer);
        }

        global $wpdb;

        // Index cocktails with title, content, excerpt, and liquor type terms
        $indexer->query("
            SELECT
                p.ID as id,
                CONCAT_WS(' ',
                    p.post_title,
                    p.post_excerpt,
                    p.post_content,
                    (
                        SELECT GROUP_CONCAT(DISTINCT t.name SEPARATOR ' ')
                        FROM {$wpdb->term_relationships} tr
                        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                        WHERE tr.object_id = p.ID
                        AND tt.taxonomy IN ('liquor_type', 'cocktail_type')
                    )
                ) as cocktail
            FROM {$wpdb->posts} p
            WHERE p.post_type = 'cocktail'
            AND p.post_status = 'publish'
        ");

        $indexer->run();

        $count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = 'cocktail' AND post_status = 'publish'
        ");

        $this->info("Successfully indexed {$count} cocktails!");

        return Command::SUCCESS;
    }
}
