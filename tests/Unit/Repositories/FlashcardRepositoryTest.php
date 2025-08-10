<?php

namespace Tests\Unit\Repositories;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use App\Repositories\FlashcardRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashcardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected FlashcardRepository $repository;
    protected User $user;
    protected Deck $deck;
    protected Deck $otherDeck;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new FlashcardRepository(new Flashcard());
        $this->user = User::factory()->create();
        
        $this->deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Deck'
        ]);
        
        $this->otherDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Other Deck'
        ]);
    }

    public function test_constructor_sets_correct_model()
    {
        $repository = new FlashcardRepository(new Flashcard());
        $this->assertInstanceOf(FlashcardRepository::class, $repository);
    }

    public function test_get_by_deck_returns_deck_flashcards_only()
    {
        // Create flashcards for the main deck
        Flashcard::factory()->count(3)->create([
            'deck_id' => $this->deck->id
        ]);
        
        // Create flashcards for other deck
        Flashcard::factory()->count(2)->create([
            'deck_id' => $this->otherDeck->id
        ]);

        $flashcards = $this->repository->getByDeck($this->deck);

        $this->assertCount(3, $flashcards);
        $flashcards->each(function ($flashcard) {
            $this->assertEquals($this->deck->id, $flashcard->deck_id);
        });
    }

    public function test_get_by_deck_returns_latest_first()
    {
        // Create flashcards with different created times
        $oldest = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Oldest Question',
            'created_at' => now()->subDays(2)
        ]);
        
        $middle = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Middle Question',
            'created_at' => now()->subDay()
        ]);
        
        $newest = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Newest Question',
            'created_at' => now()
        ]);

        $flashcards = $this->repository->getByDeck($this->deck);

        $this->assertEquals($newest->id, $flashcards->first()->id);
        $this->assertEquals($oldest->id, $flashcards->last()->id);
    }

    public function test_get_by_deck_returns_empty_collection_for_empty_deck()
    {
        $flashcards = $this->repository->getByDeck($this->deck);

        $this->assertCount(0, $flashcards);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $flashcards);
    }

    public function test_get_by_deck_paginated_returns_paginated_results()
    {
        // Create 25 flashcards
        Flashcard::factory()->count(25)->create([
            'deck_id' => $this->deck->id
        ]);

        $paginatedFlashcards = $this->repository->getByDeckPaginated($this->deck, 10);

        $this->assertEquals(10, $paginatedFlashcards->count());
        $this->assertEquals(25, $paginatedFlashcards->total());
        $this->assertEquals(3, $paginatedFlashcards->lastPage());
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginatedFlashcards);
    }

    public function test_get_by_deck_paginated_uses_default_per_page()
    {
        // Create 20 flashcards
        Flashcard::factory()->count(20)->create([
            'deck_id' => $this->deck->id
        ]);

        $paginatedFlashcards = $this->repository->getByDeckPaginated($this->deck);

        $this->assertEquals(15, $paginatedFlashcards->count()); // Default perPage is 15
        $this->assertEquals(20, $paginatedFlashcards->total());
    }

    public function test_get_by_deck_paginated_returns_latest_first()
    {
        $oldest = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Oldest',
            'created_at' => now()->subDays(2)
        ]);
        
        $newest = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Newest',
            'created_at' => now()
        ]);

        $paginatedFlashcards = $this->repository->getByDeckPaginated($this->deck, 10);

        $this->assertEquals($newest->id, $paginatedFlashcards->first()->id);
    }

    public function test_get_by_deck_and_id_returns_correct_flashcard()
    {
        $targetFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Target Question'
        ]);
        
        // Create other flashcards in same deck
        Flashcard::factory()->count(2)->create([
            'deck_id' => $this->deck->id
        ]);
        
        // Create flashcard in other deck with same ID structure
        Flashcard::factory()->create([
            'deck_id' => $this->otherDeck->id
        ]);

        $foundFlashcard = $this->repository->getByDeckAndId($this->deck, $targetFlashcard->id);

        $this->assertNotNull($foundFlashcard);
        $this->assertEquals($targetFlashcard->id, $foundFlashcard->id);
        $this->assertEquals($this->deck->id, $foundFlashcard->deck_id);
        $this->assertEquals('Target Question', $foundFlashcard->question);
    }

    public function test_get_by_deck_and_id_returns_null_for_wrong_deck()
    {
        $flashcardInOtherDeck = Flashcard::factory()->create([
            'deck_id' => $this->otherDeck->id
        ]);

        $foundFlashcard = $this->repository->getByDeckAndId($this->deck, $flashcardInOtherDeck->id);

        $this->assertNull($foundFlashcard);
    }

    public function test_get_by_deck_and_id_returns_null_for_nonexistent_id()
    {
        $foundFlashcard = $this->repository->getByDeckAndId($this->deck, 99999);

        $this->assertNull($foundFlashcard);
    }

    public function test_create_for_deck_creates_flashcard_with_correct_deck_id()
    {
        $data = [
            'question' => 'Test Question',
            'answer' => 'Test Answer'
        ];

        $flashcard = $this->repository->createForDeck($this->deck, $data);

        $this->assertInstanceOf(Flashcard::class, $flashcard);
        $this->assertEquals($this->deck->id, $flashcard->deck_id);
        $this->assertEquals('Test Question', $flashcard->question);
        $this->assertEquals('Test Answer', $flashcard->answer);
        
        $this->assertDatabaseHas('flashcards', [
            'deck_id' => $this->deck->id,
            'question' => 'Test Question',
            'answer' => 'Test Answer'
        ]);
    }

    public function test_create_for_deck_overrides_deck_id_in_data()
    {
        $data = [
            'question' => 'Test Question',
            'answer' => 'Test Answer',
            'deck_id' => $this->otherDeck->id // This should be overridden
        ];

        $flashcard = $this->repository->createForDeck($this->deck, $data);

        $this->assertEquals($this->deck->id, $flashcard->deck_id);
        $this->assertNotEquals($this->otherDeck->id, $flashcard->deck_id);
    }

    public function test_get_random_from_deck_returns_random_flashcards()
    {
        // Create flashcards
        $flashcards = Flashcard::factory()->count(10)->create([
            'deck_id' => $this->deck->id
        ]);

        $randomFlashcards = $this->repository->getRandomFromDeck($this->deck);

        $this->assertCount(10, $randomFlashcards);
        
        // All should belong to the deck
        $randomFlashcards->each(function ($flashcard) {
            $this->assertEquals($this->deck->id, $flashcard->deck_id);
        });
    }

    public function test_get_random_from_deck_with_limit()
    {
        Flashcard::factory()->count(10)->create([
            'deck_id' => $this->deck->id
        ]);

        $randomFlashcards = $this->repository->getRandomFromDeck($this->deck, 5);

        $this->assertCount(5, $randomFlashcards);
        
        $randomFlashcards->each(function ($flashcard) {
            $this->assertEquals($this->deck->id, $flashcard->deck_id);
        });
    }

    public function test_get_random_from_deck_without_limit()
    {
        Flashcard::factory()->count(7)->create([
            'deck_id' => $this->deck->id
        ]);

        $randomFlashcards = $this->repository->getRandomFromDeck($this->deck, null);

        $this->assertCount(7, $randomFlashcards);
    }

    public function test_get_random_from_deck_returns_empty_for_empty_deck()
    {
        $randomFlashcards = $this->repository->getRandomFromDeck($this->deck);

        $this->assertCount(0, $randomFlashcards);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $randomFlashcards);
    }

    public function test_get_random_from_deck_limit_larger_than_available()
    {
        Flashcard::factory()->count(3)->create([
            'deck_id' => $this->deck->id
        ]);

        $randomFlashcards = $this->repository->getRandomFromDeck($this->deck, 10);

        $this->assertCount(3, $randomFlashcards); // Should return all available
    }

    public function test_count_by_deck_returns_correct_count()
    {
        Flashcard::factory()->count(5)->create([
            'deck_id' => $this->deck->id
        ]);
        
        // Create flashcards in other deck (should not be counted)
        Flashcard::factory()->count(3)->create([
            'deck_id' => $this->otherDeck->id
        ]);

        $count = $this->repository->countByDeck($this->deck);

        $this->assertEquals(5, $count);
    }

    public function test_count_by_deck_returns_zero_for_empty_deck()
    {
        $count = $this->repository->countByDeck($this->deck);

        $this->assertEquals(0, $count);
    }

    public function test_repository_inherits_base_repository_methods()
    {
        // Test that base repository methods are available
        $this->assertTrue(method_exists($this->repository, 'create'));
        $this->assertTrue(method_exists($this->repository, 'update'));
        $this->assertTrue(method_exists($this->repository, 'delete'));
        $this->assertTrue(method_exists($this->repository, 'find'));
        $this->assertTrue(method_exists($this->repository, 'findOrFail'));
    }

    public function test_repository_uses_flashcard_model()
    {
        $data = [
            'question' => 'Base Repository Test',
            'answer' => 'Testing inheritance',
            'deck_id' => $this->deck->id
        ];

        $flashcard = $this->repository->create($data);

        $this->assertInstanceOf(Flashcard::class, $flashcard);
        $this->assertEquals('Base Repository Test', $flashcard->question);
    }

    public function test_complex_scenario_multiple_operations()
    {
        // Create flashcards
        $this->repository->createForDeck($this->deck, [
            'question' => 'Q1',
            'answer' => 'A1'
        ]);
        
        $flashcard2 = $this->repository->createForDeck($this->deck, [
            'question' => 'Q2',
            'answer' => 'A2'
        ]);

        // Test count
        $this->assertEquals(2, $this->repository->countByDeck($this->deck));

        // Test retrieval
        $flashcards = $this->repository->getByDeck($this->deck);
        $this->assertCount(2, $flashcards);

        // Test specific retrieval
        $found = $this->repository->getByDeckAndId($this->deck, $flashcard2->id);
        $this->assertEquals('Q2', $found->question);

        // Test random
        $random = $this->repository->getRandomFromDeck($this->deck, 1);
        $this->assertCount(1, $random);
        $this->assertEquals($this->deck->id, $random->first()->deck_id);
    }
}