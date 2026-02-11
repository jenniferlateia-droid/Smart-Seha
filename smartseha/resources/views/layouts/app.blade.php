<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Smart Seha' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php($isAuthPage = request()->routeIs('login') || request()->routeIs('register'))
@php($isLandingPage = request()->routeIs('landing'))
<body class="{{ $isAuthPage ? 'auth-body' : 'app-body' }}">
    @auth
        @include('partials.navbar')
    @endauth

    <main class="{{ $isAuthPage ? 'auth-main' : ($isLandingPage ? 'w-full p-0' : 'app-main') }}">
        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                <ul class="space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
