@php
    $cartCount = collect(session('noshmark.draft.items', []))->filter(fn ($qty) => (int) $qty > 0)->count();
@endphp
<header class="site-header order-header">
    <div class="wrap header-row">
        @include('partials.brand')
        <p class="header-tagline">Premium Pakistani Dining<br>for Every Occasion</p>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open menu">
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" data-nav>
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('menu') }}">Menus</a>
            <a href="{{ route('catering') }}">Catering</a>
            <a href="{{ route('about') }}">About NOSHMARK</a>
        </nav>
        <div class="icon-actions">
            <a class="icon-btn" href="{{ route('order.menu') }}" aria-label="Search the menu">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M16 16l4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </a>
            <a class="icon-btn" href="{{ route('orders.index') }}" aria-label="Your orders">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M5 19.2c1.4-3 3.8-4.5 7-4.5s5.6 1.5 7 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </a>
            <a class="icon-btn cart-btn" href="{{ route('order.menu') }}" aria-label="Current order">
                <svg viewBox="0 0 24 24"><path d="M6 8h12l-1 11H7L6 8z" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M9 8V7a3 3 0 0 1 6 0v1" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
                @if ($cartCount > 0)
                    <span class="cart-badge">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>
</header>
