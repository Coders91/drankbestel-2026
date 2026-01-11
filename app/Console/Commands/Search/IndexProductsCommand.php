<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class IndexProductsCommand extends Command
{
    protected $signature = 'search:index:products';
    protected $description = 'Create or rebuild the products search index';

    public function handle(TNTSearch $tnt): int
    {
        $this->info('Creating products search index...');

        $indexer = $tnt->createIndex(config('search.indexes.products.file', 'products.index'));

        if ($tokenizer = config('search.tokenizer')) {
            $indexer->setTokenizer(new $tokenizer);
        }

        global $wpdb;

        // Index products with title, content, excerpt, SKU, ACF fields, and all product attributes
        $indexer->query("
            SELECT
                p.ID as id,
                CONCAT_WS(' ',
                    p.post_title,
                    p.post_excerpt,
                    p.post_content,
                    COALESCE(sku.meta_value, ''),
                    COALESCE(contents.meta_value, ''),
                    (
                        SELECT GROUP_CONCAT(DISTINCT t.name SEPARATOR ' ')
                        FROM {$wpdb->term_relationships} tr
                        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                        WHERE tr.object_id = p.ID
                        AND tt.taxonomy LIKE 'pa_%'
                    )
                ) as product
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} sku ON p.ID = sku.post_id AND sku.meta_key = '_sku'
            LEFT JOIN {$wpdb->postmeta} contents ON p.ID = contents.post_id AND contents.meta_key = 'product_contents'
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
        ");

        $indexer->run();

        $count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts}
            WHERE post_type = 'product' AND post_status = 'publish'
        ");

        $this->info("Successfully indexed {$count} products!");

        return Command::SUCCESS;
    }
}
