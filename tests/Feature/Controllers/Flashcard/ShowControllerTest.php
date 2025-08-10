<?php

namespace Tests\Feature\Controllers\Flashcard;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Deck $deck;
    protected Deck $publicDeck;
    protected Deck $otherUserDeck;
    protected Flashcard $flashcard;
    protected Flashcard $publicFlashcard;
    protected Flashcard $otherFlashcard;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        // User's private deck
        $this->deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false
        ]);
        
        // User's public deck
        $this->publicDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => true
        ]);
        
        // Other user's deck
        $this->otherUserDeck = Deck::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_public' => false
        ]);
        
        // Flashcards
        $this->flashcard = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Test Question',
            'answer' => 'Test Answer'
        ]);
        
        $this->publicFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->publicDeck->id,
            'question' => 'Public Question',
            'answer' => 'Public Answer'
        ]);
        
        $this->otherFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->otherUserDeck->id,
            'question' => 'Other Question',
            'answer' => 'Other Answer'
        ]);
    }

    public function test_authenticated_user_can_view_own_flashcard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));

        $response->assertStatus(200)
                 ->assertViewIs('flashcards.show')
                 ->assertViewHas('deck', $this->deck)
                 ->assertViewHas('flashcard', $this->flashcard);
    }

    public function test_authenticated_user_can_view_public_flashcard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->publicDeck, $this->publicFlashcard]));

        $response->assertStatus(200)
                 ->assertViewIs('flashcards.show')
                 ->assertViewHas('deck', $this->publicDeck)
                 ->assertViewHas('flashcard', $this->publicFlashcard);
    }

    public function test_guest_cannot_view_flashcard()
    {
        $response = $this->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_view_private_flashcard_from_other_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->otherUserDeck, $this->otherFlashcard]));

        $response->assertStatus(403);
    }

    public function test_other_user_can_view_public_flashcard()
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('decks.flashcards.show', [$this->publicDeck, $this->publicFlashcard]));

        $response->assertStatus(200)
                 ->assertViewIs('flashcards.show')
                 ->assertViewHas('deck', $this->publicDeck)
                 ->assertViewHas('flashcard', $this->publicFlashcard);
    }

    public function test_flashcard_must_belong_to_deck()
    {
        // Try to access a flashcard with wrong deck
        $wrongDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$wrongDeck, $this->flashcard]));

        $response->assertStatus(404);
    }

    public function test_flashcard_from_different_deck_returns_404()
    {
        // Create another deck and flashcard for same user
        $anotherDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false
        ]);
        
        $anotherFlashcard = Flashcard::factory()->create([
            'deck_id' => $anotherDeck->id
        ]);

        // Try to view flashcard with wrong deck
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $anotherFlashcard]));

        $response->assertStatus(404);
    }

    public function test_view_uses_deck_authorization()
    {
        // This tests that the deck authorization is called
        // Private deck should be accessible by owner
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));

        $response->assertStatus(200);

        // Private deck should not be accessible by other user
        $response = $this->actingAs($this->otherUser)
            ->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));

        $response->assertStatus(403);
    }

    public function test_controller_loads_correct_flashcard_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));

        $response->assertStatus(200);

        $viewDeck = $response->viewData('deck');
        $viewFlashcard = $response->viewData('flashcard');

        $this->assertEquals($this->deck->id, $viewDeck->id);
        $this->assertEquals($this->deck->name, $viewDeck->name);
        
        $this->assertEquals($this->flashcard->id, $viewFlashcard->id);
        $this->assertEquals('Test Question', $viewFlashcard->question);
        $this->assertEquals('Test Answer', $viewFlashcard->answer);
    }

    public function test_route_model_binding_works_with_slug()
    {
        // Test that deck slug routing works
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck->slug, $this->flashcard]));

        $response->assertStatus(200)
                 ->assertViewHas('deck', $this->deck)
                 ->assertViewHas('flashcard', $this->flashcard);
    }

    public function test_nonexistent_deck_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->get('/decks/nonexistent-slug/flashcards/' . $this->flashcard->id);

        $response->assertStatus(404);
    }

    public function test_nonexistent_flashcard_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, 99999]));

        $response->assertStatus(404);
    }

    public function test_authorization_is_checked_before_deck_validation()
    {
        // Other user trying to access private deck should get 403, not 404
        $response = $this->actingAs($this->otherUser)
            ->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));

        $response->assertStatus(403);
    }

    public function test_public_deck_accessible_by_any_authenticated_user()
    {
        // Create a third user
        $thirdUser = User::factory()->create();

        $response = $this->actingAs($thirdUser)
            ->get(route('decks.flashcards.show', [$this->publicDeck, $this->publicFlashcard]));

        $response->assertStatus(200)
                 ->assertViewHas('deck', $this->publicDeck)
                 ->assertViewHas('flashcard', $this->publicFlashcard);
    }

    public function test_controller_handles_edge_cases()
    {
        // Test with flashcard that has special characters
        $specialFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'سوال فارسی؟',
            'answer' => 'جواب فارسی با علائم خاص @#$%'
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $specialFlashcard]));

        $response->assertStatus(200);

        $viewFlashcard = $response->viewData('flashcard');
        $this->assertEquals('سوال فارسی؟', $viewFlashcard->question);
        $this->assertEquals('جواب فارسی با علائم خاص @#$%', $viewFlashcard->answer);
    }

    public function test_controller_with_empty_deck_name()
    {
        $emptyNameDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => '',
            'is_public' => false
        ]);

        $emptyFlashcard = Flashcard::factory()->create([
            'deck_id' => $emptyNameDeck->id
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$emptyNameDeck, $emptyFlashcard]));

        $response->assertStatus(200);
    }

    public function test_multiple_flashcards_in_same_deck()
    {
        // Create multiple flashcards in the same deck
        $flashcard1 = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Question 1'
        ]);
        
        $flashcard2 = Flashcard::factory()->create([
            'deck_id' => $this->deck->id,
            'question' => 'Question 2'
        ]);

        // Test accessing each flashcard
        $response1 = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $flashcard1]));

        $response1->assertStatus(200)
                  ->assertViewHas('flashcard', $flashcard1);

        $response2 = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $flashcard2]));

        $response2->assertStatus(200)
                  ->assertViewHas('flashcard', $flashcard2);
    }

    public function test_complex_authorization_scenario()
    {
        // Owner can access private flashcard
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));
        $response->assertStatus(200);

        // Other user cannot access private flashcard
        $response = $this->actingAs($this->otherUser)
            ->get(route('decks.flashcards.show', [$this->deck, $this->flashcard]));
        $response->assertStatus(403);

        // But other user can access public flashcard
        $response = $this->actingAs($this->otherUser)
            ->get(route('decks.flashcards.show', [$this->publicDeck, $this->publicFlashcard]));
        $response->assertStatus(200);

        // Owner can also access their own public flashcard
        $response = $this->actingAs($this->user)
            ->get(route('decks.flashcards.show', [$this->publicDeck, $this->publicFlashcard]));
        $response->assertStatus(200);
    }
}