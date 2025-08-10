<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="DeckResource",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Programming Basics"),
 *   @OA\Property(property="slug", type="string", example="programming-basics"),
 *   @OA\Property(property="description", type="string", example="Essential programming concepts and terminology"),
 *   @OA\Property(property="is_public", type="boolean", example=true),
 *   @OA\Property(property="flashcards_count", type="integer", nullable=true, example=8),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class DeckResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_public' => $this->is_public,
            'flashcards_count' => $this->whenCounted('flashcards'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'user' => new UserResource($this->whenLoaded('user')),
            'flashcards' => FlashcardResource::collection($this->whenLoaded('flashcards')),
        ];
    }
}