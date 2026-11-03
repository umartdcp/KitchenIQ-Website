<footer class="site-footer">
    <div class="wrap footer-row">
        <div>
            @include('partials.brand')
            <p>Fresh Pakistani meals for the table, the office, and the gathering.</p>
        </div>
        <nav>
            <a href="{{ route('menu') }}">Menu</a>
            <a href="{{ route('plans') }}">Plans</a>
            <a href="{{ route('catering') }}">Catering</a>
            <a href="{{ route('about') }}">About</a>
        </nav>
    </div>
</footer>
