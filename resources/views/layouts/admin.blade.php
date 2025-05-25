<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    @include('admin.partials.sidebar')

    <div class="flex-1 overflow-auto">
        @include('admin.partials.header')

        <main class="p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
