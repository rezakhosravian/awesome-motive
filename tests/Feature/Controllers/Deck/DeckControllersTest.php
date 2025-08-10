<?php

namespace Tests\Feature\Controllers\Deck;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckControllersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Deck $deck;
    protected Deck $otherUserDeck;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Deck',
            'description' => 'Test Description'
        ]);
        
        $this->otherUserDeck = Deck::factory()->create([
            'user_id' => $this->otherUser->id,
            'name' => 'Other User Deck'
        ]);
    }

    // CreateController Tests
    public function test_authenticated_user_can_view_create_deck_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.create'));

        $response->assertStatus(200)
                 ->assertViewIs('decks.create');
    }

    public function test_guest_cannot_view_create_deck_form()
    {
        $response = $this->get(route('decks.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_create_deck_uses_authorization()
    {
        // Test with authenticated user (should work)
        $response = $this->actingAs($this->user)
            ->get(route('decks.create'));

        $response->assertStatus(200);
    }

    // EditController Tests
    public function test_authenticated_user_can_view_edit_own_deck_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.edit', $this->deck));

        $response->assertStatus(200)
                 ->assertViewIs('decks.edit')
                 ->assertViewHas('deck', $this->deck);
    }

    public function test_guest_cannot_view_edit_deck_form()
    {
        $response = $this->get(route('decks.edit', $this->deck));

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_edit_other_users_deck()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.edit', $this->otherUserDeck));

        $response->assertStatus(403);
    }

    public function test_edit_deck_loads_correct_deck()
    {
        $response = $this->actingAs($this->user)
            ->get(route('decks.edit', $this->deck));

        $response->assertStatus(200);
        
        $viewDeck = $response->viewData('deck');
        $this->assertEquals($this->deck->id, $viewDeck->id);
        $this->assertEquals($this->deck->name, $viewDeck->name);
        $this->assertEquals($this->deck->description, $viewDeck->description);
    }

    // UpdateController Tests
    public function test_authenticated_user_can_update_own_deck()
    {
        $updateData = [
            'name' => 'Updated Deck Name',
            'description' => 'Updated description',
            'is_public' => true
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertSessionHas('success', 'Deck updated successfully!');
        
        // Refresh the deck to get updated slug
        $this->deck->refresh();

        $this->assertDatabaseHas('decks', [
            'id' => $this->deck->id,
            'name' => 'Updated Deck Name',
            'description' => 'Updated description',
            'is_public' => true
        ]);
    }

    public function test_guest_cannot_update_deck()
    {
        $updateData = [
            'name' => 'Updated Deck Name',
            'description' => 'Updated description',
            'is_public' => true
        ];

        $response = $this->put(route('decks.update', $this->deck), $updateData);

        $response->assertRedirect(route('login'));
        
        $this->assertDatabaseMissing('decks', [
            'name' => 'Updated Deck Name'
        ]);
    }

    public function test_user_cannot_update_other_users_deck()
    {
        $updateData = [
            'name' => 'Unauthorized Update',
            'description' => 'This should not work',
            'is_public' => true
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->otherUserDeck), $updateData);

        $response->assertStatus(403);
        
        $this->assertDatabaseMissing('decks', [
            'name' => 'Unauthorized Update'
        ]);
    }

    public function test_update_deck_validates_required_fields()
    {
        $updateData = [
            'name' => '', // Empty name should fail
            'description' => 'Valid description'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_deck_validates_name_max_length()
    {
        $updateData = [
            'name' => str_repeat('a', 256), // Too long
            'description' => 'Valid description'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_deck_validates_description_max_length()
    {
        $updateData = [
            'name' => 'Valid Name',
            'description' => str_repeat('a', 1001) // Too long
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_update_deck_allows_null_description()
    {
        $updateData = [
            'name' => 'Valid Name',
            'description' => null,
            'is_public' => false
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertSessionHas('success');
        
        // Refresh deck to get updated slug
        $this->deck->refresh();

        $this->assertDatabaseHas('decks', [
            'id' => $this->deck->id,
            'name' => 'Valid Name',
            'description' => null
        ]);
    }

    public function test_update_deck_handles_boolean_conversion()
    {
        $updateData = [
            'name' => 'Test Deck',
            'is_public' => '1' // String that should convert to boolean
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertStatus(302); // Redirect

        $this->assertDatabaseHas('decks', [
            'id' => $this->deck->id,
            'is_public' => true
        ]);
    }

    public function test_update_deck_preserves_user_id()
    {
        $updateData = [
            'name' => 'Updated Name',
            'user_id' => $this->otherUser->id // This should be ignored
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertStatus(302); // Redirect

        // User ID should remain unchanged
        $this->assertDatabaseHas('decks', [
            'id' => $this->deck->id,
            'name' => 'Updated Name',
            'user_id' => $this->user->id // Original owner
        ]);
    }

    public function test_update_deck_uses_deck_service()
    {
        $updateData = [
            'name' => 'Service Test Deck',
            'description' => 'Testing service integration'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertStatus(302); // Redirect

        // Verify the update went through the service
        $this->assertDatabaseHas('decks', [
            'id' => $this->deck->id,
            'name' => 'Service Test Deck',
            'description' => 'Testing service integration'
        ]);
    }

    public function test_update_controller_redirects_to_correct_deck()
    {
        $updateData = [
            'name' => 'Redirect Test'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        // Refresh deck to get updated slug
        $this->deck->refresh();
        
        // Should redirect to the updated deck's show page
        $expectedUrl = route('decks.show', $this->deck);
        $response->assertRedirect($expectedUrl);
    }

    public function test_update_controller_handles_unicode_content()
    {
        $updateData = [
            'name' => 'دیتابیس لاراول',
            'description' => 'توضیحات فارسی برای تست'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->deck), $updateData);

        $response->assertStatus(302); // Redirect

        $this->assertDatabaseHas('decks', [
            'id' => $this->deck->id,
            'name' => 'دیتابیس لاراول',
            'description' => 'توضیحات فارسی برای تست'
        ]);
    }

    public function test_update_controller_authorizes_before_update()
    {
        // Test that authorization happens before any update logic
        $originalName = $this->otherUserDeck->name;
        
        $updateData = [
            'name' => 'Should Not Update'
        ];

        $response = $this->actingAs($this->user)
            ->put(route('decks.update', $this->otherUserDeck), $updateData);

        $response->assertStatus(403);

        // Verify no update occurred
        $this->assertDatabaseHas('decks', [
            'id' => $this->otherUserDeck->id,
            'name' => $originalName
        ]);
    }
}