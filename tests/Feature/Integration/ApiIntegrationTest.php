<?php

namespace Tests\Feature\Integration;

use App\Models\ApiToken;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createAuthenticatedUser(): array
    {
        $user = User::factory()->create();
        $apiToken = $user->apiTokens()->create([
            'name' => 'Integration Test Token',
            'token' => hash('sha256', 'integration-test-token'),
            'abilities' => ['*'],
        ]);
        
        return [$user, $apiToken];
    }

    public function test_complete_api_workflow()
    {
        [$user, $apiToken] = $this->createAuthenticatedUser();
        
        // Create another user for private deck to test access control
        $otherUser = User::factory()->create();
        
        // Create test data
        $publicDeck = Deck::factory()->create([
            'user_id' => $user->id,
            'name' => 'Public API Test Deck',
            'description' => 'A deck for API testing',
            'is_public' => true
        ]);
        
        $privateDeck = Deck::factory()->create([
            'user_id' => $otherUser->id,  // Different user owns the private deck
            'name' => 'Private API Test Deck',
            'is_public' => false
        ]);
        
        // Create flashcards
        Flashcard::factory()->count(5)->create(['deck_id' => $publicDeck->id]);
        Flashcard::factory()->count(3)->create(['deck_id' => $privateDeck->id]);
        
        $headers = ['Authorization' => 'Bearer integration-test-token'];
        
        // Test 1: Authentication test
        $response = $this->withHeaders($headers)->get('/api/v1/auth/test');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success'])
                 ->assertJsonStructure([
                     'status', 'message', 'timestamp',
                     'data' => ['user', 'token']
                 ]);
        
        // Test 2: Get all decks (should only return public)
        $response = $this->withHeaders($headers)->get('/api/v1/decks');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success'])
                 ->assertJsonFragment(['name' => 'Public API Test Deck'])
                 ->assertJsonMissing(['name' => 'Private API Test Deck'])
                 ->assertJsonStructure([
                     'status', 'message', 'timestamp',
                     'data' => ['*' => ['id', 'name', 'slug', 'is_public', 'user']],
                     'pagination'
                 ]);
        
        // Test 3: Get specific public deck
        $response = $this->withHeaders($headers)->get("/api/v1/decks/{$publicDeck->slug}");
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'id' => $publicDeck->id,
                         'name' => 'Public API Test Deck',
                         'is_public' => true
                     ]
                 ]);
        
        // Test 4: Try to get private deck (should fail)
        $response = $this->withHeaders($headers)->get("/api/v1/decks/{$privateDeck->slug}");
        $response->assertStatus(404)
                 ->assertJson(['status' => 'not_found']);
        
        // Test 5: Get deck flashcards
        $response = $this->withHeaders($headers)->get("/api/v1/decks/{$publicDeck->slug}/flashcards");
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success'])
                 ->assertJsonCount(5, 'data')
                 ->assertJsonStructure([
                     'status', 'message', 'timestamp',
                     'data' => ['*' => ['id', 'question', 'answer']],
                     'pagination',
                     'meta' => ['deck']
                 ]);
        
        // Test 6: Search decks
        $response = $this->withHeaders($headers)->get('/api/v1/search/decks?q=API');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success'])
                 ->assertJsonFragment(['name' => 'Public API Test Deck'])
                 ->assertJsonStructure([
                     'pagination',
                     'meta' => ['query']
                 ]);
        
        // Test 7: Search with empty query (should fail)
        $response = $this->withHeaders(array_merge($headers, ['Accept' => 'application/json']))->get('/api/v1/search/decks');
        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'message',
                     'errors'
                 ]);
        
        // Test 8: Invalid endpoint (should 404)
        $response = $this->withHeaders($headers)->get('/api/v1/invalid-endpoint');
        $response->assertStatus(404);
    }

    public function test_api_authentication_methods()
    {
        [$user, $apiToken] = $this->createAuthenticatedUser();
        
        // Test Bearer token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer integration-test-token'
        ])->get('/api/v1/auth/test');
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        
        // Test X-API-Key header
        $response = $this->withHeaders([
            'X-API-Key' => 'integration-test-token'
        ])->get('/api/v1/auth/test');
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        
        // Test invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->get('/api/v1/auth/test');
        $response->assertStatus(401)->assertJson(['status' => 'unauthorized']);
        
        // Test no authentication
        $response = $this->get('/api/v1/auth/test');
        $response->assertStatus(401)->assertJson(['status' => 'unauthorized']);
    }

    public function test_api_response_structure_consistency()
    {
        [$user, $apiToken] = $this->createAuthenticatedUser();
        $headers = ['Authorization' => 'Bearer integration-test-token'];
        
        $endpoints = [
            '/api/v1/auth/test',
            '/api/v1/decks',
            '/api/v1/search/decks?q=test'
        ];
        
        foreach ($endpoints as $endpoint) {
            $response = $this->withHeaders($headers)->get($endpoint);
            
            if ($response->status() === 200) {
                $data = $response->json();
                
                // All successful responses should have these fields
                $this->assertArrayHasKey('status', $data);
                $this->assertArrayHasKey('message', $data);
                $this->assertArrayHasKey('timestamp', $data);
                
                // Status should be success for 200 responses
                $this->assertEquals('success', $data['status']);
                
                // Timestamp should be valid ISO format
                $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $data['timestamp']);
            }
        }
    }

    public function test_api_error_responses_consistency()
    {
        [$user, $apiToken] = $this->createAuthenticatedUser();
        $headers = ['Authorization' => 'Bearer integration-test-token'];
        
        $errorEndpoints = [
            ['/api/v1/search/decks', 422, 'validation_error'], // No query parameter
        ];
        
        // Test unauthorized responses separately with invalid token
        $unauthorizedHeaders = ['Authorization' => 'Bearer invalid-token'];
        $unauthorizedEndpoints = [
            ['/api/v1/auth/test', 401, 'unauthorized'],
            ['/api/v1/decks', 401, 'unauthorized'],
        ];
        
        foreach ($errorEndpoints as [$endpoint, $expectedStatus, $expectedStatusValue]) {
            $response = $this->withHeaders(array_merge($headers, ['Accept' => 'application/json']))->get($endpoint);
            
            $response->assertStatus($expectedStatus);
            
            if ($expectedStatus === 422) {
                // Validation errors have a different structure
                $response->assertJsonStructure([
                    'message',
                    'errors'
                ]);
            } else {
                // Other API errors have the standard structure
                $data = $response->json();
                $this->assertArrayHasKey('status', $data);
                $this->assertArrayHasKey('message', $data);
                $this->assertArrayHasKey('timestamp', $data);
                $this->assertEquals($expectedStatusValue, $data['status']);
            }
        }
        
        // Test unauthorized endpoints
        foreach ($unauthorizedEndpoints as [$endpoint, $expectedStatus, $expectedStatusValue]) {
            $response = $this->withHeaders($unauthorizedHeaders)->get($endpoint);
            
            $response->assertStatus($expectedStatus);
            
            $data = $response->json();
            $this->assertArrayHasKey('status', $data);
            $this->assertArrayHasKey('message', $data);
            $this->assertArrayHasKey('timestamp', $data);
            $this->assertEquals($expectedStatusValue, $data['status']);
        }
    }

    public function test_pagination_works_correctly()
    {
        [$user, $apiToken] = $this->createAuthenticatedUser();
        
        // Create 25 public decks
        Deck::factory()->count(25)->create([
            'user_id' => $user->id,
            'is_public' => true
        ]);
        
        $headers = ['Authorization' => 'Bearer integration-test-token'];
        
        // Test first page
        $response = $this->withHeaders($headers)->get('/api/v1/decks?per_page=10&page=1');
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(10, $data['data']);
        $this->assertEquals(1, $data['pagination']['current_page']);
        $this->assertEquals(10, $data['pagination']['per_page']);
        $this->assertEquals(25, $data['pagination']['total']);
        $this->assertEquals(3, $data['pagination']['last_page']);
        $this->assertTrue($data['pagination']['has_more_pages']);
        
        // Test last page
        $response = $this->withHeaders($headers)->get('/api/v1/decks?per_page=10&page=3');
        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertCount(5, $data['data']); // Last page should have 5 items
        $this->assertEquals(3, $data['pagination']['current_page']);
        $this->assertFalse($data['pagination']['has_more_pages']);
    }
}