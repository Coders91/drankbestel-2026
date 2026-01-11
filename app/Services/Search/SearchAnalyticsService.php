<?php

namespace App\Services\Search;

use Illuminate\Support\Facades\DB;

class SearchAnalyticsService
{
    protected string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'search_analytics';
    }

    /**
     * Log a search query
     */
    public function logSearch(string $query, int $resultCount): void
    {
        global $wpdb;

        // Don't log empty or very short queries
        if (strlen(trim($query)) < 2) {
            return;
        }

        // Normalize the query
        $normalizedQuery = mb_strtolower(trim($query));

        $wpdb->insert($this->table, [
            'query' => $normalizedQuery,
            'result_count' => $resultCount,
            'user_id' => get_current_user_id() ?: null,
            'created_at' => current_time('mysql'),
        ]);
    }

    /**
     * Get popular search queries
     */
    public function getPopularSearches(int $limit = 10, string $period = '30 days'): array
    {
        global $wpdb;

        $since = date('Y-m-d H:i:s', strtotime("-{$period}"));

        return $wpdb->get_results($wpdb->prepare("
            SELECT
                query,
                COUNT(*) as search_count,
                AVG(result_count) as avg_results,
                MAX(created_at) as last_searched
            FROM {$this->table}
            WHERE created_at >= %s
            GROUP BY query
            ORDER BY search_count DESC
            LIMIT %d
        ", $since, $limit));
    }

    /**
     * Get searches with zero results
     */
    public function getZeroResultSearches(int $limit = 10, string $period = '30 days'): array
    {
        global $wpdb;

        $since = date('Y-m-d H:i:s', strtotime("-{$period}"));

        return $wpdb->get_results($wpdb->prepare("
            SELECT
                query,
                COUNT(*) as search_count,
                MAX(created_at) as last_searched
            FROM {$this->table}
            WHERE result_count = 0
            AND created_at >= %s
            GROUP BY query
            ORDER BY search_count DESC
            LIMIT %d
        ", $since, $limit));
    }

    /**
     * Get search statistics
     */
    public function getSearchStats(string $period = '30 days'): array
    {
        global $wpdb;

        $since = date('Y-m-d H:i:s', strtotime("-{$period}"));

        $totals = $wpdb->get_row($wpdb->prepare("
            SELECT
                COUNT(*) as total_searches,
                COUNT(DISTINCT query) as unique_queries,
                AVG(result_count) as avg_results,
                SUM(CASE WHEN result_count = 0 THEN 1 ELSE 0 END) as zero_result_searches
            FROM {$this->table}
            WHERE created_at >= %s
        ", $since));

        return [
            'total_searches' => (int) ($totals->total_searches ?? 0),
            'unique_queries' => (int) ($totals->unique_queries ?? 0),
            'avg_results' => round((float) ($totals->avg_results ?? 0), 1),
            'zero_result_searches' => (int) ($totals->zero_result_searches ?? 0),
            'zero_result_rate' => $totals->total_searches > 0
                ? round(($totals->zero_result_searches / $totals->total_searches) * 100, 1)
                : 0,
        ];
    }

    /**
     * Get searches per day for charting
     */
    public function getSearchesPerDay(int $days = 30): array
    {
        global $wpdb;

        $since = date('Y-m-d', strtotime("-{$days} days"));

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT
                DATE(created_at) as date,
                COUNT(*) as search_count,
                SUM(CASE WHEN result_count = 0 THEN 1 ELSE 0 END) as zero_results
            FROM {$this->table}
            WHERE DATE(created_at) >= %s
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", $since));

        // Fill in missing days
        $data = [];
        $currentDate = new \DateTime($since);
        $endDate = new \DateTime();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $found = null;

            foreach ($results as $row) {
                if ($row->date === $dateStr) {
                    $found = $row;
                    break;
                }
            }

            $data[] = [
                'date' => $dateStr,
                'search_count' => $found ? (int) $found->search_count : 0,
                'zero_results' => $found ? (int) $found->zero_results : 0,
            ];

            $currentDate->modify('+1 day');
        }

        return $data;
    }

    /**
     * Create the analytics table
     */
    public static function createTable(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'search_analytics';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            query varchar(255) NOT NULL,
            result_count int(11) NOT NULL DEFAULT 0,
            user_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY query (query(50)),
            KEY created_at (created_at),
            KEY result_count (result_count)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Check if table exists
     */
    public static function tableExists(): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'search_analytics';
        return $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
    }

    /**
     * Cleanup old data
     */
    public function cleanup(int $daysToKeep = 90): int
    {
        global $wpdb;

        $cutoff = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));

        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table} WHERE created_at < %s",
            $cutoff
        ));
    }
}
