<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class ArticleFields extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('article', [
            'title' => 'Artikel instellingen',
            'position' => 'side',
        ]);

        $fields
            ->setLocation('post_type', '==', 'article');

        $fields
            ->addTaxonomy('primary_category', [
                'label' => 'Primaire categorie',
                'instructions' => 'Selecteer de hoofdcategorie voor dit artikel. Dit bepaalt de URL.',
                'required' => 1,
                'taxonomy' => 'product_cat',
                'field_type' => 'select',
                'allow_null' => 0,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
                'multiple' => 0,
            ])
            ->addTaxonomy('related_categories', [
                'label' => 'Gerelateerde categorieën',
                'instructions' => 'Selecteer extra categorieën waar dit artikel ook relevant voor is.',
                'required' => 0,
                'taxonomy' => 'product_cat',
                'field_type' => 'multi_select',
                'allow_null' => 1,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
                'multiple' => 1,
            ])
            ->addTaxonomy('related_brands', [
                'label' => 'Gerelateerde merken',
                'instructions' => 'Selecteer merken die in dit artikel worden besproken.',
                'required' => 0,
                'taxonomy' => 'product_brand',
                'field_type' => 'multi_select',
                'allow_null' => 1,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
                'multiple' => 1,
            ])
            ->addPostObject('related_products', [
                'label' => 'Gerelateerde producten',
                'instructions' => 'Selecteer producten die in dit artikel worden genoemd.',
                'required' => 0,
                'post_type' => ['product'],
                'taxonomy' => [],
                'allow_null' => 1,
                'multiple' => 1,
                'return_format' => 'id',
                'ui' => 1,
            ])
            ->addPostObject('featured_on_products', [
                'label' => 'Uitgelicht op producten',
                'instructions' => 'Selecteer producten waarop dit artikel prominent moet worden getoond.',
                'required' => 0,
                'post_type' => ['product'],
                'taxonomy' => [],
                'allow_null' => 1,
                'multiple' => 1,
                'return_format' => 'id',
                'ui' => 1,
            ])
            ->addSelect('content_format', [
                'label' => 'Content format',
                'instructions' => 'Kies het type artikel.',
                'required' => 1,
                'choices' => [
                    'standard' => 'Standaard artikel',
                    'list' => 'Lijst artikel (top 10, beste, etc.)',
                ],
                'default_value' => 'standard',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 1,
                'ajax' => 0,
                'return_format' => 'value',
            ]);

        // List-specific fields (conditional)
        $fields
            ->addSelect('list_variant', [
                'label' => 'Lijst variant',
                'instructions' => 'Kies het type lijst.',
                'required' => 1,
                'choices' => [
                    'best' => 'Beste (top 10, beste keuze)',
                    'cheapest' => 'Goedkoopste',
                    'seasonal' => 'Seizoensgebonden',
                ],
                'default_value' => 'best',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 1,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'content_format',
                            'operator' => '==',
                            'value' => 'list',
                        ],
                    ],
                ],
            ])
            ->addDatePicker('last_updated', [
                'label' => 'Laatst bijgewerkt',
                'instructions' => 'Datum waarop de lijst voor het laatst is gecontroleerd.',
                'required' => 0,
                'display_format' => 'd-m-Y',
                'return_format' => 'Y-m-d',
                'first_day' => 1,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'content_format',
                            'operator' => '==',
                            'value' => 'list',
                        ],
                    ],
                ],
            ])
            ->addRepeater('list_items', [
                'label' => 'Lijst items',
                'instructions' => 'Voeg de producten toe aan de lijst.',
                'required' => 0,
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Item toevoegen',
                'conditional_logic' => [
                    [
                        [
                            'field' => 'content_format',
                            'operator' => '==',
                            'value' => 'list',
                        ],
                    ],
                ],
            ])
                ->addNumber('position', [
                    'label' => 'Positie',
                    'instructions' => 'Rangnummer in de lijst.',
                    'required' => 1,
                    'min' => 1,
                    'default_value' => 1,
                ])
                ->addPostObject('product', [
                    'label' => 'Product',
                    'instructions' => 'Selecteer het product.',
                    'required' => 1,
                    'post_type' => ['product'],
                    'taxonomy' => [],
                    'allow_null' => 0,
                    'multiple' => 0,
                    'return_format' => 'id',
                    'ui' => 1,
                ])
                ->addWysiwyg('reason', [
                    'label' => 'Reden / Beschrijving',
                    'instructions' => 'Waarom staat dit product op deze plek?',
                    'required' => 0,
                    'tabs' => 'all',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                ])
                ->addText('criteria', [
                    'label' => 'Selectiecriteria',
                    'instructions' => 'Kort criterium waarop geselecteerd is (bijv. "Beste prijs-kwaliteit").',
                    'required' => 0,
                ])
                ->addRepeater('pros_cons', [
                    'label' => 'Voordelen & Nadelen',
                    'instructions' => 'Optioneel: voeg plus- en minpunten toe.',
                    'required' => 0,
                    'min' => 0,
                    'max' => 10,
                    'layout' => 'table',
                    'button_label' => 'Punt toevoegen',
                ])
                    ->addSelect('type', [
                        'label' => 'Type',
                        'choices' => [
                            'pro' => 'Voordeel',
                            'con' => 'Nadeel',
                        ],
                        'default_value' => 'pro',
                    ])
                    ->addText('text', [
                        'label' => 'Tekst',
                        'required' => 1,
                    ])
                ->endRepeater()
            ->endRepeater();

        return $fields->build();
    }
}
