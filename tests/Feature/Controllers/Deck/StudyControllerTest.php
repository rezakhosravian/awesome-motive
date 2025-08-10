<?php

namespace Tests\Feature\Controllers\Deck;

use App\Contracts\Service\DeckServiceInterface;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Deck $deck;
    protected Deck $privateDeck;
    protected Deck $publicDeck;
    protected Deck $emptyDeck;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        // Deck owned by user with flashcards
        $this->deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Study Deck',
            'is_public' => false
        ]);
        
        // Create flashcards for study
        Flashcard::factory()->count(3)->create([
            'deck_id' => $this->deck->id
        ]);
        
        // Private deck owned by other user
        $this->privateDeck = Deck::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_public' => false
        ]);
        
        Flashcard::factory()->count(2)->create([
            'deck_id' => $this->privateDeck->id
        ]);
        
        // Public deck owned by other user
        $this->publicDeck = Deck::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_public' => true
        ]);
        
        Flashcard::factory()->count(4)->create([
            'deck_id' => $this->publicDeck->id
        ]);
        
        // Empty deck (no flashcards) owned by user
        $this->emptyDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Empty Study Deck',
            'is_public' => false
        ]);
    }

    public function test_authenticated_user_can_study_own_deck()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->deck));

        $response->assertStatus(200)
                 ->assertViewIs('decks.study')
                 ->assertViewHas('deck');
                 
        $viewDeck = $response->viewData('deck');
        $this->assertEquals($this->deck->id, $viewDeck->id);
        $this->assertGreaterThan(0, $viewDeck->flashcards->count());
    }

    public function test_authenticated_user_can_study_public_deck()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->publicDeck));

        $response->assertStatus(200)
                 ->assertViewIs('decks.study')
                 ->assertViewHas('deck');
                 
        $viewDeck = $response->viewData('deck');
        $this->assertEquals($this->publicDeck->id, $viewDeck->id);
        $this->assertGreaterThan(0, $viewDeck->flashcards->count());
    }

    public function test_guest_cannot_study_deck()
    {
        $response = $this->get(route('decks.study', $this->deck));

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_study_private_deck_of_other_user()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->privateDeck));

        $response->assertStatus(403);
    }

    public function test_owner_can_study_private_deck()
    {
        $response = $this->actingAs($this->otherUser)
            ->get(route('decks.study', $this->privateDeck));

        $response->assertStatus(200)
                 ->assertViewIs('decks.study')
                 ->assertViewHas('deck');
    }

    public function test_study_empty_deck_redirects_with_error()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->emptyDeck));

        $response->assertRedirect(route('decks.show', $this->emptyDeck))
                 ->assertSessionHas('error');
        
        $errorMessage = session('error');
        $this->assertStringContainsString('flashcard', strtolower($errorMessage));
    }

    public function test_study_nonexistent_deck_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.study', ['deck' => 'nonexistent-slug']));

        $response->assertStatus(404);
    }

    public function test_deck_service_exception_redirects_with_error_message()
    {
        // Mock the DeckService to throw an exception
        $deckServiceMock = $this->createMock(DeckServiceInterface::class);
        $deckServiceMock->method('getDeckForStudy')
            ->willThrowException(new \InvalidArgumentException('Custom error message for testing'));
        
        $this->app->instance(DeckServiceInterface::class, $deckServiceMock);

        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->deck));

        $response->assertRedirect(route('decks.show', $this->deck))
                 ->assertSessionHas('error', 'Custom error message for testing');
    }

    public function test_study_controller_loads_deck_with_flashcards()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->deck));

        $response->assertStatus(200);
        
        $viewDeck = $response->viewData('deck');
        
        // Verify deck has flashcards loaded
        $this->assertTrue($viewDeck->relationLoaded('flashcards'));
        $this->assertGreaterThan(0, $viewDeck->flashcards->count());
        
        // Verify flashcards have required attributes
        $firstFlashcard = $viewDeck->flashcards->first();
        $this->assertNotNull($firstFlashcard->question);
        $this->assertNotNull($firstFlashcard->answer);
    }

    public function test_study_route_uses_deck_slug()
    {
        $response = $this->actingAs($this->user)
            ->get("/decks/{$this->deck->slug}/study");

        $response->assertStatus(200)
                 ->assertViewIs('decks.study');
    }

    public function test_study_controller_authorizes_deck_access()
    {
        // Create a deck and then change ownership to simulate unauthorized access
        $tempDeck = Deck::factory()->create(['user_id' => $this->user->id]);
        
        // Change ownership after creation
        $tempDeck->update(['user_id' => $this->otherUser->id, 'is_public' => false]);

        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $tempDeck));

        $response->assertStatus(403);
    }

    public function test_study_controller_handles_deck_service_correctly()
    {
        // Test that the controller properly calls the deck service
        $deckServiceMock = $this->createMock(DeckServiceInterface::class);
        
        // Expect the service to be called with correct parameters
        $deckServiceMock->expects($this->once())
            ->method('getDeckForStudy')
            ->with($this->deck->id, $this->user)
            ->willReturn($this->deck->load('flashcards'));
        
        $this->app->instance(DeckServiceInterface::class, $deckServiceMock);

        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->deck));

        $response->assertStatus(200);
    }

    public function test_study_view_receives_correct_deck_data()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->deck));

        $response->assertStatus(200)
                 ->assertViewIs('decks.study');
        
        $viewDeck = $response->viewData('deck');
        
        // Assert deck properties
        $this->assertEquals($this->deck->id, $viewDeck->id);
        $this->assertEquals($this->deck->name, $viewDeck->name);
        $this->assertEquals($this->deck->user_id, $viewDeck->user_id);
        
        // Assert flashcards are present
        $this->assertGreaterThan(0, $viewDeck->flashcards->count());
        $this->assertEquals(3, $viewDeck->flashcards->count()); // We created 3 flashcards
    }

    public function test_different_exception_types_are_handled()
    {
        $deckServiceMock = $this->createMock(DeckServiceInterface::class);
        $deckServiceMock->method('getDeckForStudy')
            ->willThrowException(new \InvalidArgumentException('Deck has no flashcards'));
        
        $this->app->instance(DeckServiceInterface::class, $deckServiceMock);

        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->deck));

        $response->assertRedirect(route('decks.show', $this->deck))
                 ->assertSessionHas('error', 'Deck has no flashcards');
    }

    public function test_study_controller_preserves_deck_slug_in_redirect()
    {
        $deckServiceMock = $this->createMock(DeckServiceInterface::class);
        $deckServiceMock->method('getDeckForStudy')
            ->willThrowException(new \InvalidArgumentException('Error message'));
        
        $this->app->instance(DeckServiceInterface::class, $deckServiceMock);

        $response = $this->actingAs($this->user)
            ->get(route('decks.study', $this->deck));

        // The redirect should use the deck's slug, not ID
        $expectedUrl = route('decks.show', $this->deck);
        $response->assertRedirect($expectedUrl);
    }
}