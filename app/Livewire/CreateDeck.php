<?php

namespace App\Livewire;

use App\Contracts\Service\DeckServiceInterface;
use App\Models\Deck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class CreateDeck extends Component
{
    use AuthorizesRequests;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string|max:1000')]
    public string $description = '';

    #[Rule('boolean')]
    public bool $is_public = false;

    public bool $showSuccess = false;
    public string $successMessage = '';

    public bool $skipRedirect = false;

    public function mount()
    {
        $this->authorize('create', Deck::class);
    }

    public function save()
    {
        $this->validate();

        try {
            $deckService = app(DeckServiceInterface::class);
            $deck = $deckService->createDeckFromArray(Auth::user(), [
                'name' => $this->name,
                'description' => $this->description,
                'is_public' => $this->is_public,
            ]);

            $this->showSuccess = true;
            $this->successMessage = __('messages.deck.created');

            $this->reset(['name', 'description', 'is_public']);

            $this->dispatch('deck-created', deckId: $deck->id);

            if (!$this->skipRedirect) {
                return $this->redirect(route('decks.show', $deck), navigate: true);
            }
        } catch (\Exception $e) {
            session()->flash('error', __('messages.general.error'));
        }
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'is_public']);
        $this->resetValidation();
        $this->showSuccess = false;
    }

    public function render()
    {
        return view('livewire.create-deck');
    }
}
