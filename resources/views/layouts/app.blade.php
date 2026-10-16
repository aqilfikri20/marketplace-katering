<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ketring Yuk — makan siang beres')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;1,500;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('home') }}"><span class="brand-mark">KY</span><span>Ketring Yuk</span></a>
    <nav class="main-nav"><a href="{{ route('home') }}">Jelajahi</a><a href="{{ route('home') }}#cara-kerja">Cara kerja</a></nav>
    <div class="top-actions">
        @auth
            <a class="nav-dashboard" href="{{ route('dashboard') }}">Dashboard</a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="button button-light button-sm">Keluar</button></form>
        @else
            <a class="nav-login" href="{{ route('login') }}">Masuk</a>
            <a class="button button-primary button-sm" href="{{ route('register') }}">Mulai sekarang <span>↗</span></a>
        @endauth
    </div>
    <button class="mobile-menu" aria-label="Buka menu" onclick="document.body.classList.toggle('menu-open')">☰</button>
</header>
<main>
    @if(session('success'))
        <div class="flash-success">✓ {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="flash-error">{{ $errors->first() }}</div>
    @endif
    @auth
        @include('dashboard.profile-modal')
    @endauth
    @yield('content')
</main>
<footer class="footer"><div class="footer-inner"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">KY</span><span>Ketring Yuk</span></a><span>Makan siang yang bikin kerja lebih baik.</span><span>© {{ date('Y') }} Ketring Yuk</span></div></footer>
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/marketplace.js') }}?v={{ filemtime(public_path('js/marketplace.js')) }}" defer></script>
<script src="{{ asset('js/scroll-behavior.js') }}?v={{ filemtime(public_path('js/scroll-behavior.js')) }}" defer></script>

@stack('scripts')
</body>
</html>
