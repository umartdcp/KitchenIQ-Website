@extends('layouts.marketing')

@section('title', 'Plans — NOSHMARK')

@section('content')
    <div class="page-head">
        <h1>Choose how often you eat</h1>
        <p>After your account is created, pick dishes and a serving size of 2, 4, 8, or 12 persons, then set the repeat.</p>
    </div>
    <div class="schedule-grid">
        @foreach (config('noshmark.schedules') as $plan)
            <article class="plain-card">
                <img src="{{ asset($plan['image']) }}" alt="">
                <h2>{{ $plan['label'] }}</h2>
                <p class="muted">{{ $plan['description'] }}</p>
                <p style="margin-top:8px;font-weight:700;">{{ $plan['summary'] }}</p>
            </article>
        @endforeach
    </div>
    <p style="text-align:center;margin-top:22px;">
        <a class="btn btn-red" href="{{ auth()->check() ? route('order.menu') : route('home') }}">Start your plan</a>
    </p>
@endsection
