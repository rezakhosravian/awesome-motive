<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="ApiTokenResource",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="CI Token"),
 *   @OA\Property(property="abilities", type="array", @OA\Items(type="string"), example={"*"}),
 *   @OA\Property(property="last_used_at", type="string", format="date-time", nullable=true, example="2025-08-11T09:17:18Z"),
 *   @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, example="2025-12-31T23:59:59Z"),
 *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true, example="2025-08-11T09:17:18Z"),
 *   @OA\Property(property="is_expired", type="boolean", example=false)
 * )
 */
class ApiTokenResource extends JsonResource
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
            'abilities' => $this->abilities,
            'last_used_at' => $this->last_used_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'is_expired' => $this->isExpired(),
        ];
    }
}