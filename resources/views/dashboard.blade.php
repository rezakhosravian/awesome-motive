<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                    Welcome back, {{ auth()->user()->name }}!
                </h2>
                <p class="text-gray-600 mt-1 text-sm sm:text-base">Ready to continue your learning journey?</p>
            </div>
            
            @if($stats['daily_goal'] > 0)
                <div class="hidden lg:flex items-center space-x-3">
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Today's Goal</div>
                        <div class="text-lg font-semibold text-indigo-600">
                            {{ $stats['cards_studied_today'] }}/{{ $stats['daily_goal'] }} cards
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-4 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
                <x-dashboard.stat-card 
                    title="Total Decks"
                    :value="$stats['total_decks']"
                    icon-color="indigo">
                    <x-slot name="icon">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                <x-dashboard.stat-card 
                    title="Total Cards"
                    :value="$stats['total_flashcards']"
                    icon-color="green">
                    <x-slot name="icon">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </x-slot>
                </x-dashboard.stat-card>

                @if($stats['study_streak'] > 0)
                    <x-dashboard.stat-card 
                        title="Study Streak"
                        :value="$stats['study_streak']"
                        suffix=" days"
                        icon-color="yellow">
                        <x-slot name="icon">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </x-slot>
                    </x-dashboard.stat-card>
                @endif

                @if($stats['accuracy'] > 0)
                    <x-dashboard.stat-card 
                        title="Accuracy"
                        :value="$stats['accuracy']"
                        suffix="%"
                        icon-color="purple">
                        <x-slot name="icon">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </x-slot>
                    </x-dashboard.stat-card>
                @endif
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Quick Actions -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Quick Actions</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Create New Deck -->
                            <a href="{{ route('decks.create') }}" 
                               wire:navigate
                               class="group relative bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-4 sm:p-6 text-white hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div class="mb-3 sm:mb-0">
                                        <h4 class="text-base sm:text-lg font-semibold mb-2">Create New Deck</h4>
                                        <p class="text-indigo-100 text-sm">Start building your next study set</p>
                                    </div>
                                    <div class="p-2 sm:p-3 bg-white/20 rounded-lg w-fit">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>

                            <!-- Browse Decks -->
                            <a href="{{ route('decks.index') }}" 
                               wire:navigate
                               class="group relative bg-gradient-to-br from-green-500 to-teal-600 rounded-xl p-4 sm:p-6 text-white hover:from-green-600 hover:to-teal-700 transition-all duration-200 transform hover:scale-105">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                    <div class="mb-3 sm:mb-0">
                                        <h4 class="text-base sm:text-lg font-semibold mb-2">Browse My Decks</h4>
                                        <p class="text-green-100 text-sm">Review and study your collections</p>
                                    </div>
                                    <div class="p-2 sm:p-3 bg-white/20 rounded-lg w-fit">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    @if(!empty($stats['recent_activities']))
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4 sm:mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                                <a href="{{ route('decks.index') }}" 
                                   wire:navigate
                                   class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    View all
                                </a>
                            </div>
                            <div class="space-y-3 sm:space-y-4">
                                @foreach($stats['recent_activities'] as $activity)
                                    <x-dashboard.activity-item 
                                        :title="$activity['title']"
                                        :time="$activity['time']"
                                        :details="$activity['details'] ?? null"
                                        :icon="$activity['icon']"
                                        :color="$activity['color']" />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Today's Progress -->
                    @if($stats['daily_goal'] > 0)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-6">Today's Progress</h3>
                            <div class="space-y-4">
                                <x-dashboard.progress-bar 
                                    label="Cards Studied"
                                    :current="$stats['cards_studied_today']"
                                    :total="$stats['daily_goal']"
                                    gradient="from-indigo-500 to-purple-600" />
                                
                                <x-dashboard.progress-bar 
                                    label="Study Time"
                                    :current="$stats['study_time_today']"
                                    :total="$stats['study_time_goal']"
                                    unit="min"
                                    gradient="from-green-500 to-teal-600" />
                            </div>
                        </div>
                    @endif

                    <!-- Quick Study -->
                    <x-dashboard.quick-study :recent-deck="$stats['recent_deck']" />

                    <!-- Study Tips -->
                    <x-dashboard.study-tip />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
