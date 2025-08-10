<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDeckTest extends TestCase
{
    use RefreshDatabase;

    private $testToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use SQLite in-memory for tests
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        
        // Create a test token for API authentication
        $user = User::factory()->create();
        $this->testToken = $user->apiTokens()->create([
            'name' => 'Test API Token',
            'token' => hash('sha256', 'flashcard-pro-demo-key-123'),
            'abilities' => ['*'],
        ]);
    }

    private function createTestDecks()
    {
        $user = User::factory()->create();
        
        $publicDeck = Deck::factory()->create([
            'user_id' => $user->id,
            'name' => 'Public Programming Deck',
            'description' => 'Learn programming concepts',
            'is_public' => true,
        ]);
        
        $privateDeck = Deck::factory()->create([
            'user_id' => $user->id,
            'name' => 'Private Notes',
            'is_public' => false,
        ]);
        
        // Add flashcards
        Flashcard::factory()->count(3)->create(['deck_id' => $publicDeck->id]);
        Flashcard::factory()->count(2)->create(['deck_id' => $privateDeck->id]);

        return compact('user', 'publicDeck', 'privateDeck');
    }

    public function test_api_requires_valid_api_key()
    {
        $response = $this->get('/api/v1/decks');
        
        $response->assertStatus(401);
        $response->assertJsonStructure([
            'status',
            'message',
            'timestamp'
        ]);
        $response->assertJson(['status' => 'unauthorized']);
    }

    public function test_api_accepts_valid_bearer_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer flashcard-pro-demo-key-123'
        ])->get('/api/v1/decks');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'timestamp',
            'data',
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
        $response->assertJson(['status' => 'success']);
    }

    public function test_api_accepts_valid_api_key_in_header()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->get('/api/v1/decks');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message', 
            'timestamp',
            'data',
            'pagination'
        ]);
        $response->assertJson(['status' => 'success']);
    }

    public function test_api_returns_only_public_decks()
    {
        $data = $this->createTestDecks();
        
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->get('/api/v1/decks');
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $data['publicDeck']->name])
                 ->assertJsonMissing(['name' => $data['privateDeck']->name]);
    }

    public function test_api_can_get_specific_public_deck()
    {
        $data = $this->createTestDecks();
        
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->get('/api/v1/decks/' . $data['publicDeck']->slug);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $data['publicDeck']->id,
                         'name' => $data['publicDeck']->name,
                         'description' => $data['publicDeck']->description,
                         'is_public' => true
                     ]
                 ]);
    }

    public function test_api_cannot_get_private_deck()
    {
        $data = $this->createTestDecks();
        
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->get('/api/v1/decks/' . $data['privateDeck']->slug);
        
        $response->assertStatus(404)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'timestamp'
                 ])
                 ->assertJson(['status' => 'not_found']);
    }

    public function test_api_can_get_deck_flashcards()
    {
        $data = $this->createTestDecks();
        
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->get('/api/v1/decks/' . $data['publicDeck']->slug . '/flashcards');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'timestamp',
                     'data' => [
                         '*' => [
                             'id',
                             'question',
                             'answer'
                         ]
                     ],
                     'pagination',
                     'meta' => [
                         'deck'
                     ]
                 ])
                 ->assertJsonCount(3, 'data')
                 ->assertJson(['status' => 'success']);
    }

    public function test_api_can_search_decks()
    {
        $data = $this->createTestDecks();
        
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->get('/api/v1/search/decks?q=Programming');
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => $data['publicDeck']->name]);
    }

    public function test_api_search_requires_query()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123',
            'Accept' => 'application/json'
        ])->get('/api/v1/search/decks');
        
        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors'
                 ]);
    }

    public function test_api_returns_404_for_invalid_slug()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123',
            'Accept' => 'application/json'
        ])->get('/api/v1/decks/invalid-slug-123');
        
        $response->assertStatus(404)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'timestamp'
                 ])
                 ->assertJson(['status' => 'not_found']);
    }

    public function test_api_returns_404_for_invalid_slug_flashcards()
    {
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123',
            'Accept' => 'application/json'
        ])->get('/api/v1/decks/invalid-slug-123/flashcards');
        
        $response->assertStatus(404)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'timestamp'
                 ])
                 ->assertJson(['status' => 'not_found']);
    }

    public function test_api_returns_slug_in_deck_response()
    {
        $data = $this->createTestDecks();
        
        $response = $this->withHeaders([
            'X-API-Key' => 'flashcard-pro-demo-key-123'
        ])->get('/api/v1/decks/' . $data['publicDeck']->slug);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'slug' => $data['publicDeck']->slug
                     ]
                 ]);
    }
}
