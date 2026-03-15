<?php

namespace App\Console\Commands\Search;

use App\Services\Search\SearchConfig;
use App\Support\Search\PrefixTokenizer;
use App\Support\Search\SynonymsHandler;
use Illuminate\Console\Command;
use TeamTNT\TNTSearch\TNTSearch;

class DebugSearchCommand extends Command
{
    protected $signature = 'search:debug {query : The search query to debug}';
    protected $description = 'Debug search results to diagnose missing products';

    public function handle(TNTSearch $tnt, SearchConfig $config): int
    {
        $query = $this->argument('query');
        $this->info("Debugging search for: \"{$query}\"");
        $this->newLine();

        // 1. Synonym expansion
        $this->section('Synonym expansion');
        $handler = SynonymsHandler::fromConfig();

        if ($handler->hasSynonyms()) {
            $this->line('Synonym groups loaded: ' . count($handler->getGroups()));
            $expanded = $handler->applySynonyms($query);
            $this->line("Original:  \"{$query}\"");
            $this->line("Expanded:  \"{$expanded}\"");

            $words = preg_split('/\s+/', mb_strtolower($query));
            foreach ($words as $word) {
                $syns = $handler->getSynonyms($word);
                if (!empty($syns)) {
                    $this->line("  '{$word}' synonyms: [" . implode(', ', $syns) . ']');
                } else {
                    $this->warn("  '{$word}' has NO synonyms configured");
                }
            }
        } else {
            $this->warn('No synonyms loaded! Check WP option "search_synonyms" and config/search.php');
        }

        $this->newLine();

        // 2. Tokenizer output
        $this->section('Tokenizer output');
        $tokenizer = new PrefixTokenizer;
        $queryTokens = $tokenizer->tokenize($expanded ?? $query);
        $this->line('Query tokens (' . count($queryTokens) . '): [' . implode(', ', $queryTokens) . ']');
        $this->newLine();

        // 3. Raw TNTSearch results
        $this->section('Raw TNTSearch results (products)');
        try {
            $tnt->selectIndex($config->getIndexFile('products'));

            if ($config->fuzzyEnabled()) {
                $tnt->fuzziness = true;
                $tnt->fuzzy_prefix_length = $config->fuzzyPrefixLength();
                $tnt->fuzzy_max_expansions = $config->fuzzyMaxExpansions();
                $tnt->fuzzy_distance = $config->fuzzyDistance();
                $this->line('Fuzzy search: ENABLED (distance=' . $config->fuzzyDistance() . ')');
            } else {
                $this->line('Fuzzy search: DISABLED');
            }

            // Search with expanded query
            $expandedQuery = $handler->hasSynonyms() ? $handler->applySynonyms($query) : $query;
            $results = $tnt->search($expandedQuery, 50);

            $this->line("Search query sent to TNT: \"{$expandedQuery}\"");
            $this->line('Results returned: ' . count($results['ids']));
            $this->newLine();

            if (!empty($results['ids'])) {
                global $wpdb;

                $ids = implode(',', array_map('intval', $results['ids']));
                $rows = $wpdb->get_results("
                    SELECT ID, post_title, post_status
                    FROM {$wpdb->posts}
                    WHERE ID IN ({$ids})
                    ORDER BY FIELD(ID, {$ids})
                ");

                $this->table(
                    ['#', 'ID', 'Title', 'Status'],
                    array_map(fn($row, $i) => [
                        $i + 1,
                        $row->ID,
                        $row->post_title,
                        $row->post_status,
                    ], $rows, array_keys($rows))
                );
            }
        } catch (\Throwable $e) {
            $this->error('TNTSearch error: ' . $e->getMessage());
        }

        $this->newLine();

        // 4. Check if "monin vanilla" exists in the DB
        $this->section('Database check for "monin vanilla"');
        global $wpdb;

        $monin = $wpdb->get_results("
            SELECT ID, post_title, post_status, post_type
            FROM {$wpdb->posts}
            WHERE post_title LIKE '%monin%vanilla%'
            OR post_title LIKE '%vanilla%monin%'
            ORDER BY post_title
        ");

        if (empty($monin)) {
            $this->warn('No posts found with "monin" AND "vanilla" in the title.');

            // Broader search
            $this->line('Broader search for "monin":');
            $broader = $wpdb->get_results("
                SELECT ID, post_title, post_status, post_type
                FROM {$wpdb->posts}
                WHERE post_title LIKE '%monin%'
                AND post_type = 'product'
                ORDER BY post_title
                LIMIT 20
            ");
            if (!empty($broader)) {
                $this->table(
                    ['ID', 'Title', 'Status', 'Type'],
                    array_map(fn($row) => [$row->ID, $row->post_title, $row->post_status, $row->post_type], $broader)
                );
            }
        } else {
            $this->table(
                ['ID', 'Title', 'Status', 'Type'],
                array_map(fn($row) => [$row->ID, $row->post_title, $row->post_status, $row->post_type], $monin)
            );

            // Check if these IDs are in the TNTSearch results
            $foundIds = $results['ids'] ?? [];
            foreach ($monin as $row) {
                $inResults = in_array($row->ID, $foundIds) ? 'YES' : 'NO';
                $this->line("  ID {$row->ID} ({$row->post_title}) in search results: {$inResults}");
            }
        }

        $this->newLine();

        // 5. Check what's actually indexed for the missing product
        $this->section('Index content check');
        if (!empty($monin)) {
            foreach ($monin as $row) {
                if ($row->post_type !== 'product' || $row->post_status !== 'publish') {
                    $this->warn("ID {$row->ID}: not a published product (status={$row->post_status}, type={$row->post_type})");
                    continue;
                }

                $this->line("Checking what would be indexed for ID {$row->ID} ({$row->post_title}):");

                $indexData = $wpdb->get_var($wpdb->prepare("
                    SELECT CONCAT_WS(' ',
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
                            AND tt.taxonomy LIKE 'pa_%%'
                        )
                    )
                    FROM {$wpdb->posts} p
                    LEFT JOIN {$wpdb->postmeta} sku ON p.ID = sku.post_id AND sku.meta_key = '_sku'
                    LEFT JOIN {$wpdb->postmeta} contents ON p.ID = contents.post_id AND contents.meta_key = 'product_contents'
                    WHERE p.ID = %d
                ", $row->ID));

                if ($indexData) {
                    // Show first 200 chars
                    $preview = mb_substr($indexData, 0, 200);
                    $this->line("  Index data preview: \"{$preview}...\"");

                    // Tokenize and check for vanilla
                    $tokens = $tokenizer->tokenize($indexData);
                    $vanillaTokens = array_filter($tokens, fn($t) => str_contains($t, 'vanil') || str_contains($t, 'vanill'));
                    $this->line('  Contains "vanilla" tokens: ' . (empty($vanillaTokens) ? 'NO' : implode(', ', $vanillaTokens)));
                    $this->line('  Total tokens: ' . count($tokens));
                } else {
                    $this->warn("  No index data found for ID {$row->ID}");
                }
            }
        }

        return Command::SUCCESS;
    }

    protected function section(string $title): void
    {
        $this->line("── {$title} " . str_repeat('─', max(0, 60 - strlen($title))));
    }
}
