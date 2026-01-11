<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class CocktailFields extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('cocktail', [
            'title' => 'Cocktail recept',
        ]);

        $fields
            ->setLocation('post_type', '==', 'cocktail');

        // Basic info
        $fields
            ->addNumber('prep_time', [
                'label' => 'Bereidingstijd',
                'instructions' => 'Tijd in minuten.',
                'required' => 0,
                'min' => 1,
                'max' => 120,
                'default_value' => 5,
                'append' => 'minuten',
                'wrapper' => [
                    'width' => '33',
                ],
            ])
            ->addNumber('servings', [
                'label' => 'Porties',
                'instructions' => 'Aantal glazen.',
                'required' => 0,
                'min' => 1,
                'max' => 20,
                'default_value' => 1,
                'wrapper' => [
                    'width' => '33',
                ],
            ])
            ->addSelect('difficulty', [
                'label' => 'Moeilijkheidsgraad',
                'instructions' => '',
                'required' => 0,
                'choices' => [
                    'easy' => 'Makkelijk',
                    'medium' => 'Gemiddeld',
                    'hard' => 'Moeilijk',
                ],
                'default_value' => 'easy',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 1,
                'wrapper' => [
                    'width' => '33',
                ],
            ])
            ->addText('glass_type', [
                'label' => 'Type glas',
                'instructions' => 'Bijv. highball, tumbler, coupe.',
                'required' => 0,
                'wrapper' => [
                    'width' => '50',
                ],
            ])
            ->addText('garnish', [
                'label' => 'Garnering',
                'instructions' => 'Bijv. schijfje citroen, muntblaadjes.',
                'required' => 0,
                'wrapper' => [
                    'width' => '50',
                ],
            ]);

        // Ingredients repeater
        $fields
            ->addRepeater('liquors', [
                'label' => 'Ingrediënten',
                'instructions' => 'Voeg alle ingrediënten toe.',
                'required' => 0,
                'min' => 1,
                'max' => 20,
                'layout' => 'table',
                'button_label' => 'Ingrediënt toevoegen',
            ])
                ->addText('quantity', [
                    'label' => 'Hoeveelheid',
                    'instructions' => '',
                    'required' => 1,
                    'placeholder' => '50',
                    'wrapper' => [
                        'width' => '15',
                    ],
                ])
                ->addSelect('unit', [
                    'label' => 'Eenheid',
                    'instructions' => '',
                    'required' => 1,
                    'choices' => [
                        'ml' => 'ml',
                        'cl' => 'cl',
                        'oz' => 'oz',
                        'dash' => 'dash',
                        'drops' => 'druppels',
                        'piece' => 'stuks',
                        'slice' => 'schijf',
                        'sprig' => 'takje',
                        'tsp' => 'theelepel',
                        'tbsp' => 'eetlepel',
                    ],
                    'default_value' => 'ml',
                    'allow_null' => 0,
                    'wrapper' => [
                        'width' => '15',
                    ],
                ])
                ->addText('ingredient_name', [
                    'label' => 'Ingrediënt',
                    'instructions' => '',
                    'required' => 1,
                    'placeholder' => 'Witte rum',
                    'wrapper' => [
                        'width' => '25',
                    ],
                ])
                ->addTaxonomy('liquor_type', [
                    'label' => 'Dranksoort',
                    'instructions' => 'Voor filtering.',
                    'required' => 0,
                    'taxonomy' => 'liquor_type',
                    'field_type' => 'select',
                    'allow_null' => 1,
                    'add_term' => 0,
                    'save_terms' => 0,
                    'load_terms' => 0,
                    'return_format' => 'id',
                    'multiple' => 0,
                    'wrapper' => [
                        'width' => '20',
                    ],
                ])
                ->addPostObject('linked_product', [
                    'label' => 'Product link',
                    'instructions' => 'Optioneel: link naar exact product.',
                    'required' => 0,
                    'post_type' => ['product'],
                    'taxonomy' => [],
                    'allow_null' => 1,
                    'multiple' => 0,
                    'return_format' => 'id',
                    'ui' => 1,
                    'wrapper' => [
                        'width' => '25',
                    ],
                ])
            ->endRepeater();

        // Instructions
        $fields
            ->addWysiwyg('instructions', [
                'label' => 'Bereidingswijze',
                'instructions' => 'Beschrijf stap voor stap hoe de cocktail gemaakt wordt.',
                'required' => 0,
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ])
            ->addTextarea('tips', [
                'label' => 'Tips',
                'instructions' => 'Extra tips voor de thuisbartender.',
                'required' => 0,
                'rows' => 3,
            ]);

        // Brand association
        $fields
            ->addTaxonomy('brand_association', [
                'label' => 'Merk associatie',
                'instructions' => 'Is dit een signature cocktail van een specifiek merk? Selecteer het merk.',
                'required' => 0,
                'taxonomy' => 'product_brand',
                'field_type' => 'select',
                'allow_null' => 1,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
                'multiple' => 0,
            ]);

        return $fields->build();
    }
}
