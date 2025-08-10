<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    private function createAuthenticatedUser()
    {
        $user = User::factory()->create();
        $user->apiTokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', 'valid-token'),
            'abilities' => ['*'],
        ]);
        return $user;
    }

    public function test_decks_index_requires_authentication()
    {
        $response = $this->get('/api/v1/decks');
        
        $response->assertStatus(401);
        $response->assertJson(['status' => 'unauthorized']);
    }

    public function test_decks_index_returns_paginated_public_decks()
    {
        $user = $this->createAuthenticatedUser();
        
        // Create public and private decks
        $publicDeck = Deck::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Public Deck'
        ]);
        
        $privateDeck = Deck::factory()->create([
            'user_id' => $user->id,
            'is_public' => false,
            'name' => 'Private Deck'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token'
        ])->get('/api/v1/decks');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'timestamp',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'is_public',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'id',
                        'name',
                        'created_at'
                    ]
                ]
            ],
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
                'from',
                'to',
                'has_more_pages'
            ]
        ]);
        
        $response->assertJson([
            'status' => 'success'
        ]);
        
        // Should include public deck
        $response->assertJsonFragment(['name' => 'Public Deck']);
        
        // Should not include private deck
        $response->assertJsonMissing(['name' => 'Private Deck']);
    }

    public function test_decks_index_respects_pagination()
    {
        $user = $this->createAuthenticatedUser();
        
        // Create multiple public decks
        Deck::factory()->count(25)->create([
            'user_id' => $user->id,
            'is_public' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token'
        ])->get('/api/v1/decks?per_page=10');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(10, count($data['data']));
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(10, $data['pagination']['per_page']);
        $this->assertEquals(25, $data['pagination']['total']);
        $this->assertTrue($data['pagination']['has_more_pages']);
    }

    public function test_decks_index_with_custom_per_page()
    {
        $user = $this->createAuthenticatedUser();
        
        Deck::factory()->count(5)->create([
            'user_id' => $user->id,
            'is_public' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token'
        ])->get('/api/v1/decks?per_page=3');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertEquals(3, count($data['data']));
        $this->assertEquals(3, $data['pagination']['per_page']);
    }

    public function test_decks_index_returns_empty_when_no_public_decks()
    {
        $user = $this->createAuthenticatedUser();
        
        // Create only private decks
        Deck::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_public' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token'
        ])->get('/api/v1/decks');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => []
        ]);
        
        $data = $response->json();
        $this->assertEquals(0, $data['pagination']['total']);
    }

    public function test_deck_resource_structure()
    {
        $user = $this->createAuthenticatedUser();
        
        $deck = Deck::factory()->create([
            'user_id' => $user->id,
            'is_public' => true,
            'name' => 'Test Deck',
            'description' => 'Test Description'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer valid-token'
        ])->get('/api/v1/decks');

        $response->assertStatus(200);
        
        $data = $response->json();
        $deckData = $data['data'][0];
        
        $this->assertEquals($deck->id, $deckData['id']);
        $this->assertEquals('Test Deck', $deckData['name']);
        $this->assertEquals($deck->slug, $deckData['slug']);
        $this->assertEquals('Test Description', $deckData['description']);
        $this->assertTrue($deckData['is_public']);
        $this->assertArrayHasKey('created_at', $deckData);
        $this->assertArrayHasKey('updated_at', $deckData);
        $this->assertArrayHasKey('user', $deckData);
        
        // Verify user structure
        $this->assertEquals($user->id, $deckData['user']['id']);
        $this->assertEquals($user->name, $deckData['user']['name']);
    }
}