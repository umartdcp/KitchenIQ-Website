@extends('layouts.marketing')

@section('title', 'Log in — NOSHMARK')
@section('body-class', 'home-body')
@section('main-class', 'home-main')

@section('content')
    <section class="hero">
        @include('partials.home-hero-copy')

        <form class="account-card" method="POST" action="{{ route('login') }}">
            @csrf
            <h2>Log in</h2>
            <p class="intro">Welcome back. Log in to order fresh Pakistani meals on your schedule.</p>

            <label class="field" for="email">Email Address</label>
            <div class="input-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M4 7l8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                <input class="control" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email address" autocomplete="email" required>
            </div>
            @error('email')<p class="field-error">{{ $message }}</p>@enderror

            <label class="field" for="password">Password</label>
            <div class="input-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V8a4 4 0 0 1 8 0v2" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                <input class="control" id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
                <button class="eye" type="button" data-eye aria-label="Show password">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12S6 6.5 12 6.5 21.5 12 21.5 12 18 17.5 12 17.5 2.5 12 2.5 12z" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.4" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                </button>
            </div>

            <label class="inline-check">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>

            <button class="btn btn-red btn-wide" type="submit">Log In</button>
            <p class="form-foot split"><span>New to NOSHMARK? <a href="{{ route('home') }}">Create Account</a></span></p>
            @if (app()->environment('local'))
                <p class="form-foot">Demo account: demo@noshmark.test / password</p>
            @endif
        </form>
    </section>
@endsection
