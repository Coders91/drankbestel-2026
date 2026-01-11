<?php

namespace App\Support\Search;

use TeamTNT\TNTSearch\Tokenizer\AbstractTokenizer;
use TeamTNT\TNTSearch\Tokenizer\TokenizerInterface;

class PhoneticTokenizer extends AbstractTokenizer implements TokenizerInterface
{
    protected static $pattern = '/[\s,.\-_\/\\\\]+/';

    protected static ?SynonymsHandler $synonymsHandler = null;

    /**
     * Get or create the synonyms handler instance
     */
    protected function getSynonymsHandler(): SynonymsHandler
    {
        if (self::$synonymsHandler === null) {
            self::$synonymsHandler = SynonymsHandler::fromConfig();
        }

        return self::$synonymsHandler;
    }

    public function tokenize($text, $stopwords = []): array
    {
        $text = is_string($text) ? $text : '';
        $text = mb_strtolower($text);
        $text = $this->normalize($text);

        // Apply synonyms - adds all synonym variations
        $synonymsHandler = $this->getSynonymsHandler();
        if ($synonymsHandler->hasSynonyms()) {
            $text = $synonymsHandler->applySynonyms($text);
        }

        // Split into words
        $words = preg_split(self::$pattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        $tokens = [];

        foreach ($words as $word) {
            $word = trim($word);

            if (strlen($word) < 2) {
                continue;
            }

            // Skip stopwords
            if (is_array($stopwords) && in_array($word, $stopwords)) {
                continue;
            }

            // Add original word
            $tokens[] = $word;

            // Add soundex for phonetic matching (more reliable than metaphone)
            if (strlen($word) >= 3 && ctype_alpha($word)) {
                $soundex = soundex($word);
                if ($soundex) {
                    $tokens[] = strtolower($soundex);
                }
            }
        }

        return array_values(array_unique(array_filter($tokens)));
    }

    /**
     * Normalize text - handle common character substitutions
     */
    protected function normalize(string $text): string
    {
        // Common character replacements for Dutch/international
        $replacements = [
            'ë' => 'e',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ï' => 'i',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ö' => 'o',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'ü' => 'u',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ä' => 'a',
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ñ' => 'n',
            'ç' => 'c',
            'ß' => 'ss',
        ];

        return strtr($text, $replacements);
    }
}
