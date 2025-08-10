<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the API tokens for the user.
     */
    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    /**
     * Create a new API token for the user.
     */
    public function createApiToken(string $name, array $abilities = ['*'], ?Carbon $expiresAt = null): ApiToken
    {
        $token = ApiToken::generateToken();
        
        return $this->apiTokens()->create([
            'name' => $name,
            'token' => hash('sha256', $token),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Get the decks for the user.
     */
    public function decks(): HasMany
    {
        return $this->hasMany(Deck::class);
    }
}
