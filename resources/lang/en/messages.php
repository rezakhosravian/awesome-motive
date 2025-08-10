<?php

return [
    'deck' => [
        'created' => "Deck ':name' created successfully!",
        'updated' => 'Deck updated successfully!',
        'deleted' => 'Deck deleted successfully!',
        'not_found' => 'Deck not found or you do not have permission to access it.',
        'no_flashcards' => 'This deck has no flashcards to study!',
        'empty_for_study' => 'Deck has no flashcards available for study.',
        'access_denied' => 'You do not have permission to access this deck.',
        'update_permission_denied' => 'Deck not found or you do not have permission to update it.',
        'delete_permission_denied' => 'Deck not found or you do not have permission to delete it.',
    ],

    'flashcard' => [
        'created' => 'Flashcard created successfully!',
        'updated' => 'Flashcard updated successfully!',
        'deleted' => 'Flashcard deleted successfully!',
        'not_found' => 'Flashcard not found.',
        'not_found_in_deck' => 'Flashcard not found in this deck.',
        'create_permission_denied' => 'You do not have permission to create flashcards in this deck.',
        'update_permission_denied' => 'You do not have permission to update flashcards in this deck.',
        'delete_permission_denied' => 'You do not have permission to delete flashcards in this deck.',
    ],

    'api_token' => [
        'created' => 'API token created successfully',
        'deleted' => 'API token deleted successfully!',
        'not_found' => 'API token not found.',
        'unauthorized' => 'You are not authorized to perform this action.',
        'limit_reached' => 'You have reached the maximum number of API tokens.',
        'expired' => 'API token has expired.',
        'invalid' => 'Invalid API token.',
    ],

    'auth' => [
        'unauthorized' => 'Authentication required',
        'access_denied' => 'Access denied',
        'login_required' => 'Please log in to continue',
        'invalid_credentials' => 'Invalid credentials provided',
        'current_password_incorrect' => 'The current password is incorrect.',
        'password_incorrect' => 'The password is incorrect.',
    ],

    'validation' => [
        'deck_name_required' => 'Deck name cannot be empty',
        'deck_name_max_length' => 'Deck name cannot exceed 255 characters',
        'deck_description_max_length' => 'Deck description cannot exceed 1000 characters',
        'flashcard_question_required' => 'Flashcard question cannot be empty',
        'flashcard_answer_required' => 'Flashcard answer cannot be empty',
        'flashcard_question_max_length' => 'Flashcard question cannot exceed 1000 characters',
        'flashcard_answer_max_length' => 'Flashcard answer cannot exceed 1000 characters',
    ],

    'general' => [
        'success' => 'Operation completed successfully',
        'error' => 'An error occurred',
        'not_found' => 'Resource not found',
        'internal_error' => 'Internal server error occurred',
        'bad_request' => 'Bad request',
    ],
];
