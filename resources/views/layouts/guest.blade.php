<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'FlashcardPro') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <!-- Logo and Brand -->
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/25">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3M19 19H5V5H19V19M17 17H7V15H17V17M15 13H7V11H15V13M13 9H7V7H13V9Z"/>
                        </svg>
                    </div>
                    <h2 class="mt-6 text-3xl font-bold text-gray-900">FlashcardPro</h2>
                    <p class="mt-2 text-sm text-gray-600">Master your studies with intelligent flashcards</p>
                </div>

                <!-- Auth Form -->
                <div class="bg-white/80 backdrop-blur-sm shadow-xl rounded-2xl p-8 border border-white/20">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                <div class="text-center">
                    <p class="text-xs text-gray-500">
                        &copy; {{ date('Y') }} FlashcardPro. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
