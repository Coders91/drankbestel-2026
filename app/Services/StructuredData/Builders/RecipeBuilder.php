<?php

namespace App\Services\StructuredData\Builders;

use App\Services\StructuredData\Concerns\HasSchemaIdentifiers;
use Spatie\SchemaOrg\Recipe;
use Spatie\SchemaOrg\Schema;
use WP_Post;

class RecipeBuilder
{
    use HasSchemaIdentifiers;

    /**
     * Build Recipe schema for a cocktail.
     */
    public function build(WP_Post $post): Recipe
    {
        $schema = Schema::recipe()
            ->setProperty('@id', $this->recipeId($post->ID))
            ->name($post->post_title)
            ->url(get_permalink($post))
            ->datePublished(get_the_date('c', $post))
            ->dateModified(get_the_modified_date('c', $post));

        // Description
        $description = $post->post_excerpt ?: wp_trim_words(strip_tags($post->post_content), 55);
        if ($description) {
            $schema->description($description);
        }

        // Author/Publisher
        $schema->author(['@id' => $this->organizationId()]);
        $schema->publisher(['@id' => $this->organizationId()]);

        // Image
        $imageId = get_post_thumbnail_id($post);
        if ($imageId) {
            $imageUrl = wp_get_attachment_image_url($imageId, 'large');
            if ($imageUrl) {
                $schema->image($imageUrl);
            }
        }

        // Prep time
        $prepTime = get_field('prep_time', $post->ID);
        if ($prepTime) {
            $schema->prepTime('PT' . (int) $prepTime . 'M');
            $schema->totalTime('PT' . (int) $prepTime . 'M');
        }

        // Servings / Yield
        $servings = get_field('servings', $post->ID) ?: 1;
        $schema->recipeYield((string) $servings . ' ' . ($servings === 1 ? 'cocktail' : 'cocktails'));

        // Recipe category (cocktail types)
        $cocktailTypes = get_the_terms($post->ID, 'cocktail_type');
        if ($cocktailTypes && ! is_wp_error($cocktailTypes)) {
            $categoryNames = array_map(fn($term) => $term->name, $cocktailTypes);
            $schema->recipeCategory(implode(', ', $categoryNames));
        }

        // Recipe cuisine (liquor types as cuisine proxy)
        $liquorTypes = get_the_terms($post->ID, 'liquor_type');
        if ($liquorTypes && ! is_wp_error($liquorTypes)) {
            $liquorNames = array_map(fn($term) => $term->name, $liquorTypes);
            $schema->recipeCuisine(implode(', ', $liquorNames) . ' cocktail');
        }

        // Ingredients
        $ingredients = $this->buildIngredients($post->ID);
        if (! empty($ingredients)) {
            $schema->recipeIngredient($ingredients);
        }

        // Instructions
        $instructions = $this->buildInstructions($post->ID);
        if (! empty($instructions)) {
            $schema->recipeInstructions($instructions);
        }

        // Keywords (garnish, glass type, difficulty)
        $keywords = $this->buildKeywords($post->ID);
        if (! empty($keywords)) {
            $schema->keywords(implode(', ', $keywords));
        }

        // Suitable for diet (non-alcoholic check for mocktails)
        $cocktailTypes = get_the_terms($post->ID, 'cocktail_type');
        if ($cocktailTypes && ! is_wp_error($cocktailTypes)) {
            $typeNames = array_map(fn($term) => strtolower($term->name), $cocktailTypes);
            if (in_array('mocktail', $typeNames)) {
                $schema->suitableForDiet('https://schema.org/VeganDiet');
            }
        }

        return $schema;
    }

    protected function buildIngredients(int $postId): array
    {
        $liquors = get_field('liquors', $postId) ?: [];
        $ingredients = [];

        foreach ($liquors as $item) {
            $quantity = $item['quantity'] ?? '';
            $unit = $item['unit'] ?? '';
            $name = $item['ingredient_name'] ?? '';

            if ($name) {
                $ingredientText = trim("{$quantity} {$unit} {$name}");
                $ingredients[] = $ingredientText;
            }
        }

        // Add garnish as ingredient
        $garnish = get_field('garnish', $postId);
        if ($garnish) {
            $ingredients[] = $garnish . ' (garnering)';
        }

        return $ingredients;
    }

    protected function buildInstructions(int $postId): array
    {
        $instructions = get_field('instructions', $postId);
        $steps = [];

        if (is_array($instructions)) {
            foreach ($instructions as $index => $step) {
                $text = is_array($step) ? ($step['instruction'] ?? $step['text'] ?? '') : $step;
                if ($text) {
                    $steps[] = Schema::howToStep()
                        ->position($index + 1)
                        ->text(wp_strip_all_tags($text));
                }
            }
        } elseif (is_string($instructions) && $instructions) {
            // If instructions is a single text block, split by newlines or numbered items
            $lines = preg_split('/\r\n|\r|\n/', $instructions);
            $position = 1;
            foreach ($lines as $line) {
                $line = trim(wp_strip_all_tags($line));
                // Remove leading numbers like "1." or "1)"
                $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
                if ($line) {
                    $steps[] = Schema::howToStep()
                        ->position($position++)
                        ->text($line);
                }
            }
        }

        return $steps;
    }

    protected function buildKeywords(int $postId): array
    {
        $keywords = ['cocktail', 'recept', 'cocktail recept'];

        $glassType = get_field('glass_type', $postId);
        if ($glassType) {
            $keywords[] = $glassType;
        }

        $difficulty = get_field('difficulty', $postId);
        if ($difficulty) {
            $difficultyMap = [
                'easy' => 'makkelijk',
                'medium' => 'gemiddeld',
                'hard' => 'moeilijk',
            ];
            $keywords[] = $difficultyMap[$difficulty] ?? $difficulty;
        }

        $garnish = get_field('garnish', $postId);
        if ($garnish) {
            $keywords[] = $garnish;
        }

        // Add liquor types as keywords
        $liquorTypes = get_the_terms($postId, 'liquor_type');
        if ($liquorTypes && ! is_wp_error($liquorTypes)) {
            foreach ($liquorTypes as $term) {
                $keywords[] = strtolower($term->name);
                $keywords[] = strtolower($term->name) . ' cocktail';
            }
        }

        // Add cocktail types as keywords
        $cocktailTypes = get_the_terms($postId, 'cocktail_type');
        if ($cocktailTypes && ! is_wp_error($cocktailTypes)) {
            foreach ($cocktailTypes as $term) {
                $keywords[] = strtolower($term->name);
            }
        }

        return array_unique($keywords);
    }

    protected function recipeId(int $postId): string
    {
        return trailingslashit(get_permalink($postId)) . '#recipe';
    }
}
