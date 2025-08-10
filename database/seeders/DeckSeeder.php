<?php

namespace Database\Seeders;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users (idempotent)
        $john = User::updateOrCreate(
            ['email' => 'john@flashcardpro.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $jane = User::updateOrCreate(
            ['email' => 'jane@flashcardpro.com'],
            [
                'name' => 'Jane Smith',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Create decks for John
        $programmingDeck = Deck::firstOrCreate([
            'user_id' => $john->id,
            'name' => 'Programming Basics',
        ], [
            'description' => 'Essential programming concepts and terminology',
            'is_public' => true,
        ]);

        $mathDeck = Deck::firstOrCreate([
            'user_id' => $john->id,
            'name' => 'Basic Mathematics',
        ], [
            'description' => 'Fundamental math concepts and formulas',
            'is_public' => true,
        ]);

        $privateDeck = Deck::firstOrCreate([
            'user_id' => $john->id,
            'name' => 'Personal Notes',
        ], [
            'description' => 'My private study notes',
            'is_public' => false,
        ]);

        // Create decks for Jane
        $languageDeck = Deck::firstOrCreate([
            'user_id' => $jane->id,
            'name' => 'Spanish Vocabulary',
        ], [
            'description' => 'Common Spanish words and phrases',
            'is_public' => true,
        ]);

        $scienceDeck = Deck::firstOrCreate([
            'user_id' => $jane->id,
            'name' => 'Chemistry Basics',
        ], [
            'description' => 'Basic chemistry concepts and formulas',
            'is_public' => false,
        ]);

        echo "Created 2 users and 5 decks\n";
    }
}
