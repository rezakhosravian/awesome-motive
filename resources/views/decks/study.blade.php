<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Study Mode: {{ $deck->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <livewire:study-deck :deck="$deck" />
    </div>
</x-app-layout> 