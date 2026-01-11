<?php

namespace App\Fields;

use Log1x\AcfComposer\Builder;
use Log1x\AcfComposer\Field;

class KlantenserviceFields extends Field
{
    /**
     * The field group.
     */
    public function fields(): array
    {
        $faq = Builder::make('klantenservice');

        $faq
            ->setLocation('post_type', '==', 'klantenservice');

        $faq->addWysiwyg('content', [
            'label' => 'WYSIWYG Field',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => [],
            'wrapper' => [
                'width' => '',
                'class' => '',
                'id' => '',
            ],
            'default_value' => '',
            'tabs' => 'all',
            'toolbar' => 'full',
            'media_upload' => 0,
            'delay' => 0,
        ]);

        $faq
            ->addRepeater('accordion', [
                'layout' => 'block',
            ])
            ->addText('question')
            ->addWysiwyg('answer', [
                'label' => 'Answer',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => [],
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'default_value' => '',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'delay' => 0,
            ])
            ->addTrueFalse('featured', [
                'instructions' => 'Checking this adds it to the general FAQ archive',
            ])
            ->endRepeater();

        return $faq->build();
    }
}
