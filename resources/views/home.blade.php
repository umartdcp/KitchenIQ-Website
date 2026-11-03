@extends('layouts.marketing')

@section('title', 'NOSHMARK — Fresh Pakistani Meals')
@section('body-class', 'home-body')

@section('content')
    <section class="hero">
        <div>
            <h1>Fresh Pakistani<br>Meals, <span class="scribble">Made Easy.</span></h1>
            <p class="lede">Authentic Pakistani flavors, fresh ingredients, and hassle-free dining — from our kitchen to your table.</p>
            <ul class="promises">
                <li>
                    <span class="promise-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 20s7-4.4 7-10a4 4 0 0 0-7-2 4 4 0 0 0-7 2c0 5.6 7 10 7 10z" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    </span>
                    Fresh Ingredients
                </li>
                <li>
                    <span class="promise-icon red" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 14h16v2a6 6 0 0 1-16 0v-2zM8 14V9a4 4 0 0 1 8 0v5" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    </span>
                    Authentic Pakistani Taste
                </li>
                <li>
                    <span class="promise-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="7" cy="17" r="2" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="17" cy="17" r="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M5 17h-.5A2.5 2.5 0 0 1 2 14.5V11h9l3-4h5l2 4v6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                    </span>
                    Convenient &amp; Reliable
                </li>
            </ul>
            <img class="hero-photo" src="{{ asset('images/menu/biryani.jpg') }}" alt="Chicken biryani served in a copper bowl">
        </div>

        @auth
            <aside class="account-card">
                <h2>Welcome back, {{ auth()->user()->name }}.</h2>
                <p class="intro">Your account is ready. Choose a menu, a serving size, and how often NOSHMARK should deliver.</p>
                <a class="btn btn-red btn-wide" href="{{ route('order.menu') }}">Start an order</a>
                <a class="btn btn-ghost btn-wide" href="{{ route('orders.index') }}" style="margin-top:10px;">View my orders</a>
            </aside>
        @else
            <form class="account-card" method="POST" action="{{ route('register') }}">
                @csrf
                <h2>Create Your Account</h2>
                <p class="intro">Join NOSHMARK and enjoy fresh Pakistani meals, exclusive plans, and more.</p>

                <label class="field" for="name">Full Name</label>
                <div class="input-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M6 19c1.2-2.5 3.2-3.8 6-3.8S16.8 16.5 18 19" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <input class="control" id="name" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required>
                </div>

                <label class="field" for="email">Email Address</label>
                <div class="input-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M4 7l8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    <input class="control" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email address" required>
                </div>

                <label class="field" for="phone">Phone Number</label>
                <div class="phone-row">
                    <div class="prefix">🇵🇰 +92</div>
                    <input id="phone" name="phone" inputmode="numeric" value="{{ old('phone') }}" placeholder="300 1234567" required>
                </div>

                <label class="field" for="password">Password</label>
                <div class="input-icon">
                    <svg viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V8a4 4 0 0 1 8 0v2" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    <input class="control" id="password" name="password" type="password" placeholder="Create a password" required>
                    <button class="eye" type="button" data-eye aria-label="Show password">Show</button>
                </div>

                <button class="btn btn-red btn-wide" type="submit">Create Account</button>
                <p class="form-foot">Already a customer? <a href="{{ route('login') }}">Log in</a></p>
            </form>
        @endauth
        <div class="home-blob" aria-hidden="true"></div>
    </section>
@endsection
