<?php

namespace App\Livewire;

use App\Models\Deck;
use App\Models\Flashcard;
use Livewire\Component;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StudyDeck extends Component
{
    use AuthorizesRequests;

    public Deck $deck;
    public Collection $flashcards;
    public ?Flashcard $currentCard = null;
    public int $currentIndex = 0;
    public bool $answer = false;
    public bool $isComplete = false;
    public array $studiedCards = [];
    public int $correctCount = 0;
    public int $incorrectCount = 0;
    public bool $shuffled = false;

    public function mount(Deck $deck)
    {
        $this->authorize('view', $deck);
        
        $this->deck = $deck;
        $this->flashcards = $deck->flashcards;
        
        if ($this->flashcards->isEmpty()) {
            $this->isComplete = true;
            return;
        }
        
        $this->currentCard = $this->flashcards->first();
    }

    public function shuffleCards()
    {
        $this->flashcards = $this->flashcards->shuffle();
        $this->currentCard = $this->flashcards->first();
        $this->currentIndex = 0;
        $this->answer = false;
        $this->shuffled = true;
        $this->resetStats();
    }

    public function showAnswer()
    {
        $this->answer = true;
    }

    public function markCorrect()
    {
        $this->correctCount++;
        $this->studiedCards[$this->currentCard->id] = 'correct';
        $this->nextCard();
    }

    public function markIncorrect()
    {
        $this->incorrectCount++;
        $this->studiedCards[$this->currentCard->id] = 'incorrect';
        $this->nextCard();
    }

    public function nextCard()
    {
        $this->answer = false;
        $this->currentIndex++;
        
        if ($this->currentIndex >= $this->flashcards->count()) {
            $this->isComplete = true;
            return;
        }
        
        $this->currentCard = $this->flashcards[$this->currentIndex];
    }

    public function previousCard()
    {
        if ($this->currentIndex > 0) {
            $this->answer = false;
            $this->currentIndex--;
            $this->currentCard = $this->flashcards[$this->currentIndex];
        }
    }

    public function restart()
    {
        $this->currentIndex = 0;
        $this->answer = false;
        $this->isComplete = false;
        $this->currentCard = $this->flashcards->first();
        $this->resetStats();
    }

    public function resetStats()
    {
        $this->studiedCards = [];
        $this->correctCount = 0;
        $this->incorrectCount = 0;
    }

    public function getProgressPercentageProperty()
    {
        if ($this->flashcards->isEmpty()) {
            return 0;
        }
        
        return round(($this->currentIndex / $this->flashcards->count()) * 100);
    }

    public function getAccuracyPercentageProperty()
    {
        $total = $this->correctCount + $this->incorrectCount;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($this->correctCount / $total) * 100);
    }

    public function render()
    {
        return view('livewire.study-deck');
    }
}
