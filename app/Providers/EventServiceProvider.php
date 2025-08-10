<?php

namespace App\Providers;

use App\Events\DeckCreated;
use App\Listeners\LogDeckCreated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DeckCreated::class => [
            LogDeckCreated::class,
        ],
    ];
}
