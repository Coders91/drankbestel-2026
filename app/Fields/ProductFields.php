<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class ProductFields extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $fields = Builder::make('product');

        $fields
            ->setLocation('post_type', '==', 'product');

        $fields
            ->addText('product_ean', [
                'label' => 'EAN',
                'instructions' => '',
                'required' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ])
            ->addText('product_contents', [
                'label' => 'Inhoud',
                'instructions' => '',
                'required' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ])
            ->addText('product_alcoholpercentage', [
                'label' => 'Alcoholpercentage',
                'instructions' => '',
                'required' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ])
            ->addPostObject('product_related', [
                'label' => 'Related products',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'post_type' => ['product'],
                'taxonomy' => [],
                'allow_null' => 1,
                'multiple' => 1,
                'return_format' => 'id',
                'ui' => 1,
            ])
            ->addTaxonomy('product_related_cat', [
                'label' => 'Related product category',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'taxonomy' => 'product_cat',
                'field_type' => 'select',
                'allow_null' => 0,
                'add_term' => 0,
                'save_terms' => 0,
                'load_terms' => 0,
                'return_format' => 'id',
                'multiple' => 0,
            ])
            ->addText('lightspeed_id', [
                'label' => 'Lightspeed ID',
                'instructions' => '',
                'required' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ])
            ->addTrueFalse('product_sold_as_pack', [
                'label' => __('Sold as Pack', 'sage'),
                'instructions' => __('Enable if this product is sold in packs (e.g., 10-pack of bottles)', 'sage'),
                'default_value' => 0,
                'ui' => 1,
            ])
            ->addNumber('product_pack_size', [
                'label' => __('Pack Size', 'sage'),
                'instructions' => __('Number of items per pack (e.g., 10 for a 10-pack)', 'sage'),
                'default_value' => 1,
                'min' => 1,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'product_sold_as_pack',
                            'operator' => '==',
                            'value' => 1,
                        ],
                    ],
                ],
            ]);
        return $fields->build();
    }
}
