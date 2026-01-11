<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class IndexArticlesCommand extends Command
{
    protected $signature = 'search:index:articles';

    protected $description = 'Create or rebuild the articles search index';

    public function handle(TNTSearch $tnt): int
    {
        $this->info('Creating articles search index...');

        $indexer = $tnt->createIndex(config('search.indexes.articles.file', 'articles.index'));

        if ($tokenizer = config('search.tokenizer')) {
            $indexer->setTokenizer(new $tokenizer);
        }

        global $wpdb;

        // Index articles with title, content, excerpt, and primary category name
        $indexer->query("
            SELECT
                p.ID as id,
                CONCAT_WS(' ',
                    p.post_title,
                    p.post_excerpt,
                    p.post_content,
                    (
                        SELECT t.name
                        FROM {$wpdb->postmeta} pm
                        INNER JOIN {$wpdb->terms} t ON pm.meta_value = t.term_id
                        WHERE pm.post_id = p.ID
                        AND pm.meta_key = 'primary_category'
                        LIMIT 1
                    )
                ) as article
            FROM {$wpdb->posts} p
            WHERE p.post_type = 'article'
            AND p.post_status = 'publish'
        ");

        $indexer->run();

        $count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = 'article' AND post_status = 'publish'
        ");

        $this->info("Successfully indexed {$count} articles!");

        return Command::SUCCESS;
    }
}
