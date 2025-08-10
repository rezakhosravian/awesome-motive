<?php

namespace Tests\Unit;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    public function test_deck_belongs_to_user()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $deck->user);
        $this->assertEquals($user->id, $deck->user->id);
    }

    public function test_deck_has_many_flashcards()
    {
        $deck = Deck::factory()->create();
        $flashcard1 = Flashcard::factory()->create(['deck_id' => $deck->id]);
        $flashcard2 = Flashcard::factory()->create(['deck_id' => $deck->id]);

        $this->assertCount(2, $deck->flashcards);
        $this->assertInstanceOf(Flashcard::class, $deck->flashcards->first());
    }

    public function test_public_scope_returns_only_public_decks()
    {
        $publicDeck = Deck::factory()->create(['is_public' => true]);
        $privateDeck = Deck::factory()->create(['is_public' => false]);

        $publicDecks = Deck::public()->get();

        $this->assertCount(1, $publicDecks);
        $this->assertTrue($publicDecks->first()->is_public);
        $this->assertEquals($publicDeck->id, $publicDecks->first()->id);
    }

    public function test_flashcard_count_with_eager_loading()
    {
        $deck = Deck::factory()->create();
        Flashcard::factory()->count(5)->create(['deck_id' => $deck->id]);

        $deckWithCount = Deck::withCount('flashcards')->find($deck->id);

        $this->assertEquals(5, $deckWithCount->flashcards_count);
    }

    public function test_is_public_is_cast_to_boolean()
    {
        $deck = Deck::factory()->create(['is_public' => 1]);

        $this->assertIsBool($deck->is_public);
        $this->assertTrue($deck->is_public);

        $deck = Deck::factory()->create(['is_public' => 0]);
        $this->assertIsBool($deck->is_public);
        $this->assertFalse($deck->is_public);
    }

    public function test_fillable_attributes()
    {
        $fillable = [
            'user_id',
            'name',
            'slug',
            'description',
            'is_public',
        ];

        $deck = new Deck();

        $this->assertEquals($fillable, $deck->getFillable());
    }

    public function test_deck_creation_with_all_attributes()
    {
        $user = User::factory()->create();
        
        $deckData = [
            'user_id' => $user->id,
            'name' => 'Test Deck',
            'description' => 'A test deck for studying',
            'is_public' => true,
        ];

        $deck = Deck::create($deckData);

        $this->assertDatabaseHas('decks', $deckData);
        $this->assertEquals('Test Deck', $deck->name);
        $this->assertEquals('A test deck for studying', $deck->description);
        $this->assertTrue($deck->is_public);
        $this->assertEquals($user->id, $deck->user_id);
    }

    public function test_deck_can_be_updated()
    {
        $deck = Deck::factory()->create([
            'name' => 'Original Name',
            'is_public' => false
        ]);

        $deck->update([
            'name' => 'Updated Name',
            'is_public' => true
        ]);

        $this->assertEquals('Updated Name', $deck->fresh()->name);
        $this->assertTrue($deck->fresh()->is_public);
    }

    public function test_deck_deletion_cascades_to_flashcards()
    {
        $deck = Deck::factory()->create();
        $flashcard = Flashcard::factory()->create(['deck_id' => $deck->id]);

        $this->assertDatabaseHas('flashcards', ['id' => $flashcard->id]);

        $deck->delete();

        $this->assertDatabaseMissing('decks', ['id' => $deck->id]);
        $this->assertDatabaseMissing('flashcards', ['id' => $flashcard->id]);
    }

    public function test_deck_timestamps_are_set()
    {
        $deck = Deck::factory()->create();

        $this->assertNotNull($deck->created_at);
        $this->assertNotNull($deck->updated_at);
    }

    public function test_deck_name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Deck::create([
            'user_id' => User::factory()->create()->id,
            'description' => 'Test description',
            'is_public' => false,
            // Missing 'name'
        ]);
    }

    public function test_deck_user_id_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Deck::create([
            'name' => 'Test Deck',
            'description' => 'Test description',
            'is_public' => false,
            // Missing 'user_id'
        ]);
    }
}
