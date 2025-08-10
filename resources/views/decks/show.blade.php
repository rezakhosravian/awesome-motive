<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $deck->name }}
            </h2>
            <div class="flex space-x-2">
                @can('update', $deck)
                    <a href="{{ route('decks.edit', $deck) }}" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Edit Deck
                    </a>
                    <a href="{{ route('decks.flashcards.create', $deck) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Add Flashcard
                    </a>
                @endcan
                @if($deck->flashcards->count() > 0)
                    <a href="{{ route('decks.study', $deck) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Study Deck
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Deck Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $deck->name }}</h3>
                            @if($deck->description)
                                <p class="text-gray-600 mb-4">{{ $deck->description }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($deck->is_public)
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mb-2 inline-block">Public</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full mb-2 inline-block">Private</span>
                            @endif
                            <div class="text-sm text-gray-500">
                                Created by {{ $deck->user->name }} {{ $deck->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            {{ $deck->flashcards->count() }} flashcard{{ $deck->flashcards->count() !== 1 ? 's' : '' }}
                        </div>
                        @can('delete', $deck)
                            <form method="POST" action="{{ route('decks.destroy', $deck) }}" 
                                  onsubmit="return confirm('Are you sure you want to delete this deck? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                    Delete Deck
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Flashcards -->
            @if($deck->flashcards->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No flashcards yet</h3>
                    <p class="text-gray-600 mb-4">Add your first flashcard to start studying!</p>
                    @can('update', $deck)
                        <a href="{{ route('decks.flashcards.create', $deck) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors inline-block">
                            Add First Flashcard
                        </a>
                    @endcan
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Flashcards</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($deck->flashcards as $flashcard)
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="mb-3">
                                        <div class="text-sm font-medium text-gray-700 mb-1">Question:</div>
                                        <div class="text-gray-900">{{ Str::limit($flashcard->question, 100) }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-sm font-medium text-gray-700 mb-1">Answer:</div>
                                        <div class="text-gray-900">{{ Str::limit($flashcard->answer, 100) }}</div>
                                    </div>
                                    @can('update', $deck)
                                        <div class="flex justify-end space-x-2">
                                            <a href="{{ route('decks.flashcards.edit', [$deck, $flashcard]) }}" 
                                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('decks.flashcards.destroy', [$deck, $flashcard]) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this flashcard?')" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout> 