<?php

return [
    'responses' => [
        'success' => 'Operation completed successfully',
        'created' => 'Resource created successfully',
        'updated' => 'Resource updated successfully',
        'deleted' => 'Resource deleted successfully',
        'error' => 'An error occurred while processing your request',
        'unauthorized' => 'Authentication credentials are required or invalid.',
        'forbidden' => 'You do not have permission to perform this action',
        'not_found' => 'The requested resource was not found',
        'bad_request' => 'Invalid request parameters',
        'validation_error' => 'Validation failed',
        'too_many_requests' => 'Too many requests. Please slow down.',
    ],
    
    'auth' => [
        'test_success' => 'Authentication successful',
        'unauthorized' => 'Invalid or missing authentication token',
        'forbidden' => 'You do not have permission to perform this action',
        'rate_limit_exceeded' => 'Too many requests. Please slow down.',
    ],
    
    'decks' => [
        'index_success' => 'Public decks retrieved successfully',
        'show_success' => 'Deck retrieved successfully',
        'created_success' => 'Deck created successfully',
        'updated_success' => 'Deck updated successfully',
        'deleted_success' => 'Deck deleted successfully',
        'search_success' => 'Search completed successfully',
        'not_found' => 'Deck not found or not accessible',
        'update_forbidden' => 'You do not have permission to update this deck',
        'delete_forbidden' => 'You do not have permission to delete this deck',
        'creation_failed' => 'Failed to create deck. Please try again.',
        'cannot_study' => 'This deck cannot be studied at the moment',
        'cannot_delete_with_flashcards' => 'Cannot delete deck that contains flashcards',
    ],
    
    'flashcards' => [
        'index_success' => 'Flashcards retrieved successfully',
        'show_success' => 'Flashcard retrieved successfully',
        'created_success' => 'Flashcard created successfully',
        'updated_success' => 'Flashcard updated successfully',
        'deleted_success' => 'Flashcard deleted successfully',
        'not_found' => 'Flashcard not found or not accessible',
        'store_forbidden' => 'You do not have permission to add flashcards to this deck',
        'update_forbidden' => 'You do not have permission to update flashcards in this deck',
        'delete_forbidden' => 'You do not have permission to delete flashcards from this deck',
        'creation_failed' => 'Failed to create flashcard. Please try again.',
    ],
    
    'tokens' => [
        'index_success' => 'API tokens retrieved successfully',
        'created_success' => 'API token created successfully',
        'deleted_success' => 'API token deleted successfully',
        'rate_limit_exceeded' => 'You have reached the maximum number of API tokens allowed',
        'creation_failed' => 'Failed to create API token. Please try again.',
    ],
];