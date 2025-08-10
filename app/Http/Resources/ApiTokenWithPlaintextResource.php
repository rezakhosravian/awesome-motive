<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiTokenWithPlaintextResource extends JsonResource
{
    /**
     * The plaintext token that should be included in the response.
     */
    private string $plainToken;

    /**
     * Create a new resource instance.
     */
    public function __construct($resource, string $plainToken)
    {
        parent::__construct($resource);
        $this->plainToken = $plainToken;
    }

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
            'token' => $this->plainToken, // Only shown once during creation
            'abilities' => $this->abilities,
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}