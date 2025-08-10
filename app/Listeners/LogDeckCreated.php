<?php

namespace App\Listeners;

use App\Events\DeckCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogDeckCreated implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(DeckCreated $event): void
    {
        Log::info('Deck created', [
            'deck_id' => $event->deck->id,
            'user_id' => $event->deck->user_id,
            'name' => $event->deck->name,
        ]);
    }
}
