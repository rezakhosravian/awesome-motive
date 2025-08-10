<?php

namespace App\Events;

use App\Models\Deck;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeckCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Deck $deck) {}
}
