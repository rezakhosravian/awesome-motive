<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | "attribute.rule" convention. This makes it easy to specify a specific
    | custom language line for a given attribute rule.
    |
    */

    'deck' => [
        'name_required' => 'Please provide a name for your deck.',
        'name_max' => 'The deck name cannot exceed 255 characters.',
        'description_max' => 'The description cannot exceed 1000 characters.',
    ],

    'flashcard' => [
        'question_required' => 'Please provide a question for the flashcard.',
        'answer_required' => 'Please provide an answer for the flashcard.',
        'question_max' => 'The question cannot exceed 1000 characters.',
        'answer_max' => 'The answer cannot exceed 1000 characters.',
    ],

    'api_token' => [
        'name_required' => 'Please provide a name for your API token.',
        'name_string' => 'Token name must be a valid string.',
        'name_max' => 'Token name must be no more than :max characters.',
        'abilities_array' => 'Abilities must be provided as an array.',
        'ability_string' => 'Each ability must be a valid string.',
        'ability_invalid' => 'Invalid ability. Allowed abilities are: read, write, delete, admin.',
        'expires_at_date' => 'Expiration date must be a valid date.',
        'expires_at_future' => 'Expiration date must be in the future.',
    ],

    'search' => [
        'query_required' => 'Please provide a search query.',
        'query_string' => 'Search query must be a valid string.',
        'query_min' => 'Search query must be at least :min character.',
        'query_max' => 'Search query cannot exceed :max characters.',
        'per_page_integer' => 'Items per page must be a valid number.',
        'per_page_min' => 'Items per page must be at least :min.',
        'per_page_max' => 'Items per page cannot exceed :max.',
        'page_integer' => 'Page number must be a valid number.',
        'page_min' => 'Page number must be at least :min.',
    ],
];
