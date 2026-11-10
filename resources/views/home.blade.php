@extends('layouts.marketing')

@section('title', 'NOSHMARK — Fresh Pakistani Meals')
@section('body-class', 'home-body')
@section('main-class', 'home-main')

@section('content')
    <section class="hero">
        @include('partials.home-hero-copy')

        @auth
            <aside class="account-card">
                <h2>Welcome back, {{ auth()->user()->name }}.</h2>
                <p class="intro">Your account is ready. Choose a menu, a serving size, and how often NOSHMARK should deliver.</p>
                <a class="btn btn-red btn-wide" href="{{ route('order.menu') }}">Start an order</a>
                <a class="btn btn-ghost btn-wide" href="{{ route('orders.index') }}">View my orders</a>
            </aside>
        @else
            <form class="account-card" method="POST" action="{{ route('register') }}">
                @csrf
                <h2>Create Your Account</h2>
                <p class="intro">Join NOSHMARK and enjoy fresh Pakistani meals, exclusive plans, and more.</p>

                <label class="field" for="name">Full Name</label>
                <div class="input-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M5.5 19.2c1.3-3 3.6-4.4 6.5-4.4s5.2 1.4 6.5 4.4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <input class="control" id="name" name="name" value="{{ old('name') }}" placeholder="Enter your full name" autocomplete="name" required>
                </div>
                @error('name')<p class="field-error">{{ $message }}</p>@enderror

                <label class="field" for="email">Email Address</label>
                <div class="input-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M4 7l8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    <input class="control" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email address" autocomplete="email" required>
                </div>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror

                <label class="field" for="phone">Phone Number</label>
                <div class="phone-field">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 4.5h2.2l1 3.2-1.6 1.1a12 12 0 0 0 5.6 5.6l1.1-1.6 3.2 1V19a1.5 1.5 0 0 1-1.6 1.5A14.5 14.5 0 0 1 4.5 6.1 1.5 1.5 0 0 1 6 4.5H8z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                    <svg class="flag" viewBox="0 0 18 12" aria-hidden="true"><rect width="18" height="12" fill="#01411C"/><rect width="4.2" height="12" fill="#fff"/><circle cx="10.2" cy="6" r="2.5" fill="#fff"/><circle cx="11" cy="6" r="2" fill="#01411C"/><path fill="#fff" d="M13.1 6.1 11.7 6.5l.4-1.1.5.7z"/></svg>
                    <select aria-label="Country code">
                        <option value="+92">+92</option>
                    </select>
                    <span class="phone-split" aria-hidden="true"></span>
                    <input id="phone" name="phone" inputmode="numeric" value="{{ old('phone') }}" placeholder="300 1234567" autocomplete="tel" required>
                </div>
                @error('phone')<p class="field-error">{{ $message }}</p>@enderror

                <label class="field" for="password">Password</label>
                <div class="input-icon">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V8a4 4 0 0 1 8 0v2" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    <input class="control" id="password" name="password" type="password" placeholder="Create a password" autocomplete="new-password" required>
                    <button class="eye" type="button" data-eye aria-label="Show password">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12S6 6.5 12 6.5 21.5 12 21.5 12 18 17.5 12 17.5 2.5 12 2.5 12z" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.4" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                    </button>
                </div>
                @error('password')<p class="field-error">{{ $message }}</p>@enderror

                <button class="btn btn-red btn-wide" type="submit">Create Account</button>
                <p class="form-foot split"><span>Already a customer? <a href="{{ route('login') }}">Log In</a></span></p>
            </form>
        @endauth
    </section>
@endsection
