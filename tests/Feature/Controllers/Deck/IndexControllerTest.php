<?php

namespace Tests\Feature\Controllers\Deck;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Deck;

class IndexControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_their_decks(): void
    {
        $user = User::factory()->create();
        
        // Create decks for the user
        Deck::factory()->count(3)->for($user)->create();
        
        // Create decks for other users (should not appear)
        Deck::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('decks.index'));

        $response->assertOk();
        $response->assertViewIs('decks.index');
        $response->assertViewHas('decks');
        
        $decks = $response->viewData('decks');
        $this->assertCount(3, $decks);
    }

    public function test_guest_cannot_access_deck_index(): void
    {
        $response = $this->get(route('decks.index'));
        
        $response->assertRedirect(route('login'));
    }

    public function test_pagination_works_correctly(): void
    {
        $user = User::factory()->create();
        
        // Create 20 decks for pagination testing
        Deck::factory()->count(20)->for($user)->create();

        $response = $this->actingAs($user)->get(route('decks.index'));

        $response->assertOk();
        $response->assertViewHas('decks');
        
        $decks = $response->viewData('decks');
        // Assuming pagination shows 15 per page by default
        $this->assertCount(15, $decks);
    }

    public function test_decks_are_ordered_by_latest(): void
    {
        $user = User::factory()->create();
        
        // Create decks with specific timestamps
        $oldDeck = Deck::factory()->for($user)->create(['created_at' => now()->subDays(2)]);
        $newDeck = Deck::factory()->for($user)->create(['created_at' => now()]);
        $middleDeck = Deck::factory()->for($user)->create(['created_at' => now()->subDay()]);

        $response = $this->actingAs($user)->get(route('decks.index'));

        $response->assertOk();
        $decks = $response->viewData('decks');
        
        // Verify ordering (newest first)
        $this->assertEquals($newDeck->id, $decks->first()->id);
        $this->assertEquals($oldDeck->id, $decks->last()->id);
    }
} 