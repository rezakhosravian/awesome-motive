<?php

namespace Database\Seeders;

use App\Models\Deck;
use App\Models\Flashcard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlashcardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // avoid duplicates on re-seed by ensuring unique per (deck_id, question)
        // for SQLite/dev simplicity, we delete duplicates if any exist and re-insert predictably
        // (Production would enforce DB unique constraints)

        // Get the seeded decks
        $programmingDeck = Deck::where('name', 'Programming Basics')->first();
        $mathDeck = Deck::where('name', 'Basic Mathematics')->first();
        $languageDeck = Deck::where('name', 'Spanish Vocabulary')->first();

        if ($programmingDeck) {
            $programmingCards = [
                ['question' => 'What does HTML stand for?', 'answer' => 'HyperText Markup Language'],
                ['question' => 'What is a variable in programming?', 'answer' => 'A storage location with an associated name that contains data'],
                ['question' => 'What does CSS stand for?', 'answer' => 'Cascading Style Sheets'],
                ['question' => 'What is a function?', 'answer' => 'A block of code that performs a specific task and can be reused'],
                ['question' => 'What is debugging?', 'answer' => 'The process of finding and fixing errors in code'],
                ['question' => 'What is an API?', 'answer' => 'Application Programming Interface - a set of protocols for building software'],
                ['question' => 'What does MVC stand for?', 'answer' => 'Model-View-Controller - an architectural pattern'],
                ['question' => 'What is version control?', 'answer' => 'A system that tracks changes to files over time'],
            ];

            foreach ($programmingCards as $card) {
                Flashcard::firstOrCreate(
                    [
                        'deck_id' => $programmingDeck->id,
                        'question' => $card['question'],
                    ],
                    [
                        'answer' => $card['answer'],
                    ]
                );
            }
        }

        if ($mathDeck) {
            $mathCards = [
                ['question' => 'What is the formula for the area of a circle?', 'answer' => 'πr² (pi times radius squared)'],
                ['question' => 'What is the Pythagorean theorem?', 'answer' => 'a² + b² = c² (in a right triangle)'],
                ['question' => 'What is the derivative of x²?', 'answer' => '2x'],
                ['question' => 'What is 7! (7 factorial)?', 'answer' => '5040'],
                ['question' => 'What is the quadratic formula?', 'answer' => 'x = (-b ± √(b²-4ac)) / 2a'],
                ['question' => 'What is the value of π (pi) to 3 decimal places?', 'answer' => '3.142'],
            ];

            foreach ($mathCards as $card) {
                Flashcard::firstOrCreate(
                    [
                        'deck_id' => $mathDeck->id,
                        'question' => $card['question'],
                    ],
                    [
                        'answer' => $card['answer'],
                    ]
                );
            }
        }

        if ($languageDeck) {
            $spanishCards = [
                ['question' => 'Hello', 'answer' => 'Hola'],
                ['question' => 'Thank you', 'answer' => 'Gracias'],
                ['question' => 'Please', 'answer' => 'Por favor'],
                ['question' => 'Goodbye', 'answer' => 'Adiós'],
                ['question' => 'How are you?', 'answer' => '¿Cómo estás?'],
                ['question' => 'My name is...', 'answer' => 'Me llamo...'],
                ['question' => 'Water', 'answer' => 'Agua'],
                ['question' => 'Food', 'answer' => 'Comida'],
                ['question' => 'House', 'answer' => 'Casa'],
                ['question' => 'Friend', 'answer' => 'Amigo/Amiga'],
            ];

            foreach ($spanishCards as $card) {
                Flashcard::firstOrCreate(
                    [
                        'deck_id' => $languageDeck->id,
                        'question' => $card['question'],
                    ],
                    [
                        'answer' => $card['answer'],
                    ]
                );
            }
        }

        echo "Created flashcards for all demo decks\n";
    }
}
