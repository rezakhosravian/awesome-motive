<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\DeckRepository;
use App\Models\Deck;
use App\Models\User;
use App\Models\Flashcard;

class DeckRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected DeckRepository $repository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        
        $this->repository = new DeckRepository(new Deck());
        $this->user = User::factory()->create();
    }

    public function test_get_user_decks_returns_user_specific_decks(): void
    {
        $userDecks = Deck::factory()->count(3)->for($this->user)->create();
        $otherUserDecks = Deck::factory()->count(2)->create();

        $result = $this->repository->getUserDecks($this->user);

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn($deck) => $deck->user_id === $this->user->id));
    }

    public function test_get_user_decks_paginated(): void
    {
        Deck::factory()->count(20)->for($this->user)->create();

        $result = $this->repository->getUserDecksPaginated($this->user, 5);

        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(20, $result->total());
        $this->assertEquals(4, $result->lastPage());
    }

    public function test_get_public_decks_returns_only_public_decks(): void
    {
        Deck::factory()->count(3)->create(['is_public' => true]);
        Deck::factory()->count(2)->create(['is_public' => false]);

        $result = $this->repository->getPublicDecks();

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn($deck) => $deck->is_public === true));
    }

    public function test_search_filters_by_name_and_description(): void
    {
        Deck::factory()->create(['name' => 'Math Basics', 'is_public' => true]);
        Deck::factory()->create(['description' => 'Advanced Math concepts', 'is_public' => true]);
        Deck::factory()->create(['name' => 'Science', 'is_public' => true]);

        $result = $this->repository->search('Math', true);

        $this->assertCount(2, $result);
    }

    public function test_search_respects_public_only_flag(): void
    {
        Deck::factory()->create(['name' => 'Math Basics', 'is_public' => true]);
        Deck::factory()->create(['name' => 'Math Advanced', 'is_public' => false]);

        $publicOnlyResult = $this->repository->search('Math', true);
        $allResult = $this->repository->search('Math', false);

        $this->assertCount(1, $publicOnlyResult);
        $this->assertCount(2, $allResult);
    }

    public function test_get_deck_with_flashcards(): void
    {
        $deck = Deck::factory()->for($this->user)->create();
        Flashcard::factory()->count(3)->for($deck)->create();

        $result = $this->repository->getDeckWithFlashcards($deck->id);

        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('flashcards'));
        $this->assertCount(3, $result->flashcards);
    }

    public function test_get_user_deck(): void
    {
        $userDeck = Deck::factory()->for($this->user)->create();
        $otherUserDeck = Deck::factory()->create();

        $result = $this->repository->getUserDeck($this->user, $userDeck->id);
        $invalidResult = $this->repository->getUserDeck($this->user, $otherUserDeck->id);

        $this->assertNotNull($result);
        $this->assertEquals($userDeck->id, $result->id);
        $this->assertNull($invalidResult);
    }

    public function test_base_repository_methods(): void
    {
        $deck = Deck::factory()->for($this->user)->create();

        // Test find
        $found = $this->repository->find($deck->id);
        $this->assertEquals($deck->id, $found->id);

        // Test findOrFail
        $foundOrFail = $this->repository->findOrFail($deck->id);
        $this->assertEquals($deck->id, $foundOrFail->id);

        // Test update
        $updated = $this->repository->update($deck, ['name' => 'Updated Name']);
        $this->assertEquals('Updated Name', $updated->name);

        // Test delete
        $this->assertTrue($this->repository->delete($deck));
        $this->assertNull($this->repository->find($deck->id));
    }

    public function test_find_by_criteria(): void
    {
        $publicDeck = Deck::factory()->for($this->user)->create(['is_public' => true]);
        $privateDeck = Deck::factory()->for($this->user)->create(['is_public' => false]);

        $result = $this->repository->findBy(['is_public' => true, 'user_id' => $this->user->id]);

        $this->assertCount(1, $result);
        $this->assertEquals($publicDeck->id, $result->first()->id);
    }
} 