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
    @stack('head')
</head>
<body class="bg-gray-50 text-gray-900">
    <main class="mx-auto mt-16 max-w-md px-4">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
