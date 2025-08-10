<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Mockery;
use App\Services\DeckService;
use App\Contracts\Repository\DeckRepositoryInterface;
use App\DTOs\CreateDeckDTO;
use App\DTOs\UpdateDeckDTO;
use App\Models\User;
use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Illuminate\Support\Facades\Event;
use App\Events\DeckCreated;

class DeckServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DeckService $service;
    protected $mockRepository;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        Event::fake([DeckCreated::class]);
        $this->user = User::factory()->create();
        $this->actingAs($this->user); // Set authentication context
        
        $this->mockRepository = Mockery::mock(DeckRepositoryInterface::class);
        $this->service = new DeckService($this->mockRepository);
    }

    public function test_create_deck_adds_user_id_and_defaults(): void
    {
        $dto = CreateDeckDTO::fromArray([
            'name' => 'Test Deck',
            'description' => 'Test Description'
        ], $this->user->id);

        $expectedData = [
            'name' => 'Test Deck',
            'description' => 'Test Description',
            'user_id' => $this->user->id,
            'is_public' => false
        ];

        $deck = Deck::factory()->make($expectedData);

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($expectedData)
            ->andReturn($deck);

        $result = $this->service->createDeck($this->user, $dto);

        $this->assertEquals($deck, $result);
        Event::assertDispatched(DeckCreated::class);
    }

    public function test_update_deck_authorizes_and_validates_ownership(): void
    {
        $deck = Deck::factory()->for($this->user)->create();
        $dto = UpdateDeckDTO::fromArray(['name' => 'Updated Name']);

        $this->mockRepository
            ->shouldReceive('update')
            ->once()
            ->with($deck, $dto->toArray())
            ->andReturn($deck);

        $result = $this->service->updateDeck($this->user, $deck, $dto);

        $this->assertEquals($deck, $result);
    }

    public function test_update_deck_throws_exception_for_wrong_user(): void
    {
        $otherUser = User::factory()->create();
        $deck = Deck::factory()->for($otherUser)->create();
        $dto = UpdateDeckDTO::fromArray(['name' => 'Updated']);
        
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        
        $this->service->updateDeck($this->user, $deck, $dto);
    }

    public function test_delete_deck_validates_ownership(): void
    {
        $deck = Deck::factory()->for($this->user)->create();

        $this->mockRepository
            ->shouldReceive('delete')
            ->once()
            ->with($deck)
            ->andReturn(true);

        $result = $this->service->deleteDeck($this->user, $deck);

        $this->assertTrue($result);
    }

    public function test_get_user_decks(): void
    {
        $mockPaginator = \Mockery::mock(\Illuminate\Pagination\LengthAwarePaginator::class);

        $this->mockRepository
            ->shouldReceive('getUserDecksPaginated')
            ->once()
            ->with($this->user, 15)
            ->andReturn($mockPaginator);

        $result = $this->service->getUserDecks($this->user);

        $this->assertEquals($mockPaginator, $result);
    }

    public function test_can_user_access_deck_public(): void
    {
        $deck = Deck::factory()->make(['is_public' => true]);

        $result = $this->service->canUserAccessDeck($deck, $this->user);

        $this->assertTrue($result);
    }

    public function test_can_user_access_deck_private_owner(): void
    {
        $deck = Deck::factory()->for($this->user)->make(['is_public' => false]);

        $result = $this->service->canUserAccessDeck($deck, $this->user);

        $this->assertTrue($result);
    }

    public function test_can_user_access_deck_private_non_owner(): void
    {
        $otherUser = User::factory()->make(['id' => 999]);
        $deck = Deck::factory()->for($otherUser)->make(['is_public' => false]);

        $result = $this->service->canUserAccessDeck($deck, $this->user);

        $this->assertFalse($result);
    }

    public function test_get_deck_for_study_success(): void
    {
        $deck = Deck::factory()->for($this->user)->create();
        Flashcard::factory()->for($deck)->create();

        $this->mockRepository
            ->shouldReceive('getDeckWithFlashcards')
            ->once()
            ->with($deck->id)
            ->andReturn($deck);

        $result = $this->service->getDeckForStudy($deck->id, $this->user);

        $this->assertEquals($deck, $result);
    }

    public function test_get_deck_for_study_throws_exception_when_deck_not_found(): void
    {
        $this->mockRepository
            ->shouldReceive('getDeckWithFlashcards')
            ->once()
            ->with(999)
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->service->getDeckForStudy(999, $this->user);
    }

    public function test_get_deck_for_study_throws_exception_when_no_flashcards(): void
    {
        $deck = Deck::factory()->for($this->user)->create();
        $deck->setRelation('flashcards', collect([]));

        $this->mockRepository
            ->shouldReceive('getDeckWithFlashcards')
            ->once()
            ->with($deck->id)
            ->andReturn($deck);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This deck has no flashcards to study');

        $this->service->getDeckForStudy($deck->id, $this->user);
    }

    public function test_get_deck_stats(): void
    {
        $deck = Deck::factory()->for($this->user)->create();
        
        // Create flashcards for the deck
        Flashcard::factory()->count(5)->for($deck)->create();

        $stats = $this->service->getDeckStats($deck);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('flashcard_count', $stats);
        $this->assertEquals(5, $stats['flashcard_count']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 