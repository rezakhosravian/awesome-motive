<?php

namespace Tests\Feature;

use App\Livewire\CreateDeck;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateDeckComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->assertStatus(200);
    }

    public function test_component_requires_authentication(): void
    {
        Livewire::test(CreateDeck::class)
            ->assertForbidden();
    }

    public function test_can_create_deck_with_valid_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $deckData = [
            'name' => 'Test Deck',
            'description' => 'Test Description',
            'is_public' => false,
        ];

        Livewire::test(CreateDeck::class)
            ->set('name', $deckData['name'])
            ->set('description', $deckData['description'])
            ->set('is_public', $deckData['is_public'])
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('decks', [
            'name' => $deckData['name'],
            'description' => $deckData['description'],
            'is_public' => $deckData['is_public'],
            'user_id' => $user->id,
        ]);
    }

    public function test_can_create_public_deck(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Public Test Deck')
            ->set('description', 'Public Test Description')
            ->set('is_public', true)
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('decks', [
            'name' => 'Public Test Deck',
            'is_public' => true,
            'user_id' => $user->id,
        ]);
    }

    public function test_validates_required_name(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', '')
            ->set('description', 'Test Description')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_validates_name_max_length(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', str_repeat('a', 256))
            ->call('save')
            ->assertHasErrors(['name' => 'max']);
    }

    public function test_validates_description_max_length(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Test Deck')
            ->set('description', str_repeat('a', 1001))
            ->call('save')
            ->assertHasErrors(['description' => 'max']);
    }

    public function test_description_is_optional(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Test Deck')
            ->set('description', '')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('decks', [
            'name' => 'Test Deck',
            'description' => '',
            'user_id' => $user->id,
        ]);
    }

    public function test_reset_form_clears_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Test Name')
            ->set('description', 'Test Description')
            ->set('is_public', true)
            ->call('resetForm')
            ->assertSet('name', '')
            ->assertSet('description', '')
            ->assertSet('is_public', false);
    }

    public function test_success_message_shown_after_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Success Test Deck')
            ->set('description', 'Testing success message')
            ->set('skipRedirect', true)
            ->call('save')
            ->assertSet('showSuccess', true)
            ->assertSee('created successfully');
    }

    public function test_form_resets_after_successful_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Reset Test Deck')
            ->set('description', 'Testing form reset')
            ->set('skipRedirect', true)
            ->call('save')
            ->assertSet('name', '')
            ->assertSet('description', '')
            ->assertSet('is_public', false);
    }

    public function test_deck_created_event_is_dispatched(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Event Test Deck')
            ->set('description', 'Testing event dispatch')
            ->set('skipRedirect', true)
            ->call('save')
            ->assertDispatched('deck-created');
    }

    public function test_redirects_to_new_deck_after_creation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $component = Livewire::test(CreateDeck::class)
            ->set('name', 'Redirect Test Deck')
            ->set('description', 'Testing redirect')
            ->call('save');

        $deck = Deck::where('name', 'Redirect Test Deck')->first();
        $component->assertRedirect(route('decks.show', $deck));
    }

    public function test_only_owner_can_create_deck(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', 'Owner Test Deck')
            ->set('description', 'Testing ownership')
            ->set('skipRedirect', true)
            ->call('save');

        $this->assertDatabaseHas('decks', [
            'name' => 'Owner Test Deck',
            'user_id' => $user->id,
        ]);
    }

    public function test_real_time_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateDeck::class)
            ->set('name', '')
            ->assertHasErrors(['name' => 'required'])
            ->set('name', 'Valid Name')
            ->assertHasNoErrors(['name']);
    }

    public function test_boolean_casting_works_for_is_public(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with string "1"
        Livewire::test(CreateDeck::class)
            ->set('name', 'Boolean Test Deck')
            ->set('is_public', '1')
            ->set('skipRedirect', true)
            ->call('save');

        $this->assertDatabaseHas('decks', [
            'name' => 'Boolean Test Deck',
            'is_public' => true,
        ]);
    }
} 