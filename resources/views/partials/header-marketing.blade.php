<header class="site-header">
    <div class="wrap header-row">
        @include('partials.brand')
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open menu">
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" data-nav>
            <a href="{{ route('menu') }}" @class(['is-active' => request()->routeIs('menu')])>Menu</a>
            <a href="{{ route('plans') }}" @class(['is-active' => request()->routeIs('plans')])>Plans</a>
            <a href="{{ route('how') }}" @class(['is-active' => request()->routeIs('how')])>How It Works</a>
        </nav>
        <div class="header-actions">
            @auth
                <a class="text-link" href="{{ route('orders.index') }}">My Orders</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline" type="submit">Logout</button>
                </form>
            @else
                <a class="btn btn-outline" href="{{ route('login') }}">Login</a>
            @endauth
        </div>
    </div>
</header>
