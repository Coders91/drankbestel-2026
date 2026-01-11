<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;

class IndexAllCommand extends Command
{
    protected $signature = 'search:index
                            {--type= : Index specific type only (products, categories, tags, brands)}';

    protected $description = 'Build or rebuild all search indexes';

    public function handle(): int
    {
        $type = $this->option('type');

        if ($type) {
            return $this->indexType($type);
        }

        $this->info('Building all search indexes...');
        $this->newLine();

        $commands = [
            'search:index:products',
            'search:index:categories',
            'search:index:tags',
            'search:index:brands',
            'search:index:articles',
            'search:index:cocktails',
        ];

        foreach ($commands as $command) {
            $this->call($command);
            $this->newLine();
        }

        $this->info('All indexes rebuilt successfully!');

        return Command::SUCCESS;
    }

    protected function indexType(string $type): int
    {
        $command = match ($type) {
            'products' => 'search:index:products',
            'categories' => 'search:index:categories',
            'tags' => 'search:index:tags',
            'brands' => 'search:index:brands',
            'articles' => 'search:index:articles',
            'cocktails' => 'search:index:cocktails',
            default => null,
        };

        if (!$command) {
            $this->error("Unknown type: {$type}");
            $this->info('Available types: products, categories, tags, brands, articles, cocktails');
            return Command::FAILURE;
        }

        return $this->call($command);
    }
}
