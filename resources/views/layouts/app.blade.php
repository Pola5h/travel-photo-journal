<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <style>
        [x-cloak] { display: none !important; }
        body { margin: 0; background: #f9fafb; color: #111827; }
    </style>
    @include('partials.scripts')
    <script src="/assets/js/lib/guard.js"></script>
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-900">
    @include('partials.navbar')

    <main class="mx-auto mt-8 max-w-4xl px-4">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
