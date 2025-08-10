@props([
    'label',
    'current',
    'total',
    'unit' => '',
    'gradient' => 'from-indigo-500 to-purple-600'
])

@php
    $percentage = $total > 0 ? min(100, ($current / $total) * 100) : 0;
@endphp

<div>
    <div class="flex justify-between text-sm mb-2">
        <span class="text-gray-600">{{ $label }}</span>
        <span class="font-medium">{{ $current }}/{{ $total }}@if($unit) {{ $unit }}@endif</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2">
        <div class="bg-gradient-to-r {{ $gradient }} h-2 rounded-full transition-all duration-300" 
             style="width: {{ $percentage }}%"></div>
    </div>
</div> 