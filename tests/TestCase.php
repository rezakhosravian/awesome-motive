<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Livewire\Volt\Volt;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Register Volt test helpers
        Volt::mount([
            config('livewire.view_path', resource_path('views/livewire')),
            resource_path('views/pages'),
        ]);

        // Register the assertSeeVolt macro
        TestResponse::macro('assertSeeVolt', function (string $component) {
            $componentPath = str_replace('.', '/', $component);
            $this->assertSee('wire:snapshot', false);
            return $this;
        });

        TestResponse::macro('assertSeeLivewire', function (string $component) {
            return $this->assertSeeVolt($component);
        });
    }
}
