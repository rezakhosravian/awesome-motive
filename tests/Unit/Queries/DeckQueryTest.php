<?php

namespace Tests\Unit\Queries;

use App\Models\Deck;
use App\Models\User;
use App\Queries\DeckQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckQueryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Deck $publicDeck;
    private Deck $privateDeck;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->publicDeck = Deck::factory()->for($this->user)->create(['is_public' => true, 'name' => 'Public Deck']);
        $this->privateDeck = Deck::factory()->for($this->user)->create(['is_public' => false, 'name' => 'Private Deck']);
    }

    public function test_constructor_creates_query()
    {
        $query = new DeckQuery();
        
        $this->assertInstanceOf(DeckQuery::class, $query);
    }

    public function test_constructor_with_custom_builder()
    {
        $builder = Deck::where('id', 1);
        $query = new DeckQuery($builder);
        
        $this->assertInstanceOf(DeckQuery::class, $query);
    }

    public function test_make_static_factory()
    {
        $query = DeckQuery::make();
        
        $this->assertInstanceOf(DeckQuery::class, $query);
    }

    public function test_for_user_filters_by_user()
    {
        $otherDeck = Deck::factory()->for($this->otherUser)->create();

        $results = DeckQuery::make()
            ->forUser($this->user)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($this->publicDeck));
        $this->assertTrue($results->contains($this->privateDeck));
        $this->assertFalse($results->contains($otherDeck));
    }

    public function test_public_only_filters()
    {
        $results = DeckQuery::make()
            ->publicOnly()
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($this->publicDeck));
        $this->assertFalse($results->contains($this->privateDeck));
    }

    public function test_private_only_filters()
    {
        $results = DeckQuery::make()
            ->privateOnly()
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($this->privateDeck));
        $this->assertFalse($results->contains($this->publicDeck));
    }

    public function test_search_by_name()
    {
        $results = DeckQuery::make()
            ->search('Public')
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($this->publicDeck));
    }

    public function test_search_by_description()
    {
        $deck = Deck::factory()->for($this->user)->create([
            'name' => 'Test Deck',
            'description' => 'Searchable description'
        ]);

        $results = DeckQuery::make()
            ->search('Searchable')
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($deck));
    }

    public function test_with_flashcard_count()
    {
        $results = DeckQuery::make()
            ->withFlashcardCount()
            ->get();

        $this->assertCount(2, $results);
        $this->assertArrayHasKey('flashcards_count', $results->first()->toArray());
    }

    public function test_with_user()
    {
        $results = DeckQuery::make()
            ->withUser()
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->first()->relationLoaded('user'));
    }

    public function test_with_flashcards()
    {
        $results = DeckQuery::make()
            ->withFlashcards()
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->first()->relationLoaded('flashcards'));
    }

    public function test_has_flashcards()
    {
        // Create deck with flashcards
        $deckWithCards = Deck::factory()->for($this->user)->hasFlashcards(3)->create();

        $results = DeckQuery::make()
            ->hasFlashcards()
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($deckWithCards));
    }

    public function test_without_flashcards()
    {
        // Our setup decks don't have flashcards
        $results = DeckQuery::make()
            ->withoutFlashcards()
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($this->publicDeck));
        $this->assertTrue($results->contains($this->privateDeck));
    }

    public function test_order_by_latest()
    {
        $results = DeckQuery::make()
            ->orderByLatest()
            ->get();

        $this->assertCount(2, $results);
        // Latest deck should be first
        $this->assertEquals($this->privateDeck->id, $results->first()->id);
    }

    public function test_order_by_oldest()
    {
        $results = DeckQuery::make()
            ->orderByOldest()
            ->get();

        $this->assertCount(2, $results);
        // Oldest deck should be first
        $this->assertEquals($this->publicDeck->id, $results->first()->id);
    }

    public function test_order_by_name()
    {
        $results = DeckQuery::make()
            ->orderByName()
            ->get();

        $this->assertCount(2, $results);
        // Private Deck comes before Public Deck alphabetically
        $this->assertEquals($this->privateDeck->id, $results->first()->id);
    }

    public function test_order_by_flashcard_count()
    {
        $results = DeckQuery::make()
            ->withFlashcardCount()
            ->orderByFlashcardCount()
            ->get();

        $this->assertCount(2, $results);
    }

    public function test_limit()
    {
        $results = DeckQuery::make()
            ->limit(1)
            ->get();

        $this->assertCount(1, $results);
    }

    public function test_get()
    {
        $results = DeckQuery::make()->get();

        $this->assertCount(2, $results);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    public function test_paginate()
    {
        $results = DeckQuery::make()->paginate(1);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $results);
        $this->assertEquals(1, $results->perPage());
        $this->assertEquals(2, $results->total());
    }

    public function test_first()
    {
        $result = DeckQuery::make()
            ->forUser($this->user)
            ->orderByLatest()
            ->first();

        $this->assertInstanceOf(Deck::class, $result);
        $this->assertEquals($this->privateDeck->id, $result->id);
    }

    public function test_first_returns_null()
    {
        $result = DeckQuery::make()
            ->forUser($this->otherUser)
            ->search('Non-existent')
            ->first();

        $this->assertNull($result);
    }

    public function test_find()
    {
        $result = DeckQuery::make()->find($this->publicDeck->id);

        $this->assertInstanceOf(Deck::class, $result);
        $this->assertEquals($this->publicDeck->id, $result->id);
    }

    public function test_find_returns_null()
    {
        $result = DeckQuery::make()->find(9999);

        $this->assertNull($result);
    }

    public function test_find_or_fail()
    {
        $result = DeckQuery::make()->findOrFail($this->publicDeck->id);

        $this->assertInstanceOf(Deck::class, $result);
        $this->assertEquals($this->publicDeck->id, $result->id);
    }

    public function test_find_or_fail_throws_exception()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        DeckQuery::make()->findOrFail(9999);
    }

    public function test_count()
    {
        $count = DeckQuery::make()->count();

        $this->assertEquals(2, $count);
    }

    public function test_count_with_filters()
    {
        $count = DeckQuery::make()
            ->forUser($this->user)
            ->publicOnly()
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_exists()
    {
        $exists = DeckQuery::make()
            ->forUser($this->user)
            ->exists();

        $this->assertTrue($exists);
    }

    public function test_exists_returns_false()
    {
        $exists = DeckQuery::make()
            ->forUser($this->otherUser)
            ->search('Non-existent')
            ->exists();

        $this->assertFalse($exists);
    }

    public function test_get_query()
    {
        $query = DeckQuery::make();
        $builder = $query->getQuery();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $builder);
    }

    public function test_method_chaining()
    {
        $results = DeckQuery::make()
            ->forUser($this->user)
            ->publicOnly()
            ->withFlashcardCount()
            ->orderByLatest()
            ->limit(10)
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($this->publicDeck));
    }

    public function test_complex_search_scenario()
    {
        // Create more test data
        $searchableDeck = Deck::factory()->for($this->user)->create([
            'name' => 'Learning Spanish',
            'description' => 'Spanish vocabulary cards',
            'is_public' => true
        ]);

        $results = DeckQuery::make()
            ->search('Spanish')
            ->publicOnly()
            ->withFlashcardCount()
            ->orderByName()
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($searchableDeck));
    }
}
