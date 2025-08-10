<?php

namespace Tests\Unit\Services;

use App\Contracts\Repository\FlashcardRepositoryInterface;
use App\Contracts\Service\DeckServiceInterface;
use App\DTOs\CreateFlashcardDTO;
use App\DTOs\UpdateFlashcardDTO;
use App\Exceptions\Flashcard\FlashcardAccessDeniedException;
use App\Exceptions\Flashcard\FlashcardNotFoundException;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use App\Services\FlashcardService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class FlashcardServiceTest extends TestCase
{
    use RefreshDatabase;

    private FlashcardService $service;
    private FlashcardRepositoryInterface $flashcardRepository;
    private DeckServiceInterface $deckService;
    private User $user;
    private User $otherUser;
    private Deck $deck;
    private Deck $privateDeck;
    private Flashcard $flashcard;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashcardRepository = $this->createMock(FlashcardRepositoryInterface::class);
        $this->deckService = $this->createMock(DeckServiceInterface::class);
        
        $this->service = new FlashcardService($this->flashcardRepository, $this->deckService);

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->deck = Deck::factory()->for($this->user)->create(['is_public' => true]);
        $this->privateDeck = Deck::factory()->for($this->user)->create(['is_public' => false]);
        $this->flashcard = Flashcard::factory()->for($this->deck)->create();
    }

    public function test_create_flashcard_success()
    {
        $dto = new CreateFlashcardDTO('Test Question', 'Test Answer', $this->deck->id);
        $expectedFlashcard = new Flashcard();

        $this->flashcardRepository
            ->expects($this->once())
            ->method('createForDeck')
            ->with($this->deck, $dto->toArray())
            ->willReturn($expectedFlashcard);

        $this->actingAs($this->user);
        $result = $this->service->createFlashcard($this->user, $this->deck, $dto);

        $this->assertSame($expectedFlashcard, $result);
    }

    public function test_create_flashcard_fails_authorization()
    {
        $dto = new CreateFlashcardDTO('Test Question', 'Test Answer', $this->deck->id);

        $this->actingAs($this->otherUser);
        
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->service->createFlashcard($this->otherUser, $this->deck, $dto);
    }

    public function test_create_flashcard_fails_permission_check()
    {
        $dto = new CreateFlashcardDTO('Test Question', 'Test Answer', $this->deck->id);
        $otherUserDeck = Deck::factory()->for($this->otherUser)->create();

        $this->actingAs($this->otherUser); // Acting as other user trying to access their own deck
        
        $this->expectException(ModelNotFoundException::class);
        $this->service->createFlashcard($this->user, $otherUserDeck, $dto); // But service call with first user
    }

    public function test_create_flashcard_fails_validation()
    {
        $dto = new CreateFlashcardDTO('', 'Test Answer', $this->deck->id); // Empty question

        $this->actingAs($this->user);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->service->createFlashcard($this->user, $this->deck, $dto);
    }

    public function test_create_flashcard_from_array()
    {
        $data = ['question' => 'Array Question', 'answer' => 'Array Answer'];
        $expectedFlashcard = new Flashcard();

        $this->flashcardRepository
            ->expects($this->once())
            ->method('createForDeck')
            ->willReturn($expectedFlashcard);

        $this->actingAs($this->user);
        $result = $this->service->createFlashcardFromArray($this->user, $this->deck, $data);

        $this->assertSame($expectedFlashcard, $result);
    }

    public function test_update_flashcard_success()
    {
        $dto = new UpdateFlashcardDTO('Updated Question', 'Updated Answer');
        $expectedFlashcard = new Flashcard();

        $this->flashcardRepository
            ->expects($this->once())
            ->method('update')
            ->with($this->flashcard, $dto->toArray())
            ->willReturn($expectedFlashcard);

        $this->actingAs($this->user);
        $result = $this->service->updateFlashcard($this->user, $this->deck, $this->flashcard, $dto);

        $this->assertSame($expectedFlashcard, $result);
    }

    public function test_update_flashcard_fails_authorization()
    {
        $dto = new UpdateFlashcardDTO('Updated Question', 'Updated Answer');

        $this->actingAs($this->otherUser);
        
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->service->updateFlashcard($this->otherUser, $this->deck, $this->flashcard, $dto);
    }

    public function test_update_flashcard_fails_permission_check()
    {
        $dto = new UpdateFlashcardDTO('Updated Question', 'Updated Answer');
        $otherUserDeck = Deck::factory()->for($this->otherUser)->create();

        $this->actingAs($this->otherUser);
        
        $this->expectException(ModelNotFoundException::class);
        $this->service->updateFlashcard($this->user, $otherUserDeck, $this->flashcard, $dto);
    }

    public function test_update_flashcard_fails_deck_mismatch()
    {
        $dto = new UpdateFlashcardDTO('Updated Question', 'Updated Answer');
        $otherDeck = Deck::factory()->for($this->user)->create();

        $this->actingAs($this->user);
        
        $this->expectException(ModelNotFoundException::class);
        $this->service->updateFlashcard($this->user, $otherDeck, $this->flashcard, $dto);
    }

    public function test_update_flashcard_fails_validation()
    {
        $dto = new UpdateFlashcardDTO('', 'Updated Answer'); // Empty question

        $this->actingAs($this->user);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->service->updateFlashcard($this->user, $this->deck, $this->flashcard, $dto);
    }

    public function test_update_flashcard_from_array()
    {
        $data = ['question' => 'Array Question', 'answer' => 'Array Answer'];
        $expectedFlashcard = new Flashcard();

        $this->flashcardRepository
            ->expects($this->once())
            ->method('update')
            ->willReturn($expectedFlashcard);

        $this->actingAs($this->user);
        $result = $this->service->updateFlashcardFromArray($this->user, $this->deck, $this->flashcard, $data);

        $this->assertSame($expectedFlashcard, $result);
    }

    public function test_delete_flashcard_success()
    {
        $this->flashcardRepository
            ->expects($this->once())
            ->method('delete')
            ->with($this->flashcard)
            ->willReturn(true);

        $this->actingAs($this->user);
        $result = $this->service->deleteFlashcard($this->user, $this->deck, $this->flashcard);

        $this->assertTrue($result);
    }

    public function test_delete_flashcard_fails_authorization()
    {
        $this->actingAs($this->otherUser);
        
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->service->deleteFlashcard($this->otherUser, $this->deck, $this->flashcard);
    }

    public function test_delete_flashcard_fails_permission_check()
    {
        $otherUserDeck = Deck::factory()->for($this->otherUser)->create();

        $this->actingAs($this->otherUser);
        
        $this->expectException(ModelNotFoundException::class);
        $this->service->deleteFlashcard($this->user, $otherUserDeck, $this->flashcard);
    }

    public function test_delete_flashcard_fails_deck_mismatch()
    {
        $otherDeck = Deck::factory()->for($this->user)->create();

        $this->actingAs($this->user);
        
        $this->expectException(ModelNotFoundException::class);
        $this->service->deleteFlashcard($this->user, $otherDeck, $this->flashcard);
    }

    public function test_get_flashcards_for_deck_success()
    {
        $perPage = 15;
        $paginator = new LengthAwarePaginator([], 0, $perPage);

        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(true);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getByDeckPaginated')
            ->with($this->deck, $perPage)
            ->willReturn($paginator);

        config(['flashcard.pagination.default_per_page' => 15]);

        $result = $this->service->getFlashcardsForDeck($this->deck, $this->user);

        $this->assertSame($paginator, $result);
    }

    public function test_get_flashcards_for_deck_fails_permission()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(false);

        $this->expectException(ModelNotFoundException::class);
        $this->service->getFlashcardsForDeck($this->deck, $this->user);
    }

    public function test_get_flashcards_for_deck_with_custom_per_page()
    {
        $perPage = 25;
        $paginator = new LengthAwarePaginator([], 0, $perPage);

        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->willReturn(true);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getByDeckPaginated')
            ->with($this->deck, $perPage)
            ->willReturn($paginator);

        $result = $this->service->getFlashcardsForDeck($this->deck, $this->user, $perPage);

        $this->assertSame($paginator, $result);
    }

    public function test_get_flashcard_success()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(true);

        $result = $this->service->getFlashcard($this->deck, $this->flashcard, $this->user);

        $this->assertSame($this->flashcard, $result);
    }

    public function test_get_flashcard_fails_permission()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(false);

        $this->expectException(ModelNotFoundException::class);
        $this->service->getFlashcard($this->deck, $this->flashcard, $this->user);
    }

    public function test_get_flashcard_fails_deck_mismatch()
    {
        $otherDeck = Deck::factory()->for($this->user)->create();

        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->willReturn(true);

        $this->expectException(ModelNotFoundException::class);
        $this->service->getFlashcard($otherDeck, $this->flashcard, $this->user);
    }

    public function test_get_flashcard_for_user_success()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(true);

        $result = $this->service->getFlashcardForUser($this->deck, $this->flashcard, $this->user);

        $this->assertSame($this->flashcard, $result);
    }

    public function test_get_flashcard_for_user_fails_access()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(false);

        $this->expectException(FlashcardAccessDeniedException::class);
        $this->service->getFlashcardForUser($this->deck, $this->flashcard, $this->user);
    }

    public function test_get_flashcard_for_user_fails_deck_mismatch()
    {
        $otherDeck = Deck::factory()->for($this->user)->create();

        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->willReturn(true);

        $this->expectException(FlashcardNotFoundException::class);
        $this->service->getFlashcardForUser($otherDeck, $this->flashcard, $this->user);
    }

    public function test_can_user_manage_flashcards_in_deck_owner()
    {
        $result = $this->service->canUserManageFlashcardsInDeck($this->user, $this->deck);

        $this->assertTrue($result);
    }

    public function test_can_user_manage_flashcards_in_deck_not_owner()
    {
        $result = $this->service->canUserManageFlashcardsInDeck($this->otherUser, $this->deck);

        $this->assertFalse($result);
    }

    public function test_can_user_view_flashcards_in_deck()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(true);

        $result = $this->service->canUserViewFlashcardsInDeck($this->deck, $this->user);

        $this->assertTrue($result);
    }

    public function test_get_flashcards_for_user_with_access_success()
    {
        $perPage = 20;
        $paginator = new LengthAwarePaginator([], 0, $perPage);

        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(true);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getByDeckPaginated')
            ->with($this->deck, $perPage)
            ->willReturn($paginator);

        $result = $this->service->getFlashcardsForUserWithAccess($this->deck, $this->user, $perPage);

        $this->assertSame($paginator, $result);
    }

    public function test_get_flashcards_for_user_with_access_fails()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, $this->user)
            ->willReturn(false);

        $this->expectException(\App\Exceptions\Deck\DeckAccessDeniedException::class);
        $this->service->getFlashcardsForUserWithAccess($this->deck, $this->user, 20);
    }

    public function test_get_flashcards_for_deck_without_user()
    {
        $perPage = 15;
        $paginator = new LengthAwarePaginator([], 0, $perPage);

        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, null)
            ->willReturn(true);

        $this->flashcardRepository
            ->expects($this->once())
            ->method('getByDeckPaginated')
            ->with($this->deck, $perPage)
            ->willReturn($paginator);

        config(['flashcard.pagination.default_per_page' => 15]);

        $result = $this->service->getFlashcardsForDeck($this->deck, null);

        $this->assertSame($paginator, $result);
    }

    public function test_get_flashcard_without_user()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, null)
            ->willReturn(true);

        $result = $this->service->getFlashcard($this->deck, $this->flashcard, null);

        $this->assertSame($this->flashcard, $result);
    }

    public function test_get_flashcard_for_user_without_user()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, null)
            ->willReturn(true);

        $result = $this->service->getFlashcardForUser($this->deck, $this->flashcard, null);

        $this->assertSame($this->flashcard, $result);
    }

    public function test_can_user_view_flashcards_in_deck_without_user()
    {
        $this->deckService
            ->expects($this->once())
            ->method('canUserAccessDeck')
            ->with($this->deck, null)
            ->willReturn(true);

        $result = $this->service->canUserViewFlashcardsInDeck($this->deck, null);

        $this->assertTrue($result);
    }
}
