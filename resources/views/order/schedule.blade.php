@extends('layouts.order')

@section('title', 'Subscription Schedule — NOSHMARK')

@section('content')
    @include('partials.stepper', ['current' => 2])
    <div class="page-head">
        <h1>Choose Your Subscription Schedule</h1>
        <p>Set how often you’d like to receive your curated NOSHMARK meals.</p>
    </div>

    @php
        $selected = [];
        foreach ($draft['items'] as $id => $qty) {
            $item = $menu->get((int) $id);
            if ($item) {
                $selected[] = $item->name.' × '.$qty;
            }
        }
    @endphp
    <p class="notice">Selected menu: {{ implode(', ', $selected) }} · Serving {{ $draft['serving_size'] }} persons</p>

    <form method="POST" action="{{ route('order.schedule.store') }}">
        @csrf
        <div class="schedule-grid">
            @foreach (config('noshmark.schedules') as $key => $plan)
                <label class="choice-card">
                    <input type="radio" name="schedule" value="{{ $key }}" @checked(old('schedule', $draft['schedule']) === $key) required>
                    <span class="check-badge">✓</span>
                    <img src="{{ asset($plan['image']) }}" alt="">
                    <div class="choice-body">
                        <h3>{{ $plan['label'] }}</h3>
                        <p class="muted">{{ $plan['description'] }}</p>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="schedule-layout">
            <div class="info-card">
                <label class="field" for="start_date">Start Date</label>
                <input class="control" id="start_date" type="date" name="start_date" min="{{ $minDate }}" value="{{ old('start_date', $draft['start_date']) }}" required>
                <label class="inline-check">
                    <input type="checkbox" name="menus_vary" value="1" @checked(old('menus_vary', $draft['menus_vary']))>
                    Menus may vary by delivery day
                </label>
                <p class="field-error">Orders must be placed at least {{ config('noshmark.lead_days') }} days before the first delivery.</p>
            </div>
            <div class="info-card">
                <strong>Your subscription will repeat automatically</strong>
                <p class="muted" style="margin-top:8px;">The schedule you choose starts from the selected date. On the next step you will assign a menu to every delivery day, and you can change a future day up to two days before it arrives.</p>
            </div>
        </div>

        <div class="wizard-actions">
            <a class="btn btn-ghost" href="{{ route('order.menu') }}">← Back</a>
            <button class="btn btn-red" type="submit">Continue →</button>
        </div>
    </form>
@endsection
