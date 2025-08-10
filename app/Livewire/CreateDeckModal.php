<?php

namespace App\Livewire;

use App\Contracts\Service\DeckServiceInterface;
use App\Models\Deck;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class CreateDeckModal extends Component
{
    use AuthorizesRequests;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string|max:1000')]
    public string $description = '';

    #[Rule('boolean')]
    public bool $is_public = false;

    public bool $showModal = false;

    public function mount()
    {
        $this->authorize('create', Deck::class);
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'description', 'is_public']);
        $this->resetValidation();
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

            $this->closeModal();

            $this->dispatch('deck-created', deckId: $deck->id);
            $this->dispatch('refresh-decks');

            session()->flash('success', __('messages.deck.created', ['name' => $deck->name]));

            return $this->redirect(route('decks.show', $deck), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', __('messages.general.error'));
        }
    }

    public function render()
    {
        return view('livewire.create-deck-modal');
    }
}
