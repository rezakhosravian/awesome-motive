<div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $deck->name }}</h1>
                    @if($deck->description)
                        <p class="text-gray-600 text-sm sm:text-base">{{ $deck->description }}</p>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('decks.show', $deck) }}" 
                       wire:navigate
                       class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Deck
                    </a>
                </div>
            </div>
        </div>

        @if($isComplete)
            <!-- Study Complete -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 text-center">
                <div class="mb-6 sm:mb-8">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Study Session Complete!</h2>
                    <p class="text-gray-600">Great job studying your flashcards.</p>
                </div>

                @if($correctCount + $incorrectCount > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 sm:mb-8">
                        <div class="bg-green-50 p-4 rounded-xl border border-green-200">
                            <div class="text-2xl sm:text-3xl font-bold text-green-600">{{ $correctCount }}</div>
                            <div class="text-sm text-green-600 font-medium">Correct</div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-xl border border-red-200">
                            <div class="text-2xl sm:text-3xl font-bold text-red-600">{{ $incorrectCount }}</div>
                            <div class="text-sm text-red-600 font-medium">Incorrect</div>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                            <div class="text-2xl sm:text-3xl font-bold text-blue-600">{{ $this->accuracyPercentage }}%</div>
                            <div class="text-sm text-blue-600 font-medium">Accuracy</div>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button wire:click="restart" 
                            class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Study Again
                    </button>
                    <a href="{{ route('decks.show', $deck) }}" 
                       wire:navigate
                       class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Deck
                    </a>
                </div>
            </div>
        @elseif($flashcards->isEmpty())
            <!-- No Flashcards -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8 text-center">
                <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">No Flashcards Yet</h2>
                <p class="text-gray-600 mb-6">This deck doesn't have any flashcards to study.</p>
                <a href="{{ route('decks.show', $deck) }}" 
                   wire:navigate
                   class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Flashcards
                </a>
            </div>
        @else
            <!-- Study Interface -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Progress Bar -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0 mb-4">
                        <span class="text-sm font-medium text-gray-700">Study Progress</span>
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <span>Card {{ $currentIndex + 1 }} of {{ $flashcards->count() }}</span>
                            @if($correctCount + $incorrectCount > 0)
                                <span class="text-green-600 font-medium">{{ $correctCount }} correct</span>
                                <span class="text-red-600 font-medium">{{ $incorrectCount }} incorrect</span>
                            @endif
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ $this->progressPercentage }}%"></div>
                    </div>
                </div>

                <!-- Flashcard -->
                @if($currentCard)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 sm:p-8 min-h-[300px] sm:min-h-[400px] flex items-center justify-center">
                            <div class="text-center w-full">
                                @if(!$answer)
                                    <!-- Question Side -->
                                    <div class="space-y-6 sm:space-y-8">
                                        <div class="inline-flex items-center px-3 py-1 bg-indigo-100 text-indigo-700 text-sm font-medium rounded-full">
                                            Question
                                        </div>
                                        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 leading-relaxed px-4">
                                            {{ $currentCard->question }}
                                        </div>
                                        <button wire:click="showAnswer" 
                                                wire:loading.attr="disabled"
                                                wire:loading.class="opacity-50 cursor-not-allowed"
                                                class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg disabled:transform-none disabled:hover:scale-100">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span wire:loading.remove wire:target="showAnswer">Show Answer</span>
                                            <span wire:loading wire:target="showAnswer">Loading...</span>
                                        </button>
                                    </div>
                                @else
                                    <!-- Answer Side -->
                                    <div class="space-y-6 sm:space-y-8">
                                        <div class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 text-sm font-medium rounded-full">
                                            Answer
                                        </div>
                                        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 leading-relaxed px-4">
                                            {{ $currentCard->answer }}
                                        </div>
                                        <div class="space-y-4">
                                            <p class="text-gray-600 text-sm sm:text-base">How did you do?</p>
                                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                                <button wire:click="markIncorrect" 
                                                        class="inline-flex items-center justify-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Incorrect
                                                </button>
                                                <button wire:click="markCorrect" 
                                                        class="inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    Correct
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Navigation Controls -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between space-y-4 sm:space-y-0">
                        <!-- Left Controls -->
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="previousCard" 
                                    @disabled($currentIndex === 0)
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Previous
                            </button>
                            
                            @if($answer && $currentIndex < $flashcards->count() - 1)
                                <button wire:click="nextCard" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Next
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            @endif
                            
                            @if(!$shuffled)
                                <button wire:click="shuffleCards" 
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Shuffle
                                </button>
                            @endif
                        </div>

                        <!-- Right Controls -->
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="restart" 
                                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Restart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Study Tips (Mobile) -->
                <div class="bg-gradient-to-br from-orange-400 to-pink-500 rounded-xl p-4 sm:p-6 text-white sm:hidden">
                    <h3 class="text-lg font-semibold mb-2">Study Tip</h3>
                    <p class="text-orange-100 text-sm">Take your time to really understand each answer before marking it correct or incorrect.</p>
                </div>
            </div>
        @endif
    </div>
</div> 