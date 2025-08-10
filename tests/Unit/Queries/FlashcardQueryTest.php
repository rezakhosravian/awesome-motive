<?php

namespace Tests\Unit\Queries;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use App\Queries\FlashcardQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashcardQueryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Deck $deck;
    private Deck $otherDeck;
    private Flashcard $flashcard1;
    private Flashcard $flashcard2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->deck = Deck::factory()->for($this->user)->create();
        $this->otherDeck = Deck::factory()->for($this->user)->create();
        
        $this->flashcard1 = Flashcard::factory()->for($this->deck)->create([
            'question' => 'What is PHP?',
            'answer' => 'Programming language'
        ]);
        $this->flashcard2 = Flashcard::factory()->for($this->deck)->create([
            'question' => 'What is Laravel?',
            'answer' => 'PHP framework'
        ]);
    }

    public function test_constructor_creates_query()
    {
        $query = new FlashcardQuery();
        
        $this->assertInstanceOf(FlashcardQuery::class, $query);
    }

    public function test_constructor_with_custom_builder()
    {
        $builder = Flashcard::where('id', 1);
        $query = new FlashcardQuery($builder);
        
        $this->assertInstanceOf(FlashcardQuery::class, $query);
    }

    public function test_make_static_factory()
    {
        $query = FlashcardQuery::make();
        
        $this->assertInstanceOf(FlashcardQuery::class, $query);
    }

    public function test_for_deck_filters_by_deck()
    {
        $otherFlashcard = Flashcard::factory()->for($this->otherDeck)->create();

        $results = FlashcardQuery::make()
            ->forDeck($this->deck)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($this->flashcard1));
        $this->assertTrue($results->contains($this->flashcard2));
        $this->assertFalse($results->contains($otherFlashcard));
    }

    public function test_for_deck_id_filters_by_deck_id()
    {
        $otherFlashcard = Flashcard::factory()->for($this->otherDeck)->create();

        $results = FlashcardQuery::make()
            ->forDeckId($this->deck->id)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains($this->flashcard1));
        $this->assertTrue($results->contains($this->flashcard2));
        $this->assertFalse($results->contains($otherFlashcard));
    }

    public function test_search_by_question()
    {
        $results = FlashcardQuery::make()
            ->search('PHP')
            ->get();

        $this->assertCount(2, $results); // Both contain PHP
        $this->assertTrue($results->contains($this->flashcard1));
        $this->assertTrue($results->contains($this->flashcard2));
    }

    public function test_search_by_answer()
    {
        $results = FlashcardQuery::make()
            ->search('framework')
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($this->flashcard2));
    }

    public function test_search_case_insensitive()
    {
        $results = FlashcardQuery::make()
            ->search('programming')
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($this->flashcard1));
    }

    public function test_with_deck()
    {
        $results = FlashcardQuery::make()
            ->withDeck()
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->first()->relationLoaded('deck'));
    }

    public function test_random()
    {
        $results = FlashcardQuery::make()
            ->random()
            ->get();

        $this->assertCount(2, $results);
        // Random order means we can't predict exact order, but count should be same
    }

    public function test_order_by_latest()
    {
        $results = FlashcardQuery::make()
            ->orderByLatest()
            ->get();

        $this->assertCount(2, $results);
        // Latest flashcard should be first
        $this->assertEquals($this->flashcard2->id, $results->first()->id);
    }

    public function test_order_by_oldest()
    {
        $results = FlashcardQuery::make()
            ->orderByOldest()
            ->get();

        $this->assertCount(2, $results);
        // Oldest flashcard should be first
        $this->assertEquals($this->flashcard1->id, $results->first()->id);
    }

    public function test_limit()
    {
        $results = FlashcardQuery::make()
            ->limit(1)
            ->get();

        $this->assertCount(1, $results);
    }

    public function test_get()
    {
        $results = FlashcardQuery::make()->get();

        $this->assertCount(2, $results);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    public function test_paginate()
    {
        $results = FlashcardQuery::make()->paginate(1);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $results);
        $this->assertEquals(1, $results->perPage());
        $this->assertEquals(2, $results->total());
    }

    public function test_first()
    {
        $result = FlashcardQuery::make()
            ->forDeck($this->deck)
            ->orderByLatest()
            ->first();

        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals($this->flashcard2->id, $result->id);
    }

    public function test_first_returns_null()
    {
        $result = FlashcardQuery::make()
            ->forDeckId(9999)
            ->first();

        $this->assertNull($result);
    }

    public function test_find()
    {
        $result = FlashcardQuery::make()->find($this->flashcard1->id);

        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals($this->flashcard1->id, $result->id);
    }

    public function test_find_returns_null()
    {
        $result = FlashcardQuery::make()->find(9999);

        $this->assertNull($result);
    }

    public function test_find_or_fail()
    {
        $result = FlashcardQuery::make()->findOrFail($this->flashcard1->id);

        $this->assertInstanceOf(Flashcard::class, $result);
        $this->assertEquals($this->flashcard1->id, $result->id);
    }

    public function test_find_or_fail_throws_exception()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        FlashcardQuery::make()->findOrFail(9999);
    }

    public function test_count()
    {
        $count = FlashcardQuery::make()->count();

        $this->assertEquals(2, $count);
    }

    public function test_count_with_filters()
    {
        $count = FlashcardQuery::make()
            ->forDeck($this->deck)
            ->search('Laravel')
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_exists()
    {
        $exists = FlashcardQuery::make()
            ->forDeck($this->deck)
            ->exists();

        $this->assertTrue($exists);
    }

    public function test_exists_returns_false()
    {
        $exists = FlashcardQuery::make()
            ->forDeckId(9999)
            ->exists();

        $this->assertFalse($exists);
    }

    public function test_get_query()
    {
        $query = FlashcardQuery::make();
        $builder = $query->getQuery();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $builder);
    }

    public function test_method_chaining()
    {
        $results = FlashcardQuery::make()
            ->forDeck($this->deck)
            ->search('PHP')
            ->withDeck()
            ->orderByLatest()
            ->limit(10)
            ->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->first()->relationLoaded('deck'));
    }

    public function test_complex_search_scenario()
    {
        // Create more test data
        $specialFlashcard = Flashcard::factory()->for($this->deck)->create([
            'question' => 'Advanced PHP Concepts',
            'answer' => 'Object-oriented programming'
        ]);

        $results = FlashcardQuery::make()
            ->forDeck($this->deck)
            ->search('Advanced')
            ->withDeck()
            ->orderByLatest()
            ->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($specialFlashcard));
        $this->assertTrue($results->first()->relationLoaded('deck'));
    }

    public function test_random_with_limit()
    {
        // Create more flashcards for better random testing
        Flashcard::factory(5)->for($this->deck)->create();

        $results = FlashcardQuery::make()
            ->forDeck($this->deck)
            ->random()
            ->limit(3)
            ->get();

        $this->assertCount(3, $results);
    }

    public function test_search_no_results()
    {
        $results = FlashcardQuery::make()
            ->search('Non-existent term')
            ->get();

        $this->assertCount(0, $results);
    }

    public function test_pagination_with_custom_per_page()
    {
        $results = FlashcardQuery::make()->paginate(5);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $results);
        $this->assertEquals(5, $results->perPage());
        $this->assertEquals(2, $results->total());
    }
}
