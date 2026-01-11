<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class ProductTaxonomyFields extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('product_taxonomy_fields');

        $fields
            ->setLocation('taxonomy', '==', 'product_cat')
            ->or('taxonomy', '==', 'product_brand');

        $fields
            ->addSelect('proposed_filters', [
                'label' => 'Voorgestelde filters',
                'instructions' => 'Selecteer filters die als snelkeuze worden getoond boven het productoverzicht.',
                'choices' => [],
                'multiple' => 1,
                'ui' => 1,
                'ajax' => 0,
                'return_format' => 'value',
            ])
            ->addTab('hub_settings', [
                'label' => 'Artikelhub instellingen',
            ])
            ->addWysiwyg('hub_intro', [
                'label' => 'Hub introductie',
                'instructions' => 'Introductietekst voor de artikelhub pagina (/sterke-drank/{categorie}/).',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 1,
            ])
            ->addRepeater('hub_faq', [
                'label' => 'FAQ',
                'instructions' => 'Veelgestelde vragen voor de artikelhub.',
                'layout' => 'block',
                'button_label' => 'Vraag toevoegen',
            ])
                ->addText('question', [
                    'label' => 'Vraag',
                    'required' => 1,
                ])
                ->addWysiwyg('answer', [
                    'label' => 'Antwoord',
                    'required' => 1,
                    'tabs' => 'all',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                ])
            ->endRepeater()
            ->addPostObject('featured_products', [
                'label' => 'Uitgelichte producten',
                'instructions' => 'Selecteer producten om uit te lichten op de hub pagina.',
                'post_type' => ['product'],
                'multiple' => 1,
                'return_format' => 'id',
                'ui' => 1,
            ])
            ->addPostObject('featured_articles', [
                'label' => 'Uitgelichte artikelen',
                'instructions' => 'Selecteer artikelen om uit te lichten.',
                'post_type' => ['article'],
                'multiple' => 1,
                'return_format' => 'id',
                'ui' => 1,
            ])
            ->addPostObject('featured_lists', [
                'label' => 'Uitgelichte lijsten',
                'instructions' => 'Selecteer lijst-artikelen (top 10, beste, etc.).',
                'post_type' => ['article'],
                'multiple' => 1,
                'return_format' => 'id',
                'ui' => 1,
            ])
            ->addTaxonomy('linked_liquor_type', [
                'label' => 'Gekoppeld dranktype',
                'instructions' => 'Koppel aan een liquor_type voor gerelateerde cocktails.',
                'taxonomy' => 'liquor_type',
                'field_type' => 'select',
                'allow_null' => 1,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
            ]);

        return $fields->build();
    }
}
