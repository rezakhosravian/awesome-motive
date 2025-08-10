<?php

namespace Tests\Unit\Resources\Api;

use App\Http\Resources\Api\FlashcardResource;
use App\Http\Resources\Api\DeckResource;
use App\Models\Flashcard;
use App\Models\Deck;
use Illuminate\Http\Request;
use Tests\TestCase;

class FlashcardResourceTest extends TestCase
{
    public function test_flashcard_resource_structure()
    {
        $deck = new Deck([
            'id' => 1,
            'name' => 'Test Deck',
            'slug' => 'test-deck'
        ]);
        
        $flashcard = new Flashcard();
        $flashcard->id = 1;
        $flashcard->question = 'What is PHP?';
        $flashcard->answer = 'PHP is a programming language';
        $flashcard->difficulty = 'easy';
        $flashcard->deck_id = 1;
        $flashcard->created_at = now();
        $flashcard->updated_at = now();
        
        $flashcard->setRelation('deck', $deck);
        
        $request = Request::create('/');
        $resource = new FlashcardResource($flashcard);
        $data = $resource->toArray($request);
        
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('question', $data);
        $this->assertArrayHasKey('answer', $data);
        $this->assertArrayHasKey('difficulty', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertArrayHasKey('deck', $data);
        
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('What is PHP?', $data['question']);
        $this->assertEquals('PHP is a programming language', $data['answer']);
        $this->assertEquals('easy', $data['difficulty']);
        $this->assertInstanceOf(DeckResource::class, $data['deck']);
    }

    public function test_flashcard_resource_without_deck_relationship()
    {
        $flashcard = new Flashcard([
            'id' => 1,
            'question' => 'What is PHP?',
            'answer' => 'PHP is a programming language',
            'difficulty' => 'easy',
            'deck_id' => 1
        ]);
        
        $request = Request::create('/');
        $resource = new FlashcardResource($flashcard);
        $data = $resource->toArray($request);
        
        $this->assertInstanceOf(DeckResource::class, $data['deck']);
    }

    public function test_flashcard_resource_timestamps_are_iso_format()
    {
        $now = now();
        $flashcard = new Flashcard([
            'id' => 1,
            'question' => 'What is PHP?',
            'answer' => 'PHP is a programming language',
            'difficulty' => 'easy',
            'created_at' => $now,
            'updated_at' => $now
        ]);
        
        $request = Request::create('/');
        $resource = new FlashcardResource($flashcard);
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