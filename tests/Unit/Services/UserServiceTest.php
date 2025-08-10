<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\UserService;
use App\Models\User;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        
        $this->service = new UserService();
    }



    public function test_register_creates_user_with_hashed_password(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $user = $this->service->register($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->service->authenticate([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertTrue($result);
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_authenticate_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->service->authenticate([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertFalse($result);
        $this->assertNull(Auth::id());
    }

    public function test_update_profile_excludes_sensitive_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com'
        ]);

        $data = [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'password' => 'should-be-ignored',
            'email_verified_at' => 'should-be-ignored'
        ];

        $updatedUser = $this->service->updateProfile($user, $data);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);
        $this->assertNotEquals('should-be-ignored', $updatedUser->password);
    }

    public function test_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        $result = $this->service->changePassword($user, 'oldpassword', 'newpassword');

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    public function test_change_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The current password is incorrect.');

        $this->service->changePassword($user, 'wrongpassword', 'newpassword');
    }

    public function test_delete_account_with_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $result = $this->service->deleteAccount($user, 'password123');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_account_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The password is incorrect.');

        $this->service->deleteAccount($user, 'wrongpassword');
    }

    public function test_get_user_stats(): void
    {
        $user = User::factory()->create();
        
        // Create decks with different visibility
        $publicDecks = Deck::factory()->count(3)->for($user)->create(['is_public' => true]);
        $privateDecks = Deck::factory()->count(2)->for($user)->create(['is_public' => false]);
        
        // Create flashcards for some decks
        Flashcard::factory()->count(5)->for($publicDecks->first())->create();
        Flashcard::factory()->count(3)->for($privateDecks->first())->create();

        $stats = $this->service->getUserStats($user);

        $this->assertArrayHasKey('total_decks', $stats);
        $this->assertArrayHasKey('public_decks', $stats);
        $this->assertArrayHasKey('private_decks', $stats);
        $this->assertArrayHasKey('total_flashcards', $stats);
        $this->assertArrayHasKey('created_at', $stats);

        $this->assertEquals(5, $stats['total_decks']);
        $this->assertEquals(3, $stats['public_decks']);
        $this->assertEquals(2, $stats['private_decks']);
        $this->assertEquals(8, $stats['total_flashcards']);
        $this->assertEquals($user->created_at, $stats['created_at']);
    }

    public function test_get_user_stats_with_no_data(): void
    {
        $user = User::factory()->create();

        $stats = $this->service->getUserStats($user);

        $this->assertEquals(0, $stats['total_decks']);
        $this->assertEquals(0, $stats['public_decks']);
        $this->assertEquals(0, $stats['private_decks']);
        $this->assertEquals(0, $stats['total_flashcards']);
    }
} 