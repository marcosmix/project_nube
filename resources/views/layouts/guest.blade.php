<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
         <link rel="icon" type="image/png" href="{{ asset('favicon-ligth.png') }}" media="(prefers-color-scheme: light)">
         <link rel="icon" type="image/png" href="{{ asset('favicon-black.png') }}" media="(prefers-color-scheme: dark)">
         <link rel="alternate icon" type="image/png" href="{{ asset('favicon-ligth.png') }}">

        <title>{{ config('app.name', 'NubeERP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <img src="{{ asset('favicon-ligth.png') }}" alt="NubeERP" class="w-20 h-20">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
