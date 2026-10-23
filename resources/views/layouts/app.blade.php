<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-900">
    @include('partials.navbar')

    <main class="mx-auto mt-8 max-w-4xl px-4">
        @yield('content')
    </main>

    @include('partials.scripts')
    <script src="/assets/js/lib/guard.js"></script>
    @stack('scripts')
</body>
</html>
