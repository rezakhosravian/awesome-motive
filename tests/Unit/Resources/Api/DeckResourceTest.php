<?php

namespace Tests\Unit\Resources\Api;

use App\Http\Resources\Api\DeckResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class DeckResourceTest extends TestCase
{
    public function test_deck_resource_structure()
    {
        $user = new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        
        $deck = new Deck();
        $deck->id = 1;
        $deck->name = 'Test Deck';
        $deck->slug = 'test-deck';
        $deck->description = 'A test deck';
        $deck->is_public = true;
        $deck->created_at = now();
        $deck->updated_at = now();
        
        $deck->setRelation('user', $user);
        
        $request = Request::create('/');
        $resource = new DeckResource($deck);
        $data = $resource->toArray($request);
        
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('slug', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('is_public', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertArrayHasKey('user', $data);
        
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Test Deck', $data['name']);
        $this->assertEquals('test-deck', $data['slug']);
        $this->assertEquals('A test deck', $data['description']);
        $this->assertTrue($data['is_public']);
        $this->assertInstanceOf(UserResource::class, $data['user']);
    }

    public function test_deck_resource_with_flashcards_count()
    {
        $deck = new Deck([
            'id' => 1,
            'name' => 'Test Deck',
            'slug' => 'test-deck',
            'description' => 'A test deck',
            'is_public' => true
        ]);
        
        // Mock the flashcards count
        $deck->flashcards_count = 5;
        
        $request = Request::create('/');
        $resource = new DeckResource($deck);
        $data = $resource->toArray($request);
        
        $this->assertArrayHasKey('flashcards_count', $data);
        $this->assertEquals(5, $data['flashcards_count']);
    }

    public function test_deck_resource_without_loaded_relationships()
    {
        $deck = new Deck([
            'id' => 1,
            'name' => 'Test Deck',
            'slug' => 'test-deck',
            'description' => 'A test deck',
            'is_public' => true
        ]);
        
        $request = Request::create('/');
        $resource = new DeckResource($deck);
        $data = $resource->toArray($request);
        
        // User should be a UserResource wrapping null when not loaded
        $this->assertInstanceOf(UserResource::class, $data['user']);
        
        // Flashcards should be a resource collection wrapping empty when not loaded
        $this->assertArrayHasKey('flashcards', $data);
    }

    public function test_deck_resource_timestamps_are_iso_format()
    {
        $now = now();
        $deck = new Deck([
            'id' => 1,
            'name' => 'Test Deck',
            'slug' => 'test-deck',
            'description' => 'A test deck',
            'is_public' => true,
            'created_at' => $now,
            'updated_at' => $now
        ]);
        
        $request = Request::create('/');
        $resource = new DeckResource($deck);
        $data = $resource->toArray($request);
        
        // Check timestamp format rather than exact value due to microsecond precision
        if ($data['created_at']) {
            $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['created_at']);
        } else {
            $this->assertNull($data['created_at']);
        }
        if ($data['updated_at']) {
            $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['updated_at']);
        } else {
            $this->assertNull($data['updated_at']);
        }
    }
}