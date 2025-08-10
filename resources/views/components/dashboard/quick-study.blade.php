@props([
    'recentDeck' => null
])

@if($recentDeck)
    <div class="bg-gradient-to-br from-orange-400 to-pink-500 rounded-xl p-4 sm:p-6 text-white">
        <h3 class="text-lg font-semibold mb-3">Quick Study</h3>
        <p class="text-orange-100 text-sm mb-4">Continue with "{{ $recentDeck->name }}"</p>
        <a href="{{ route('decks.study', $recentDeck) }}" 
           wire:navigate
           class="w-full bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-4 rounded-lg transition-colors inline-block text-center">
            Start Studying
        </a>
    </div>
@endif 