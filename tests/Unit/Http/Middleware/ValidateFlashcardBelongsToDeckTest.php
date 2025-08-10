<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ValidateFlashcardBelongsToDeck;
use App\Models\Deck;
use App\Models\Flashcard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ValidateFlashcardBelongsToDeckTest extends TestCase
{
    use RefreshDatabase;

    protected ValidateFlashcardBelongsToDeck $middleware;
    protected User $user;
    protected Deck $deck;
    protected Deck $otherDeck;
    protected Flashcard $flashcard;
    protected Flashcard $otherFlashcard;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->middleware = new ValidateFlashcardBelongsToDeck();
        $this->user = User::factory()->create();
        
        $this->deck = Deck::factory()->create(['user_id' => $this->user->id]);
        $this->otherDeck = Deck::factory()->create(['user_id' => $this->user->id]);
        
        $this->flashcard = Flashcard::factory()->create(['deck_id' => $this->deck->id]);
        $this->otherFlashcard = Flashcard::factory()->create(['deck_id' => $this->otherDeck->id]);
    }

    public function test_allows_request_when_flashcard_belongs_to_deck()
    {
        $request = Request::create('/test');
        $route = new Route(['GET'], '/test', []);
        $route->bind($request);
        
        // Set route parameters
        $route->setParameter('deck', $this->deck);
        $route->setParameter('flashcard', $this->flashcard);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_aborts_when_flashcard_does_not_belong_to_deck()
    {
        $this->expectException(NotFoundHttpException::class);

        $request = Request::create('/test');
        $route = new Route(['GET'], '/test', []);
        $route->bind($request);
        
        // Set route parameters with mismatched deck and flashcard
        $route->setParameter('deck', $this->deck);
        $route->setParameter('flashcard', $this->otherFlashcard); // This belongs to otherDeck
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    public function test_allows_request_when_deck_parameter_missing()
    {
        $request = Request::create('/test');
        $route = new Route(['GET'], '/test', []);
        $route->bind($request);
        
        // Only set flashcard parameter
        $route->setParameter('flashcard', $this->flashcard);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_allows_request_when_flashcard_parameter_missing()
    {
        $request = Request::create('/test');
        $route = new Route(['GET'], '/test', []);
        $route->bind($request);
        
        // Only set deck parameter
        $route->setParameter('deck', $this->deck);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_allows_request_when_both_parameters_missing()
    {
        $request = Request::create('/test');
        $route = new Route(['GET'], '/test', []);
        $route->bind($request);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_prevents_idor_attack()
    {
        $this->expectException(NotFoundHttpException::class);

        // Simulate an IDOR attack where attacker tries to access
        // a flashcard from a different deck by manipulating the URL
        $attackerDeck = Deck::factory()->create(['user_id' => $this->user->id]);
        $victimFlashcard = $this->flashcard; // This belongs to a different deck

        $request = Request::create('/test');
        $route = new Route(['GET'], '/test', []);
        $route->bind($request);
        
        $route->setParameter('deck', $attackerDeck);
        $route->setParameter('flashcard', $victimFlashcard);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $this->middleware->handle($request, function ($req) {
            return response('Attack successful');
        });
    }

    public function test_middleware_works_with_different_deck_and_flashcard_combinations()
    {
        // Test multiple valid combinations
        $combinations = [
            [$this->deck, $this->flashcard], // Valid combination
        ];

        foreach ($combinations as [$deck, $flashcard]) {
            $request = Request::create('/test');
            $route = new Route(['GET'], '/test', []);
            $route->bind($request);
            
            $route->setParameter('deck', $deck);
            $route->setParameter('flashcard', $flashcard);
            $request->setRouteResolver(function () use ($route) {
                return $route;
            });

            $response = $this->middleware->handle($request, function ($req) {
                return response('Valid');
            });

            $this->assertEquals('Valid', $response->getContent());
        }
    }

    public function test_middleware_returns_404_not_403_for_security()
    {
        try {
            $request = Request::create('/test');
            $route = new Route(['GET'], '/test', []);
            $route->bind($request);
            
            $route->setParameter('deck', $this->deck);
            $route->setParameter('flashcard', $this->otherFlashcard);
            $request->setRouteResolver(function () use ($route) {
                return $route;
            });

            $this->middleware->handle($request, function ($req) {
                return response('OK');
            });

            $this->fail('Expected NotFoundHttpException was not thrown');
        } catch (NotFoundHttpException $e) {
            // Should be 404, not 403, to avoid information disclosure
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    public function test_middleware_handles_edge_case_with_null_values()
    {
        $request = Request::create('/test');
        $route = new Route(['GET'], '/test', []);
        $route->bind($request);
        
        // Set null values
        $route->setParameter('deck', null);
        $route->setParameter('flashcard', null);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }
}