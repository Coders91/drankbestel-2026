<?php

namespace App\Console\Commands\Search;

use App\Services\Search\SearchAnalyticsService;
use Illuminate\Console\Command;

class CreateAnalyticsTableCommand extends Command
{
    protected $signature = 'search:analytics:migrate';
    protected $description = 'Create the search analytics database table';

    public function handle(): int
    {
        if (SearchAnalyticsService::tableExists()) {
            $this->info('Search analytics table already exists.');
            return Command::SUCCESS;
        }

        $this->info('Creating search analytics table...');

        SearchAnalyticsService::createTable();

        if (SearchAnalyticsService::tableExists()) {
            $this->info('Search analytics table created successfully!');
            return Command::SUCCESS;
        }

        $this->error('Failed to create search analytics table.');
        return Command::FAILURE;
    }
}
