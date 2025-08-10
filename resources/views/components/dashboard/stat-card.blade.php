@props([
    'title',
    'value', 
    'icon',
    'iconColor' => 'indigo',
    'suffix' => '',
    'gradient' => null
])

@php
    $iconColors = [
        'indigo' => 'bg-indigo-100 text-indigo-600',
        'green' => 'bg-green-100 text-green-600',
        'yellow' => 'bg-yellow-100 text-yellow-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'blue' => 'bg-blue-100 text-blue-600',
        'red' => 'bg-red-100 text-red-600',
    ];
    
    $colorClass = $iconColors[$iconColor] ?? $iconColors['indigo'];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 hover:shadow-md transition-shadow">
    <div class="flex flex-col sm:flex-row sm:items-center">
        <div class="p-2 sm:p-3 {{ $colorClass }} rounded-lg mb-3 sm:mb-0 w-fit">
            {!! $icon !!}
        </div>
        <div class="sm:ml-4">
            <p class="text-xs sm:text-sm font-medium text-gray-500">{{ $title }}</p>
            <p class="text-lg sm:text-2xl font-bold text-gray-900">
                {{ $value }}@if($suffix)<span class="text-sm sm:text-base">{{ $suffix }}</span>@endif
            </p>
        </div>
    </div>
</div> 