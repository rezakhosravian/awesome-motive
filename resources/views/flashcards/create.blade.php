<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Flashcard to "{{ $deck->name }}"
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('decks.flashcards.store', $deck) }}">
                        @csrf

                        <!-- Question -->
                        <div class="mb-4">
                            <label for="question" class="block text-sm font-medium text-gray-700 mb-2">
                                Question *
                            </label>
                            <textarea id="question" 
                                      name="question" 
                                      rows="3" 
                                      required
                                      placeholder="Enter the question here..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('question') }}</textarea>
                            @error('question')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Answer -->
                        <div class="mb-6">
                            <label for="answer" class="block text-sm font-medium text-gray-700 mb-2">
                                Answer *
                            </label>
                            <textarea id="answer" 
                                      name="answer" 
                                      rows="3" 
                                      required
                                      placeholder="Enter the answer here..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('answer') }}</textarea>
                            @error('answer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('decks.show', $deck) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-medium transition-colors">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                Add Flashcard
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 