<?php

namespace App\Support\Search;

class SynonymsHandler
{
    protected array $singleWordLookup = [];
    protected array $multiWordLookup = [];
    protected array $synonymGroups = [];

    public function __construct(?string $rawSynonyms = null)
    {
        if ($rawSynonyms !== null) {
            $this->parseSynonyms($rawSynonyms);
        }
    }

    /**
     * Create instance from WP option, falling back to config
     */
    public static function fromConfig(): self
    {
        $synonyms = get_option('search_synonyms');

        if ($synonyms === false) {
            $synonyms = config('search.synonyms', '');
        }

        return new self($synonyms);
    }

    /**
     * Parse raw synonyms text into lookup tables
     * Format: Each line is a group of synonyms, comma-separated
     * First term in each group is the "canonical" form
     */
    public function parseSynonyms(string $rawSynonyms): void
    {
        $this->singleWordLookup = [];
        $this->multiWordLookup = [];
        $this->synonymGroups = [];

        $lines = explode("\n", $rawSynonyms);

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $terms = array_map('trim', explode(',', $line));
            $terms = array_filter($terms, fn($t) => !empty($t));

            if (count($terms) < 2) {
                continue;
            }

            // Normalize all terms
            $normalizedTerms = array_map(fn($t) => mb_strtolower($t), $terms);
            $this->synonymGroups[] = $normalizedTerms;

            // Build lookup tables
            foreach ($normalizedTerms as $term) {
                $wordCount = count(explode(' ', $term));

                if ($wordCount > 1) {
                    $this->multiWordLookup[$term] = $normalizedTerms;
                } else {
                    $this->singleWordLookup[$term] = $normalizedTerms;
                }
            }
        }
    }

    /**
     * Apply synonyms to search text - append all synonym variations
     * Used during search to expand query
     */
    public function applySynonyms(string $text): string
    {
        $text = mb_strtolower(trim($text));

        if (empty($text)) {
            return $text;
        }

        $additions = [];

        // Check multi-word synonyms first (longer matches take priority)
        foreach ($this->multiWordLookup as $phrase => $synonyms) {
            if (str_contains($text, $phrase)) {
                foreach ($synonyms as $synonym) {
                    if ($synonym !== $phrase && !str_contains($text, $synonym)) {
                        $additions[] = $synonym;
                    }
                }
            }
        }

        // Check single-word synonyms
        $words = preg_split('/\s+/', $text);

        foreach ($words as $word) {
            if (isset($this->singleWordLookup[$word])) {
                foreach ($this->singleWordLookup[$word] as $synonym) {
                    if ($synonym !== $word && !in_array($synonym, $words)) {
                        $additions[] = $synonym;
                    }
                }
            }
        }

        if (!empty($additions)) {
            $text .= ' ' . implode(' ', array_unique($additions));
        }

        return $text;
    }

    /**
     * Canonize text - replace synonyms with their canonical form
     * Used during indexing to normalize content
     */
    public function canonize(string $text): string
    {
        $text = mb_strtolower(trim($text));

        if (empty($text)) {
            return $text;
        }

        // Replace multi-word synonyms first
        foreach ($this->multiWordLookup as $phrase => $synonyms) {
            $canonical = $synonyms[0]; // First term is canonical

            if ($phrase !== $canonical && str_contains($text, $phrase)) {
                $text = str_replace($phrase, $canonical, $text);
            }
        }

        // Replace single-word synonyms
        $words = preg_split('/\s+/', $text);
        $replaced = false;

        foreach ($words as &$word) {
            if (isset($this->singleWordLookup[$word])) {
                $canonical = $this->singleWordLookup[$word][0];

                if ($word !== $canonical) {
                    $word = $canonical;
                    $replaced = true;
                }
            }
        }

        if ($replaced) {
            $text = implode(' ', $words);
        }

        return $text;
    }

    /**
     * Get all synonyms for a term
     */
    public function getSynonyms(string $term): array
    {
        $term = mb_strtolower(trim($term));

        return $this->singleWordLookup[$term]
            ?? $this->multiWordLookup[$term]
            ?? [];
    }

    /**
     * Check if handler has any synonyms loaded
     */
    public function hasSynonyms(): bool
    {
        return !empty($this->singleWordLookup) || !empty($this->multiWordLookup);
    }

    /**
     * Get all synonym groups
     */
    public function getGroups(): array
    {
        return $this->synonymGroups;
    }
}
