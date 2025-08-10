<?php

return [
    /*
    |--------------------------------------------------------------------------
    | FlashcardPro Application Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration values for the FlashcardPro application.
    | All constants and static values should be defined here instead of
    | hardcoded throughout the application.
    |
    */

    'pagination' => [
        'default_per_page' => env('PAGINATION_DEFAULT_PER_PAGE', 15),
        'max_per_page' => env('PAGINATION_MAX_PER_PAGE', 50),
        'min_per_page' => env('PAGINATION_MIN_PER_PAGE', 1),
    ],

    'api' => [
        'version' => env('API_VERSION', 'v1'),
        'rate_limits' => [
            'requests_per_minute' => env('API_RATE_LIMIT_REQUESTS', 60),
            'api_tokens_per_user' => env('API_TOKENS_MAX_PER_USER', 10),
        ],
        'token' => [
            'hash_algorithm' => env('API_TOKEN_HASH', 'sha256'),
            'length' => env('API_TOKEN_LENGTH', 40),
            'default_abilities' => ['*'],
            'valid_abilities' => ['read', 'write', 'delete', 'admin', '*'],
        ],
    ],

    'decks' => [
        'name_max_length' => env('DECK_NAME_MAX_LENGTH', 255),
        'description_max_length' => env('DECK_DESCRIPTION_MAX_LENGTH', 1000),
        'slug_max_length' => env('DECK_SLUG_MAX_LENGTH', 255),
    ],

    'flashcards' => [
        'question_max_length' => env('FLASHCARD_QUESTION_MAX_LENGTH', 1000),
        'answer_max_length' => env('FLASHCARD_ANSWER_MAX_LENGTH', 1000),
        'hint_max_length' => env('FLASHCARD_HINT_MAX_LENGTH', 500),
    ],

    'search' => [
        'min_query_length' => env('SEARCH_MIN_QUERY_LENGTH', 2),
        'max_query_length' => env('SEARCH_MAX_QUERY_LENGTH', 100),
        'enable_fulltext' => env('SEARCH_ENABLE_FULLTEXT', true),
        'result_limit' => env('SEARCH_RESULT_LIMIT', 100),
    ],

    'validation' => [
        'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'username_min_length' => env('USERNAME_MIN_LENGTH', 3),
        'username_max_length' => env('USERNAME_MAX_LENGTH', 255),
    ],

    'study' => [
        'max_flashcards_per_session' => env('STUDY_MAX_FLASHCARDS', 200),
        'shuffle_default' => env('STUDY_SHUFFLE_DEFAULT', false),
        'show_progress' => env('STUDY_SHOW_PROGRESS', true),
    ],

    'features' => [
        'enable_public_decks' => env('FEATURE_PUBLIC_DECKS', true),
        'enable_deck_sharing' => env('FEATURE_DECK_SHARING', true),
        'enable_statistics' => env('FEATURE_STATISTICS', true),
        'enable_export' => env('FEATURE_EXPORT', false),
    ],
];
