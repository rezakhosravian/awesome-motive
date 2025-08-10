<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>FlashcardPro - Master Your Studies with Intelligent Flashcards</title>
        <meta name="description" content="Accelerate your learning with FlashcardPro's intelligent flashcard system. Create, study, and master any subject with our powerful study tools.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white">
        <!-- Navigation -->
        <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3M19 19H5V5H19V19M17 17H7V15H17V17M15 13H7V11H15V13M13 9H7V7H13V9Z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900">FlashcardPro</span>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Features</a>
                        <a href="#how-it-works" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">How it Works</a>
                        <a href="#pricing" class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Pricing</a>
                        
                        @auth
                            <a href="{{ route('dashboard') }}" 
                               wire:navigate
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               wire:navigate
                               class="text-gray-600 hover:text-gray-900 font-medium transition-colors">Log in</a>
                            <a href="{{ route('register') }}" 
                               wire:navigate
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                Sign up
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden" x-data="{ open: false }">
                        <button @click="open = !open" class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path :class="{'hidden': ! open, 'inline-flex': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        
                        <!-- Mobile menu -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute top-16 right-4 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2">
                            <a href="#features" class="block px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50">Features</a>
                            <a href="#how-it-works" class="block px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50">How it Works</a>
                            <a href="#pricing" class="block px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50">Pricing</a>
                            <div class="border-t border-gray-200 my-2"></div>
                            @auth
                                <a href="{{ route('dashboard') }}" 
                                   wire:navigate
                                   class="block px-4 py-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" 
                                   wire:navigate
                                   class="block px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50">Log in</a>
                                <a href="{{ route('register') }}" 
                                   wire:navigate
                                   class="block px-4 py-2 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50">Sign up</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-20 pb-16 bg-gradient-to-br from-indigo-50 via-white to-cyan-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 leading-tight">
                        Master Your Studies with
                        <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Intelligent Flashcards
                        </span>
                    </h1>
                    <p class="mt-6 text-xl text-gray-600 max-w-3xl mx-auto">
                        Accelerate your learning with FlashcardPro's powerful study system. Create, organize, and study flashcards with scientifically-proven techniques.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                        @auth
                            <a href="{{ route('dashboard') }}" 
                               wire:navigate
                               class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg">
                                Go to Dashboard
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" 
                               wire:navigate
                               class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg">
                                Start Learning Free
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                            <a href="#demo" 
                               class="inline-flex items-center px-8 py-4 border border-gray-300 hover:border-gray-400 text-gray-700 font-semibold rounded-xl transition-colors">
                                <svg class="mr-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-5-8V3a1 1 0 011-1h1a1 1 0 011 1v3M9 21v-9a1 1 0 011-1h4a1 1 0 011 1v9"></path>
                                </svg>
                                Watch Demo
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Hero Visual -->
                <div class="mt-16 relative">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-4xl mx-auto border">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Sample Flashcard</h3>
                                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-6 text-white min-h-[200px] flex items-center justify-center">
                                    <div class="text-center">
                                        <h4 class="text-xl font-semibold mb-3">What is the capital of France?</h4>
                                        <button class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg font-medium transition-colors">
                                            Show Answer
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Study Progress</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span>Cards Mastered</span>
                                        <span class="font-semibold">24/30</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3">
                                        <div class="bg-gradient-to-r from-green-400 to-blue-500 h-3 rounded-full" style="width: 80%"></div>
                                    </div>
                                    <div class="grid grid-cols-3 gap-4 pt-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-green-600">95%</div>
                                            <div class="text-sm text-gray-600">Accuracy</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-blue-600">12</div>
                                            <div class="text-sm text-gray-600">Streak</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-purple-600">45m</div>
                                            <div class="text-sm text-gray-600">Today</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                                </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4">
                        Everything you need to succeed
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Powerful features designed to make your study sessions more effective and enjoyable
                                    </p>
                                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="group bg-white rounded-xl p-8 border border-gray-100 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Smart Study Algorithm</h3>
                        <p class="text-gray-600">Our intelligent system adapts to your learning pace and focuses on cards you need to review most.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group bg-white rounded-xl p-8 border border-gray-100 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Progress Tracking</h3>
                        <p class="text-gray-600">Detailed analytics help you understand your learning patterns and identify areas for improvement.</p>
                                </div>

                    <!-- Feature 3 -->
                    <div class="group bg-white rounded-xl p-8 border border-gray-100 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-pink-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Share & Collaborate</h3>
                        <p class="text-gray-600">Create public decks to share with classmates or access thousands of community-created study sets.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="group bg-white rounded-xl p-8 border border-gray-100 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Mobile Optimized</h3>
                        <p class="text-gray-600">Study anywhere, anytime with our responsive design that works perfectly on all devices.</p>
                                </div>

                    <!-- Feature 5 -->
                    <div class="group bg-white rounded-xl p-8 border border-gray-100 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M15 9h.01"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Rich Content</h3>
                        <p class="text-gray-600">Add images, formatting, and multimedia to create engaging and memorable flashcards.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="group bg-white rounded-xl p-8 border border-gray-100 hover:shadow-lg transition-all">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Privacy First</h3>
                        <p class="text-gray-600">Your data is secure with us. Choose to keep your decks private or share them with the community.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-br from-indigo-600 to-purple-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-4xl font-bold text-white mb-6">
                    Ready to transform your studying?
                </h2>
                <p class="text-xl text-indigo-100 mb-8">
                    Join thousands of students who are already learning smarter with FlashcardPro
                </p>
                @auth
                    <a href="{{ route('dashboard') }}" 
                       wire:navigate
                       class="inline-flex items-center px-8 py-4 bg-white hover:bg-gray-50 text-indigo-600 font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg">
                        Continue Learning
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" 
                       wire:navigate
                       class="inline-flex items-center px-8 py-4 bg-white hover:bg-gray-50 text-indigo-600 font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg">
                        Get Started Free
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @endauth
                                </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3M19 19H5V5H19V19M17 17H7V15H17V17M15 13H7V11H15V13M13 9H7V7H13V9Z"/>
                                </svg>
                            </div>
                            <span class="text-xl font-bold">FlashcardPro</span>
                        </div>
                        <p class="text-gray-400 mb-4 max-w-md">
                            Empowering students worldwide to achieve their learning goals through intelligent flashcard technology.
                        </p>
                        <p class="text-sm text-gray-500">
                            &copy; {{ date('Y') }} FlashcardPro. All rights reserved.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-semibold mb-4">Product</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                            <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">API</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-semibold mb-4">Support</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white transition-colors">Help Center</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
