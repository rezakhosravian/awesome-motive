<?php

namespace Tests\Unit;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashcardModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    public function test_flashcard_belongs_to_deck()
    {
        $deck = Deck::factory()->create();
        $flashcard = Flashcard::factory()->create(['deck_id' => $deck->id]);

        $this->assertInstanceOf(Deck::class, $flashcard->deck);
        $this->assertEquals($deck->id, $flashcard->deck->id);
    }

    public function test_fillable_attributes()
    {
        $fillable = [
            'deck_id',
            'question',
            'answer',
        ];

        $flashcard = new Flashcard();

        $this->assertEquals($fillable, $flashcard->getFillable());
    }

    public function test_flashcard_creation_with_all_attributes()
    {
        $deck = Deck::factory()->create();
        
        $flashcardData = [
            'deck_id' => $deck->id,
            'question' => 'What is Laravel?',
            'answer' => 'A PHP web framework',
        ];

        $flashcard = Flashcard::create($flashcardData);

        $this->assertDatabaseHas('flashcards', $flashcardData);
        $this->assertEquals('What is Laravel?', $flashcard->question);
        $this->assertEquals('A PHP web framework', $flashcard->answer);
        $this->assertEquals($deck->id, $flashcard->deck_id);
    }

    public function test_flashcard_can_be_updated()
    {
        $flashcard = Flashcard::factory()->create([
            'question' => 'Original Question',
            'answer' => 'Original Answer'
        ]);

        $flashcard->update([
            'question' => 'Updated Question',
            'answer' => 'Updated Answer'
        ]);

        $this->assertEquals('Updated Question', $flashcard->fresh()->question);
        $this->assertEquals('Updated Answer', $flashcard->fresh()->answer);
    }

    public function test_flashcard_timestamps_are_set()
    {
        $flashcard = Flashcard::factory()->create();

        $this->assertNotNull($flashcard->created_at);
        $this->assertNotNull($flashcard->updated_at);
    }

    public function test_flashcard_deck_id_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Flashcard::create([
            'question' => 'Test Question',
            'answer' => 'Test Answer',
            // Missing 'deck_id'
        ]);
    }

    public function test_flashcard_question_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Flashcard::create([
            'deck_id' => Deck::factory()->create()->id,
            'answer' => 'Test Answer',
            // Missing 'question'
        ]);
    }

    public function test_flashcard_answer_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Flashcard::create([
            'deck_id' => Deck::factory()->create()->id,
            'question' => 'Test Question',
            // Missing 'answer'
        ]);
    }

    public function test_flashcard_deletion()
    {
        $flashcard = Flashcard::factory()->create();
        $flashcardId = $flashcard->id;

        $this->assertDatabaseHas('flashcards', ['id' => $flashcardId]);

        $flashcard->delete();

        $this->assertDatabaseMissing('flashcards', ['id' => $flashcardId]);
    }

    public function test_flashcard_relationship_with_deck_through_factory()
    {
        $deck = Deck::factory()->create();
        $flashcards = Flashcard::factory()->count(3)->create(['deck_id' => $deck->id]);

        $this->assertCount(3, $deck->fresh()->flashcards);
        
        foreach ($flashcards as $flashcard) {
            $this->assertEquals($deck->id, $flashcard->deck_id);
            $this->assertEquals($deck->id, $flashcard->deck->id);
        }
    }

    public function test_flashcard_can_handle_long_text()
    {
        $longQuestion = str_repeat('This is a very long question. ', 50);
        $longAnswer = str_repeat('This is a very long answer. ', 50);

        $flashcard = Flashcard::factory()->create([
            'question' => $longQuestion,
            'answer' => $longAnswer
        ]);

        $this->assertEquals($longQuestion, $flashcard->question);
        $this->assertEquals($longAnswer, $flashcard->answer);
    }

    public function test_multiple_flashcards_can_belong_to_same_deck()
    {
        $deck = Deck::factory()->create();
        
        $flashcard1 = Flashcard::factory()->create([
            'deck_id' => $deck->id,
            'question' => 'Question 1',
            'answer' => 'Answer 1'
        ]);
        
        $flashcard2 = Flashcard::factory()->create([
            'deck_id' => $deck->id,
            'question' => 'Question 2',
            'answer' => 'Answer 2'
        ]);

        $this->assertEquals($deck->id, $flashcard1->deck_id);
        $this->assertEquals($deck->id, $flashcard2->deck_id);
        $this->assertCount(2, $deck->fresh()->flashcards);
    }
}
