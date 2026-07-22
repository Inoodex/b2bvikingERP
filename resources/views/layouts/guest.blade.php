<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full bg-gray-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inventory Management System') }}</title>
        <link rel="icon" type="image/png" href="{{ asset(optional($settings)->site_logo ?: 'uploads/logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="min-h-full antialiased text-gray-900 bg-gray-50 relative">
        <!-- Animated Background Blobs -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
            <div class="absolute top-0 right-1/4 w-96 h-96 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-1/3 w-96 h-96 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        </div>

        <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8 relative z-10">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                {{-- <a href="/" class="flex justify-center mb-6">
                   <h1 class="text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-linear-to-r from-indigo-600 to-purple-600 drop-shadow-sm">
                       Inventory<span class="text-gray-800">System</span>
                   </h1>
                </a> --}}
                @if (isset($heading))
                    <div class="text-center">
                        {{ $heading }}
                    </div>
                @endif
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-[480px]">
                <div class="bg-white/80 backdrop-blur-lg px-6 py-12 shadow-2xl sm:rounded-2xl sm:px-12 border border-white/20 ring-1 ring-gray-900/5">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
