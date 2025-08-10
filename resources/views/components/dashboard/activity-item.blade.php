@props([
    'title',
    'time',
    'details' => null,
    'icon',
    'color' => 'blue'
])

@php
    $colorClasses = [
        'blue' => 'bg-blue-100 text-blue-600',
        'green' => 'bg-green-100 text-green-600',
        'yellow' => 'bg-yellow-100 text-yellow-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'red' => 'bg-red-100 text-red-600',
    ];
    
    $colorClass = $colorClasses[$color] ?? $colorClasses['blue'];
    
    $icons = [
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>',
        'study' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>',
    ];
    
    $iconSvg = $icons[$icon] ?? $icons['plus'];
@endphp

<div class="flex items-center space-x-3 sm:space-x-4 p-3 bg-gray-50 rounded-lg">
    <div class="w-8 h-8 sm:w-10 sm:h-10 {{ $colorClass }} rounded-full flex items-center justify-center">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $iconSvg !!}
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 truncate">{{ $title }}</p>
        <p class="text-xs text-gray-500">
            {{ $time }}@if($details) • {{ $details }}@endif
        </p>
    </div>
</div> 