<?php

namespace Tests\Unit\Policies;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use App\Policies\FlashcardPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashcardPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected FlashcardPolicy $policy;
    protected User $user;
    protected User $otherUser;
    protected Deck $privateDeck;
    protected Deck $publicDeck;
    protected Deck $otherUserPrivateDeck;
    protected Deck $otherUserPublicDeck;
    protected Flashcard $privateFlashcard;
    protected Flashcard $publicFlashcard;
    protected Flashcard $otherUserPrivateFlashcard;
    protected Flashcard $otherUserPublicFlashcard;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new FlashcardPolicy();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        // Create decks
        $this->privateDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false
        ]);
        
        $this->publicDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => true
        ]);
        
        $this->otherUserPrivateDeck = Deck::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_public' => false
        ]);
        
        $this->otherUserPublicDeck = Deck::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_public' => true
        ]);
        
        // Create flashcards
        $this->privateFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->privateDeck->id
        ]);
        
        $this->publicFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->publicDeck->id
        ]);
        
        $this->otherUserPrivateFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->otherUserPrivateDeck->id
        ]);
        
        $this->otherUserPublicFlashcard = Flashcard::factory()->create([
            'deck_id' => $this->otherUserPublicDeck->id
        ]);
    }

    // ViewAny Tests
    public function test_view_any_allows_authenticated_users()
    {
        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->viewAny($this->otherUser));
    }

    // View Tests
    public function test_user_can_view_own_private_flashcard()
    {
        $this->assertTrue(
            $this->policy->view($this->user, $this->privateFlashcard)
        );
    }

    public function test_user_can_view_own_public_flashcard()
    {
        $this->assertTrue(
            $this->policy->view($this->user, $this->publicFlashcard)
        );
    }

    public function test_user_cannot_view_other_users_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->view($this->user, $this->otherUserPrivateFlashcard)
        );
    }

    public function test_user_can_view_other_users_public_flashcard()
    {
        $this->assertTrue(
            $this->policy->view($this->user, $this->otherUserPublicFlashcard)
        );
    }

    public function test_other_user_cannot_view_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->view($this->otherUser, $this->privateFlashcard)
        );
    }

    public function test_other_user_can_view_public_flashcard()
    {
        $this->assertTrue(
            $this->policy->view($this->otherUser, $this->publicFlashcard)
        );
    }

    // Create Tests
    public function test_authenticated_user_can_create_flashcards()
    {
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->create($this->otherUser));
    }

    // Update Tests
    public function test_user_can_update_own_private_flashcard()
    {
        $this->assertTrue(
            $this->policy->update($this->user, $this->privateFlashcard)
        );
    }

    public function test_user_can_update_own_public_flashcard()
    {
        $this->assertTrue(
            $this->policy->update($this->user, $this->publicFlashcard)
        );
    }

    public function test_user_cannot_update_other_users_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->update($this->user, $this->otherUserPrivateFlashcard)
        );
    }

    public function test_user_cannot_update_other_users_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->update($this->user, $this->otherUserPublicFlashcard)
        );
    }

    public function test_other_user_cannot_update_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->update($this->otherUser, $this->privateFlashcard)
        );
    }

    public function test_other_user_cannot_update_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->update($this->otherUser, $this->publicFlashcard)
        );
    }

    // Delete Tests
    public function test_user_can_delete_own_private_flashcard()
    {
        $this->assertTrue(
            $this->policy->delete($this->user, $this->privateFlashcard)
        );
    }

    public function test_user_can_delete_own_public_flashcard()
    {
        $this->assertTrue(
            $this->policy->delete($this->user, $this->publicFlashcard)
        );
    }

    public function test_user_cannot_delete_other_users_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->delete($this->user, $this->otherUserPrivateFlashcard)
        );
    }

    public function test_user_cannot_delete_other_users_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->delete($this->user, $this->otherUserPublicFlashcard)
        );
    }

    public function test_other_user_cannot_delete_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->delete($this->otherUser, $this->privateFlashcard)
        );
    }

    public function test_other_user_cannot_delete_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->delete($this->otherUser, $this->publicFlashcard)
        );
    }

    // Restore Tests
    public function test_user_can_restore_own_private_flashcard()
    {
        $this->assertTrue(
            $this->policy->restore($this->user, $this->privateFlashcard)
        );
    }

    public function test_user_can_restore_own_public_flashcard()
    {
        $this->assertTrue(
            $this->policy->restore($this->user, $this->publicFlashcard)
        );
    }

    public function test_user_cannot_restore_other_users_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->restore($this->user, $this->otherUserPrivateFlashcard)
        );
    }

    public function test_user_cannot_restore_other_users_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->restore($this->user, $this->otherUserPublicFlashcard)
        );
    }

    public function test_other_user_cannot_restore_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->restore($this->otherUser, $this->privateFlashcard)
        );
    }

    public function test_other_user_cannot_restore_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->restore($this->otherUser, $this->publicFlashcard)
        );
    }

    // ForceDelete Tests
    public function test_user_can_force_delete_own_private_flashcard()
    {
        $this->assertTrue(
            $this->policy->forceDelete($this->user, $this->privateFlashcard)
        );
    }

    public function test_user_can_force_delete_own_public_flashcard()
    {
        $this->assertTrue(
            $this->policy->forceDelete($this->user, $this->publicFlashcard)
        );
    }

    public function test_user_cannot_force_delete_other_users_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->forceDelete($this->user, $this->otherUserPrivateFlashcard)
        );
    }

    public function test_user_cannot_force_delete_other_users_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->forceDelete($this->user, $this->otherUserPublicFlashcard)
        );
    }

    public function test_other_user_cannot_force_delete_private_flashcard()
    {
        $this->assertFalse(
            $this->policy->forceDelete($this->otherUser, $this->privateFlashcard)
        );
    }

    public function test_other_user_cannot_force_delete_public_flashcard()
    {
        $this->assertFalse(
            $this->policy->forceDelete($this->otherUser, $this->publicFlashcard)
        );
    }

    // Edge Cases
    public function test_deck_relationship_is_loaded_for_view_policy()
    {
        // Ensure that the deck relationship is accessible
        $flashcard = Flashcard::factory()->create([
            'deck_id' => $this->privateDeck->id
        ]);
        
        // Load the deck relationship
        $flashcard->load('deck');
        
        $this->assertTrue(
            $this->policy->view($this->user, $flashcard)
        );
    }

    public function test_deck_relationship_is_loaded_for_update_policy()
    {
        // Ensure that the deck relationship is accessible
        $flashcard = Flashcard::factory()->create([
            'deck_id' => $this->privateDeck->id
        ]);
        
        // Load the deck relationship
        $flashcard->load('deck');
        
        $this->assertTrue(
            $this->policy->update($this->user, $flashcard)
        );
    }

    public function test_policy_works_with_different_user_instances()
    {
        // Create new user instance with same ID
        $sameUser = User::find($this->user->id);
        
        $this->assertTrue(
            $this->policy->view($sameUser, $this->privateFlashcard)
        );
        
        $this->assertTrue(
            $this->policy->update($sameUser, $this->privateFlashcard)
        );
    }

    public function test_policy_consistency_across_all_ownership_methods()
    {
        // Test that all ownership-based methods return the same result
        $methods = ['update', 'delete', 'restore', 'forceDelete'];
        
        foreach ($methods as $method) {
            // Owner should have access
            $this->assertTrue(
                $this->policy->$method($this->user, $this->privateFlashcard),
                "Method {$method} should allow owner access"
            );
            
            // Non-owner should not have access
            $this->assertFalse(
                $this->policy->$method($this->otherUser, $this->privateFlashcard),
                "Method {$method} should deny non-owner access"
            );
        }
    }

    public function test_view_policy_respects_deck_visibility()
    {
        // Private deck - only owner can view
        $this->assertTrue(
            $this->policy->view($this->user, $this->privateFlashcard)
        );
        $this->assertFalse(
            $this->policy->view($this->otherUser, $this->privateFlashcard)
        );
        
        // Public deck - anyone can view
        $this->assertTrue(
            $this->policy->view($this->user, $this->otherUserPublicFlashcard)
        );
        $this->assertTrue(
            $this->policy->view($this->otherUser, $this->publicFlashcard)
        );
    }
}