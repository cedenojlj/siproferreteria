<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Header -->
    <header class="sticky-top shadow-sm">
        @yield('header')
    </header>

    <div class="d-flex flex-grow-1">      

        <!-- Main Content -->
        <main class="flex-grow-1 p-3">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    @yield('scripts')
</body>
</html>
