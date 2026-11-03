@extends('layouts.marketing')

@section('title', 'Log in — NOSHMARK')

@section('content')
    <form class="account-card" method="POST" action="{{ route('login') }}" style="max-width:460px;margin:24px auto;">
        @csrf
        <h2>Welcome back</h2>
        <p class="intro">Log in to order a recurring NOSHMARK menu.</p>

        <label class="field" for="email">Email Address</label>
        <input class="control" id="email" name="email" type="email" value="{{ old('email') }}" required>

        <label class="field" for="password">Password</label>
        <div class="input-icon">
            <input class="control" id="password" name="password" type="password" required>
            <button class="eye" type="button" data-eye aria-label="Show password">Show</button>
        </div>

        <label class="inline-check">
            <input type="checkbox" name="remember" value="1"> Remember me
        </label>

        <button class="btn btn-red btn-wide" type="submit">Log in</button>
        <p class="form-foot">New to NOSHMARK? <a href="{{ route('home') }}">Create an account</a></p>
        @if (app()->environment('local'))
            <p class="form-foot">Demo account: demo@noshmark.test / password</p>
        @endif
    </form>
@endsection
