<?php

namespace App\Console\Commands\Search;

use App\Services\Search\SearchConfig;
use App\Support\Search\PrefixTokenizer;
use App\Support\Search\SearchTokenizer;
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

        // 2. Tokenizer comparison
        $this->section('Tokenizer comparison');
        $expandedQuery = $expanded ?? $query;

        $prefixTokenizer = new PrefixTokenizer;
        $searchTokenizer = new SearchTokenizer;

        $prefixTokens = $prefixTokenizer->tokenize($expandedQuery);
        $searchTokens = $searchTokenizer->tokenize($expandedQuery);

        $this->warn('PrefixTokenizer (OLD - used at search time):');
        $this->line('  Tokens (' . count($prefixTokens) . '): [' . implode(', ', $prefixTokens) . ']');
        $this->info('SearchTokenizer (NEW - no prefix noise):');
        $this->line('  Tokens (' . count($searchTokens) . '): [' . implode(', ', $searchTokens) . ']');
        $this->newLine();

        // 3. Raw TNTSearch results with OLD tokenizer (PrefixTokenizer)
        $this->section('Results with PrefixTokenizer (OLD)');
        try {
            $tnt->selectIndex($config->getIndexFile('products'));
            $this->configureFuzzy($tnt, $config);

            $results = $tnt->search($expandedQuery, 50);
            $this->line('Results returned: ' . count($results['ids']));
            $this->printResults($results['ids']);
        } catch (\Throwable $e) {
            $this->error('TNTSearch error: ' . $e->getMessage());
        }

        $this->newLine();

        // 4. Raw TNTSearch results with NEW tokenizer (SearchTokenizer)
        $this->section('Results with SearchTokenizer (NEW)');
        try {
            $tnt->selectIndex($config->getIndexFile('products'));
            $this->configureFuzzy($tnt, $config);
            $tnt->tokenizer = new SearchTokenizer;

            $resultsNew = $tnt->search($expandedQuery, 50);
            $this->line('Results returned: ' . count($resultsNew['ids']));
            $this->printResults($resultsNew['ids']);
        } catch (\Throwable $e) {
            $this->error('TNTSearch error: ' . $e->getMessage());
        }

        $this->newLine();

        // 5. Check if "monin vanilla" exists in the DB
        $this->section('Database check');
        global $wpdb;

        $queryWords = preg_split('/\s+/', mb_strtolower($query));
        $likeClause = implode(' AND ', array_map(fn($w) => "post_title LIKE '%{$w}%'", $queryWords));

        $dbResults = $wpdb->get_results("
            SELECT ID, post_title, post_status, post_type
            FROM {$wpdb->posts}
            WHERE ({$likeClause})
            AND post_type = 'product'
            ORDER BY post_title
            LIMIT 20
        ");

        if (empty($dbResults)) {
            $this->warn('No products found matching all query words in title.');
        } else {
            $this->table(
                ['ID', 'Title', 'Status', 'In OLD results', 'In NEW results'],
                array_map(function ($row) use ($results, $resultsNew) {
                    $oldIds = $results['ids'] ?? [];
                    $newIds = $resultsNew['ids'] ?? [];
                    return [
                        $row->ID,
                        $row->post_title,
                        $row->post_status,
                        in_array($row->ID, $oldIds) ? 'YES' : 'NO',
                        in_array($row->ID, $newIds) ? 'YES' : 'NO',
                    ];
                }, $dbResults)
            );
        }

        return Command::SUCCESS;
    }

    protected function printResults(array $ids): void
    {
        if (empty($ids)) {
            $this->warn('No results.');
            return;
        }

        global $wpdb;

        $idList = implode(',', array_map('intval', $ids));
        $rows = $wpdb->get_results("
            SELECT ID, post_title
            FROM {$wpdb->posts}
            WHERE ID IN ({$idList})
            ORDER BY FIELD(ID, {$idList})
        ");

        $this->table(
            ['#', 'ID', 'Title'],
            array_map(fn($row, $i) => [$i + 1, $row->ID, $row->post_title], $rows, array_keys($rows))
        );
    }

    protected function configureFuzzy(TNTSearch $tnt, SearchConfig $config): void
    {
        if ($config->fuzzyEnabled()) {
            $tnt->fuzziness = true;
            $tnt->fuzzy_prefix_length = $config->fuzzyPrefixLength();
            $tnt->fuzzy_max_expansions = $config->fuzzyMaxExpansions();
            $tnt->fuzzy_distance = $config->fuzzyDistance();
        }
    }

    protected function section(string $title): void
    {
        $this->line("── {$title} " . str_repeat('─', max(0, 60 - strlen($title))));
    }
}
