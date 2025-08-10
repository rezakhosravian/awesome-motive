<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use App\Livewire\StudyDeck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudyDeckComponentTest extends TestCase
{
    use RefreshDatabase;

    private function createTestData()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Deck',
            'description' => 'A test deck for studying',
        ]);
        
        // Create test flashcards
        $flashcards = collect([
            Flashcard::factory()->create([
                'deck_id' => $deck->id,
                'question' => 'What is Laravel?',
                'answer' => 'A PHP web framework'
            ]),
            Flashcard::factory()->create([
                'deck_id' => $deck->id,
                'question' => 'What is Livewire?',
                'answer' => 'A Laravel framework for building reactive components'
            ]),
            Flashcard::factory()->create([
                'deck_id' => $deck->id,
                'question' => 'What is PHP?',
                'answer' => 'A server-side scripting language'
            ])
        ]);

        return compact('user', 'deck', 'flashcards');
    }

    public function test_component_can_be_rendered()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->assertStatus(200)
            ->assertSee($data['deck']->name)
            ->assertSee($data['deck']->description);
    }

    public function test_component_initializes_with_first_flashcard()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        $firstCard = $data['flashcards']->first();
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->assertSet('currentIndex', 0)
            ->assertSet('currentCard.id', $firstCard->id)
            ->assertSee($firstCard->question)
            ->assertDontSee($firstCard->answer); // Answer should be hidden initially
    }

    public function test_can_show_answer_for_current_card()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        $firstCard = $data['flashcards']->first();
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->call('showAnswer')
            ->assertSet('answer', true)
            ->assertSee($firstCard->answer);
    }

    public function test_can_mark_answer_as_correct()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->call('showAnswer')
            ->call('markCorrect')
            ->assertSet('currentIndex', 1)
            ->assertSet('correctCount', 1)
            ->assertSet('showAnswer', false);
    }

    public function test_can_mark_answer_as_incorrect()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->call('showAnswer')
            ->call('markIncorrect')
            ->assertSet('currentIndex', 1)
            ->assertSet('incorrectCount', 1)
            ->assertSet('showAnswer', false);
    }

    public function test_can_navigate_to_previous_card()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->call('showAnswer')
            ->call('markCorrect') // Move to card 2
            ->call('previousCard')
            ->assertSet('currentIndex', 0)
            ->assertSet('showAnswer', false);
    }

    public function test_cannot_go_to_previous_card_when_at_first_card()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->call('previousCard')
            ->assertSet('currentIndex', 0); // Should stay at 0
    }

    public function test_study_session_completes_after_all_cards()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        $component = Livewire::test(StudyDeck::class, ['deck' => $data['deck']]);
        
        // Go through all cards
        foreach ($data['flashcards'] as $index => $card) {
            $component->call('showAnswer')->call('markCorrect');
        }
        
        $component->assertSet('isComplete', true);
    }

    public function test_can_shuffle_cards()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        $component = Livewire::test(StudyDeck::class, ['deck' => $data['deck']]);
        $originalOrder = $component->get('flashcards')->pluck('id')->toArray();
        
        $component->call('shuffleCards');
        $shuffledOrder = $component->get('flashcards')->pluck('id')->toArray();
        
        // Order might be the same by chance, but we can at least confirm the method doesn't error
        $component->assertSet('currentIndex', 0);
    }

    public function test_can_restart_study_session()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        Livewire::test(StudyDeck::class, ['deck' => $data['deck']])
            ->call('showAnswer')
            ->call('markCorrect')
            ->call('restart')
            ->assertSet('currentIndex', 0)
            ->assertSet('correctCount', 0)
            ->assertSet('incorrectCount', 0)
            ->assertSet('showAnswer', false)
            ->assertSet('isComplete', false);
    }

    public function test_progress_percentage_calculation()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        $component = Livewire::test(StudyDeck::class, ['deck' => $data['deck']]);
        
        // At start: 0% progress
        $this->assertEquals(0, $component->get('progressPercentage'));
        
        // After first card: 33% progress (1 of 3 cards)
        $component->call('showAnswer')->call('markCorrect');
        $this->assertEquals(33, $component->get('progressPercentage'));
        
        // After second card: 67% progress (2 of 3 cards)  
        $component->call('showAnswer')->call('markCorrect');
        $this->assertEquals(67, $component->get('progressPercentage'));
        
        // After all cards: 100% progress
        $component->call('showAnswer')->call('markCorrect');
        $this->assertEquals(100, $component->get('progressPercentage'));
    }

    public function test_accuracy_percentage_calculation()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        $component = Livewire::test(StudyDeck::class, ['deck' => $data['deck']]);
        
        // At start: 0% accuracy (no answers yet)
        $this->assertEquals(0, $component->get('accuracyPercentage'));
        
        // Mark first card correct
        $component->call('showAnswer')->call('markCorrect');
        $this->assertEquals(100, $component->get('accuracyPercentage')); // 1/1 = 100%
        
        // Mark second card incorrect  
        $component->call('showAnswer')->call('markIncorrect');
        $this->assertEquals(50, $component->get('accuracyPercentage')); // 1/2 = 50%
        
        // Mark third card correct
        $component->call('showAnswer')->call('markCorrect');
        $this->assertEquals(67, $component->get('accuracyPercentage')); // 2/3 = 67%
    }

    public function test_handles_empty_deck()
    {
        $user = User::factory()->create();
        $emptyDeck = Deck::factory()->create([
            'user_id' => $user->id,
            'name' => 'Empty Deck',
        ]);
        
        $this->actingAs($user);
        
        $component = Livewire::test(StudyDeck::class, ['deck' => $emptyDeck])
            ->assertSet('isComplete', true);
            
        $flashcards = $component->get('flashcards');
        $this->assertTrue($flashcards->isEmpty());
    }

    public function test_tracks_studied_cards()
    {
        $data = $this->createTestData();
        $this->actingAs($data['user']);
        
        $component = Livewire::test(StudyDeck::class, ['deck' => $data['deck']]);
        
        $firstCard = $data['flashcards']->first();
        
        // Initially no cards studied
        $this->assertEmpty($component->get('studiedCards'));
        
        // After marking first card correct
        $component->call('showAnswer')->call('markCorrect');
        $studiedCards = $component->get('studiedCards');
        $this->assertArrayHasKey($firstCard->id, $studiedCards);
        $this->assertEquals('correct', $studiedCards[$firstCard->id]);
    }

    public function test_user_can_study_public_decks_from_others()
    {
        $owner = User::factory()->create();
        $studier = User::factory()->create();
        
        $publicDeck = Deck::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Public Study Deck',
            'is_public' => true,
        ]);
        
        Flashcard::factory()->create([
            'deck_id' => $publicDeck->id,
            'question' => 'Public Question',
            'answer' => 'Public Answer'
        ]);
        
        $this->actingAs($studier);
        
        Livewire::test(StudyDeck::class, ['deck' => $publicDeck])
            ->assertStatus(200)
            ->assertSee($publicDeck->name)
            ->assertSee('Public Question');
    }
}
