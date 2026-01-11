<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class ReviewForm extends Form
{
    public int $rating = 0;

    public string $author = '';

    public string $email = '';

    public string $content = '';

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'author' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'content' => 'required|string|min:10|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => __('Selecteer een beoordeling.', 'sage'),
            'rating.min' => __('Selecteer minimaal 1 ster.', 'sage'),
            'rating.max' => __('Selecteer maximaal 5 sterren.', 'sage'),
            'author.required' => __('Vul je naam in.', 'sage'),
            'author.min' => __('Naam moet minimaal 2 karakters bevatten.', 'sage'),
            'author.max' => __('Naam mag maximaal 100 karakters bevatten.', 'sage'),
            'email.required' => __('Vul je e-mailadres in.', 'sage'),
            'email.email' => __('Vul een geldig e-mailadres in.', 'sage'),
            'content.required' => __('Schrijf een review.', 'sage'),
            'content.min' => __('Review moet minimaal 10 karakters bevatten.', 'sage'),
            'content.max' => __('Review mag maximaal 2000 karakters bevatten.', 'sage'),
        ];
    }
}
