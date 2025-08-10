@props([
    'tips' => []
])

@php
    $defaultTips = [
        'Study in short, frequent sessions rather than long cramming sessions for better retention.',
        'Review flashcards at increasing intervals: 1 day, 3 days, 1 week, 2 weeks.',
        'Create your own flashcards rather than using pre-made ones for better understanding.',
        'Use the active recall method: try to remember the answer before flipping the card.',
        'Study difficult cards more frequently than easy ones.',
        'Mix up the order of your flashcards to avoid position-based memorization.',
        'Take breaks every 25-30 minutes to maintain focus and prevent fatigue.',
        'Connect new information to what you already know for stronger memory formation.',
    ];
    
    $allTips = !empty($tips) ? $tips : $defaultTips;
    $randomTip = $allTips[array_rand($allTips)];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Study Tip</h3>
    <div class="flex items-start space-x-3">
        <div class="p-2 bg-yellow-100 rounded-lg">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-600">{{ $randomTip }}</p>
        </div>
    </div>
</div> 