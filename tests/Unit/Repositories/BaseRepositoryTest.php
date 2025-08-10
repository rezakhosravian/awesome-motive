<?php

namespace Tests\Unit\Repositories;

use App\Models\Deck;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected BaseRepository $repository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a concrete implementation of BaseRepository for testing
        $this->repository = new class(new Deck()) extends BaseRepository {
            // Concrete implementation for testing
        };
        
        $this->user = User::factory()->create();
    }

    public function test_constructor_sets_model()
    {
        $deck = new Deck();
        $repository = new class($deck) extends BaseRepository {};
        
        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        
        $this->assertSame($deck, $property->getValue($repository));
    }

    public function test_all_returns_all_models()
    {
        // Create test data
        Deck::factory()->count(3)->create(['user_id' => $this->user->id]);

        $result = $this->repository->all();

        $this->assertCount(3, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_all_returns_empty_collection_when_no_data()
    {
        $result = $this->repository->all();

        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_find_returns_model_when_found()
    {
        $deck = Deck::factory()->create(['user_id' => $this->user->id]);

        $result = $this->repository->find($deck->id);

        $this->assertNotNull($result);
        $this->assertEquals($deck->id, $result->id);
        $this->assertInstanceOf(Deck::class, $result);
    }

    public function test_find_returns_null_when_not_found()
    {
        $result = $this->repository->find(99999);

        $this->assertNull($result);
    }

    public function test_find_or_fail_returns_model_when_found()
    {
        $deck = Deck::factory()->create(['user_id' => $this->user->id]);

        $result = $this->repository->findOrFail($deck->id);

        $this->assertNotNull($result);
        $this->assertEquals($deck->id, $result->id);
        $this->assertInstanceOf(Deck::class, $result);
    }

    public function test_find_or_fail_throws_exception_when_not_found()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->findOrFail(99999);
    }

    public function test_create_creates_new_model()
    {
        $data = [
            'name' => 'Test Deck',
            'description' => 'Test Description',
            'user_id' => $this->user->id,
            'is_public' => false
        ];

        $result = $this->repository->create($data);

        $this->assertInstanceOf(Deck::class, $result);
        $this->assertEquals('Test Deck', $result->name);
        $this->assertEquals('Test Description', $result->description);
        $this->assertDatabaseHas('decks', $data);
    }

    public function test_update_updates_existing_model()
    {
        $deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ];

        $result = $this->repository->update($deck, $updateData);

        $this->assertInstanceOf(Deck::class, $result);
        $this->assertEquals('Updated Name', $result->name);
        $this->assertEquals('Updated Description', $result->description);
        
        $this->assertDatabaseHas('decks', [
            'id' => $deck->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description'
        ]);
    }

    public function test_update_returns_fresh_model()
    {
        $deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Original Name'
        ]);

        $originalUpdatedAt = $deck->updated_at;

        // Small delay to ensure updated_at changes
        sleep(1);

        $updateData = ['name' => 'Updated Name'];
        $result = $this->repository->update($deck, $updateData);

        // Result should be fresh from database
        $this->assertTrue($result->updated_at->gt($originalUpdatedAt));
        $this->assertEquals('Updated Name', $result->name);
    }

    public function test_delete_removes_model()
    {
        $deck = Deck::factory()->create(['user_id' => $this->user->id]);

        $result = $this->repository->delete($deck);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('decks', ['id' => $deck->id]);
    }

    public function test_paginate_returns_paginated_results()
    {
        Deck::factory()->count(25)->create(['user_id' => $this->user->id]);

        $result = $this->repository->paginate(10);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(25, $result->total());
        $this->assertEquals(3, $result->lastPage());
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
    }

    public function test_paginate_uses_default_per_page()
    {
        Deck::factory()->count(20)->create(['user_id' => $this->user->id]);

        $result = $this->repository->paginate();

        $this->assertEquals(15, $result->count()); // Default is 15
        $this->assertEquals(20, $result->total());
    }

    public function test_find_by_returns_matching_models()
    {
        $targetDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Target Deck',
            'is_public' => true
        ]);

        // Create other decks that don't match
        Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Other Deck',
            'is_public' => false
        ]);

        $criteria = [
            'name' => 'Target Deck',
            'is_public' => true
        ];

        $result = $this->repository->findBy($criteria);

        $this->assertCount(1, $result);
        $this->assertEquals($targetDeck->id, $result->first()->id);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_find_by_returns_empty_collection_when_no_matches()
    {
        Deck::factory()->create(['user_id' => $this->user->id]);

        $criteria = ['name' => 'Non-existent Deck'];
        $result = $this->repository->findBy($criteria);

        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
    }

    public function test_find_by_handles_multiple_criteria()
    {
        $user2 = User::factory()->create();

        Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Shared Name',
            'is_public' => true
        ]);

        Deck::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Shared Name',
            'is_public' => false
        ]);

        $criteria = [
            'user_id' => $this->user->id,
            'name' => 'Shared Name',
            'is_public' => true
        ];

        $result = $this->repository->findBy($criteria);

        $this->assertCount(1, $result);
        $this->assertEquals($this->user->id, $result->first()->user_id);
        $this->assertTrue($result->first()->is_public);
    }

    public function test_find_one_by_returns_first_matching_model()
    {
        $targetDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Target Deck',
            'is_public' => true
        ]);

        // Create another deck with same criteria (should not be returned)
        Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Target Deck',
            'is_public' => true
        ]);

        $criteria = [
            'name' => 'Target Deck',
            'is_public' => true
        ];

        $result = $this->repository->findOneBy($criteria);

        $this->assertNotNull($result);
        $this->assertInstanceOf(Deck::class, $result);
        $this->assertEquals('Target Deck', $result->name);
        $this->assertTrue($result->is_public);
    }

    public function test_find_one_by_returns_null_when_no_matches()
    {
        Deck::factory()->create(['user_id' => $this->user->id]);

        $criteria = ['name' => 'Non-existent Deck'];
        $result = $this->repository->findOneBy($criteria);

        $this->assertNull($result);
    }

    public function test_find_one_by_handles_multiple_criteria()
    {
        $user2 = User::factory()->create();

        $targetDeck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Specific Deck',
            'is_public' => false
        ]);

        // Create deck with partial match
        Deck::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Specific Deck',
            'is_public' => false
        ]);

        $criteria = [
            'user_id' => $this->user->id,
            'name' => 'Specific Deck'
        ];

        $result = $this->repository->findOneBy($criteria);

        $this->assertNotNull($result);
        $this->assertEquals($targetDeck->id, $result->id);
        $this->assertEquals($this->user->id, $result->user_id);
    }

    public function test_new_query_returns_fresh_query_builder()
    {
        $reflection = new \ReflectionClass($this->repository);
        $method = $reflection->getMethod('newQuery');
        $method->setAccessible(true);

        $result = $method->invoke($this->repository);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Builder::class, $result);
    }

    public function test_repository_interface_implementation()
    {
        $this->assertInstanceOf(\App\Contracts\Repository\RepositoryInterface::class, $this->repository);
    }

    public function test_repository_with_different_model_types()
    {
        // Test with User model
        $userRepository = new class(new User()) extends BaseRepository {};
        
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now()
        ];

        $user = $userRepository->create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_complex_operations_workflow()
    {
        // Create
        $deck = $this->repository->create([
            'name' => 'Workflow Test',
            'user_id' => $this->user->id,
            'is_public' => false
        ]);

        // Find
        $foundDeck = $this->repository->find($deck->id);
        $this->assertEquals('Workflow Test', $foundDeck->name);

        // Update
        $updatedDeck = $this->repository->update($foundDeck, [
            'name' => 'Updated Workflow Test',
            'is_public' => true
        ]);
        $this->assertEquals('Updated Workflow Test', $updatedDeck->name);
        $this->assertTrue($updatedDeck->is_public);

        // Find by criteria
        $searchResults = $this->repository->findBy(['is_public' => true]);
        $this->assertCount(1, $searchResults);

        // Delete
        $deleted = $this->repository->delete($updatedDeck);
        $this->assertTrue($deleted);

        // Verify deletion
        $this->assertNull($this->repository->find($deck->id));
    }
}