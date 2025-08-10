<?php

namespace Tests\Unit\DTOs;

use App\DTOs\CreateDeckDTO;
use App\Http\Requests\StoreDeckRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateDeckDTOTest extends TestCase
{
    use RefreshDatabase;
    public function test_from_request_uses_validated_inputs(): void
    {
        $user = User::factory()->create();
        $request = StoreDeckRequest::create('/decks', 'POST', [
            'name' => 'My Deck',
            'description' => 'About',
            'is_public' => true,
        ]);
        $request->setUserResolver(fn() => $user);
        $request->setMethod('POST');

        $this->app['router']->post('/decks', fn() => 'ok');
        $request->setContainer($this->app);
        $request->validateResolved();

        $dto = CreateDeckDTO::fromRequest($request);

        $this->assertSame('My Deck', $dto->name);
        $this->assertSame('About', $dto->description);
        $this->assertTrue($dto->isPublic);
        $this->assertSame($user->id, $dto->userId);
    }

    public function test_from_validated_request_falls_back_to_inputs(): void
    {
        $user = User::factory()->create();
        $req = Request::create('/decks', 'POST', [
            'name' => 'Another Deck',
            'description' => null,
            'is_public' => '1',
        ]);
        $req->setUserResolver(fn() => $user);

        $dto = CreateDeckDTO::fromValidatedRequest($req);

        $this->assertSame('Another Deck', $dto->name);
        $this->assertNull($dto->description);
        $this->assertTrue($dto->isPublic);
        $this->assertSame($user->id, $dto->userId);
    }

    public function test_from_array_builds_dto(): void
    {
        $dto = CreateDeckDTO::fromArray([
            'name' => 'Array Deck',
            'description' => 'Desc',
            'is_public' => false,
        ], 99);

        $this->assertSame('Array Deck', $dto->name);
        $this->assertSame('Desc', $dto->description);
        $this->assertFalse($dto->isPublic);
        $this->assertSame(99, $dto->userId);
        $this->assertSame([
            'name' => 'Array Deck',
            'description' => 'Desc',
            'is_public' => false,
            'user_id' => 99,
        ], $dto->toArray());
    }
}


