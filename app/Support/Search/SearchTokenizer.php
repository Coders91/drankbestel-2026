<?php

namespace App\Support\Search;

use TeamTNT\TNTSearch\Tokenizer\TokenizerInterface;

/**
 * Search-time tokenizer: normalizes text and splits into words,
 * but does NOT generate prefix tokens.
 *
 * PrefixTokenizer is used at index time to create prefix tokens
 * (e.g., "vanilla" → [vanilla, vanill, vanil, vani, van]).
 * At search time we only want full words, because prefixes like "van"
 * match too many unrelated documents and flood the results.
 */
class SearchTokenizer implements TokenizerInterface
{
    protected static $pattern = '/[\s,.\-_\/\\\\]+/u';

    public function getPattern(): string
    {
        return self::$pattern;
    }

    public function tokenize($text, $stopwords = []): array
    {
        $text = is_string($text) ? $text : '';
        $text = mb_strtolower($text);
        $text = $this->normalize($text);

        $words = preg_split(self::$pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        $tokens = [];

        foreach ($words as $word) {
            $word = trim($word);

            if (mb_strlen($word) < 2) {
                continue;
            }

            if (is_array($stopwords) && in_array($word, $stopwords)) {
                continue;
            }

            $tokens[] = $word;
        }

        return array_values(array_unique(array_filter($tokens)));
    }

    /**
     * Normalize text - same as PrefixTokenizer for consistent matching
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
