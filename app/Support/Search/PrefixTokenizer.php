<?php

namespace App\Support\Search;

use TeamTNT\TNTSearch\Tokenizer\TokenizerInterface;

class PrefixTokenizer implements TokenizerInterface
{
    protected static $pattern = '/[\s,.\-_\/\\\\]+/u';

    public function getPattern(): string
    {
        return self::$pattern;
    }

    /**
     * Minimum prefix length to generate
     */
    protected int $minPrefixLength = 3;

    public function tokenize($text, $stopwords = []): array
    {
        $text = is_string($text) ? $text : '';
        $text = mb_strtolower($text);
        $text = $this->normalize($text);
        $text = $this->canonizeSynonyms($text);

        // Split into words
        $words = preg_split(self::$pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        $tokens = [];

        foreach ($words as $word) {
            $word = trim($word);
            $wordLength = mb_strlen($word);

            if ($wordLength < 2) {
                continue;
            }

            // Skip stopwords
            if (is_array($stopwords) && in_array($word, $stopwords)) {
                continue;
            }

            // Add full word
            $tokens[] = $word;

            // Add prefixes for words 4+ characters
            // e.g., "bacardi" -> "bacard", "bacar", "baca", "bac"
            if ($wordLength >= 4) {
                for ($i = $wordLength - 1; $i >= $this->minPrefixLength; $i--) {
                    $tokens[] = mb_substr($word, 0, $i);
                }
            }
        }

        return array_values(array_unique(array_filter($tokens)));
    }

    /**
     * Replace synonyms with their canonical form so both indexed
     * text and search queries use the same tokens
     */
    protected function canonizeSynonyms(string $text): string
    {
        static $handler = null;

        if ($handler === null) {
            $handler = SynonymsHandler::fromConfig();
        }

        if (! $handler->hasSynonyms()) {
            return $text;
        }

        return $handler->canonize($text);
    }

    /**
     * Normalize text - handle common character substitutions
     */
    protected function normalize(string $text): string
    {
        $replacements = [
            'ë' => 'e', 'é' => 'e', 'è' => 'e', 'ê' => 'e',
            'ï' => 'i', 'í' => 'i', 'ì' => 'i', 'î' => 'i',
            'ö' => 'o', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o',
            'ü' => 'u', 'ú' => 'u', 'ù' => 'u', 'û' => 'u',
            'ä' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a',
            'ñ' => 'n', 'ç' => 'c', 'ß' => 'ss',
        ];

        return strtr($text, $replacements);
    }
}
