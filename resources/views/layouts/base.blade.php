<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NOSHMARK')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/noshmark.css') }}">
</head>
<body class="@yield('body-class', 'app-body')">
    @yield('chrome')
    <main class="@yield('main-class', 'wrap page')">
        @include('partials.flash')
        @yield('content')
    </main>
    @yield('footer')
    <script src="{{ asset('js/site.js') }}"></script>
    @stack('scripts')
</body>
</html>
