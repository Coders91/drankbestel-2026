<?php

namespace App\Support\Search;

class SearchHighlighter
{
    /**
     * Highlight matching terms in text
     *
     * @param string $text The text to highlight
     * @param string $query The search query
     * @param string $tag The HTML tag to use for highlighting
     * @return string The highlighted text (HTML)
     */
    public static function highlight(string $text, string $query, string $tag = 'mark'): string
    {
        if (empty(trim($query))) {
            return e($text);
        }

        // Split query into words
        $words = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);

        // Filter out very short words
        $words = array_filter($words, fn($word) => mb_strlen($word) >= 2);

        if (empty($words)) {
            return e($text);
        }

        // Sort by length (longest first) to avoid partial replacements
        usort($words, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

        // Escape the text first for security
        $escapedText = e($text);

        // Build pattern for all words
        $patterns = array_map(fn($word) => preg_quote($word, '/'), $words);
        $pattern = '/(' . implode('|', $patterns) . ')/iu';

        // Replace matches with highlighted version
        return preg_replace(
            $pattern,
            "<{$tag}>$1</{$tag}>",
            $escapedText
        );
    }

    /**
     * Highlight with custom CSS class instead of tag
     */
    public static function highlightWithClass(string $text, string $query, string $class = 'search-highlight'): string
    {
        if (empty(trim($query))) {
            return e($text);
        }

        $words = preg_split('/\s+/', trim($query), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn($word) => mb_strlen($word) >= 2);

        if (empty($words)) {
            return e($text);
        }

        usort($words, fn($a, $b) => mb_strlen($b) - mb_strlen($a));

        $escapedText = e($text);
        $patterns = array_map(fn($word) => preg_quote($word, '/'), $words);
        $pattern = '/(' . implode('|', $patterns) . ')/iu';

        return preg_replace(
            $pattern,
            "<span class=\"{$class}\">$1</span>",
            $escapedText
        );
    }
}
