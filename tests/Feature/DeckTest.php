<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeckTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_authenticated_user_can_view_their_decks()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('decks.index'));

        $response->assertStatus(200);
        $response->assertSee($deck->name);
    }

    public function test_user_can_create_a_deck()
    {
        $user = User::factory()->create();

        $deckData = [
            'name' => 'Test Deck',
            'description' => 'A test deck for learning',
            'is_public' => false,
        ];

        $response = $this->actingAs($user)->post(route('decks.store'), $deckData);

        $response->assertRedirect();
        $this->assertDatabaseHas('decks', [
            'name' => 'Test Deck',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_view_their_own_deck()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('decks.show', $deck));

        $response->assertStatus(200);
        $response->assertSee($deck->name);
    }

    public function test_user_cannot_view_other_users_private_deck()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $privateDeck = Deck::factory()->create([
            'user_id' => $user1->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($user2)->get(route('decks.show', $privateDeck));

        $response->assertStatus(403);
    }

    public function test_user_can_view_public_deck_from_other_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $publicDeck = Deck::factory()->create([
            'user_id' => $user1->id,
            'is_public' => true,
        ]);

        $response = $this->actingAs($user2)->get(route('decks.show', $publicDeck));

        $response->assertStatus(200);
        $response->assertSee($publicDeck->name);
    }

    public function test_user_can_study_deck_with_flashcards()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create(['user_id' => $user->id]);
        Flashcard::factory()->create(['deck_id' => $deck->id]);

        $response = $this->actingAs($user)->get(route('decks.study', $deck));

        $response->assertStatus(200);
        $response->assertSee('Study Mode');
    }

    public function test_deck_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('decks.store'), [
            'description' => 'A deck without a name',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_can_delete_their_own_deck()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('decks.destroy', $deck));

        $response->assertRedirect();
        $this->assertDatabaseMissing('decks', ['id' => $deck->id]);
    }

    public function test_guest_cannot_access_deck_routes()
    {
        $deck = Deck::factory()->create();

        $this->get(route('decks.index'))->assertRedirect(route('login'));
        $this->get(route('decks.create'))->assertRedirect(route('login'));
        $this->get(route('decks.show', $deck))->assertRedirect(route('login'));
    }

    public function test_route_model_binding_works_with_slug()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Slug Deck',
        ]);

        $response = $this->actingAs($user)->get(route('decks.show', $deck->slug));

        $response->assertStatus(200);
        $response->assertSee($deck->name);
    }

    public function test_study_route_works_with_slug()
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create([
            'user_id' => $user->id,
            'name' => 'Study Slug Deck',
        ]);
        Flashcard::factory()->create(['deck_id' => $deck->id]);

        $response = $this->actingAs($user)->get(route('decks.study', $deck->slug));

        $response->assertStatus(200);
        $response->assertSee('Study Mode');
    }

    public function test_route_returns_404_for_invalid_slug()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/decks/invalid-slug-123');

        $response->assertStatus(404);
    }
}
