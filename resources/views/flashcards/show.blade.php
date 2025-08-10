<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Flashcard Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('decks.show', $deck) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Back to Deck') }}
                </a>
                <a href="{{ route('decks.flashcards.edit', [$deck, $flashcard]) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Edit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Deck Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">{{ __('Deck') }}: {{ $deck->name }}</h3>
                    @if($deck->description)
                        <p class="text-gray-600">{{ $deck->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Flashcard Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Question -->
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h4 class="text-lg font-semibold text-blue-800 mb-3">{{ __('Question') }}</h4>
                            <div class="text-gray-800 whitespace-pre-wrap">{{ $flashcard->question }}</div>
                        </div>

                        <!-- Answer -->
                        <div class="bg-green-50 p-6 rounded-lg">
                            <h4 class="text-lg font-semibold text-green-800 mb-3">{{ __('Answer') }}</h4>
                            <div class="text-gray-800 whitespace-pre-wrap">{{ $flashcard->answer }}</div>
                        </div>
                    </div>

                    <!-- Flashcard Meta Information -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <strong>{{ __('Created') }}:</strong> 
                                {{ $flashcard->created_at->format('M j, Y \a\t g:i A') }}
                            </div>
                            <div>
                                <strong>{{ __('Last Updated') }}:</strong> 
                                {{ $flashcard->updated_at->format('M j, Y \a\t g:i A') }}
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-200 flex flex-wrap gap-3">
                        <a href="{{ route('decks.flashcards.edit', [$deck, $flashcard]) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Edit Flashcard') }}
                        </a>
                        
                        <form action="{{ route('decks.flashcards.destroy', [$deck, $flashcard]) }}" 
                              method="POST" 
                              class="inline"
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this flashcard?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Delete Flashcard') }}
                            </button>
                        </form>

                        <a href="{{ route('decks.study', $deck) }}" 
                           class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Study This Deck') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>