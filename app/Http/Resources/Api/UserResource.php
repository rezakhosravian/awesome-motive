<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->when($this->shouldShowEmail($request), $this->email),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Determine if email should be shown based on context
     */
    private function shouldShowEmail(Request $request): bool
    {
        $currentUser = $request->user();
        
        // Show email if it's the current user or if specifically requested
        return $currentUser && ($currentUser->id === $this->id || $request->has('include_email'));
    }
}