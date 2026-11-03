@extends('layouts.order')

@section('title', 'Delivery Dates & Menu — NOSHMARK')

@section('content')
    @include('partials.stepper', ['current' => 4])
    <div class="page-head">
        <h1>Select Delivery Dates &amp; <span class="scribble">Daily Menu</span></h1>
        <div class="center-banner">
            <div class="lead-banner">Order at least 2 days in advance.</div>
        </div>
    </div>

    <form method="POST" action="{{ route('order.delivery.store') }}" id="delivery-form">
        @csrf
        <input type="hidden" name="delivery_payload" id="delivery-payload" value="{{ json_encode(['deliveries' => $draft['deliveries']]) }}">

        <div class="delivery-layout">
            <div>
                <section class="cal-card">
                    <div class="panel-title"><strong>Select Delivery Dates</strong></div>
                    <div id="calendar-root"></div>
                </section>
                <div class="control-stack">
                    <div class="control-card">
                        <label for="days-per-week">Delivery days per week
                            <select class="control" id="days-per-week" name="days_per_week">
                                @for ($days = 1; $days <= 7; $days++)
                                    <option value="{{ $days }}" @selected((int) old('days_per_week', $draft['days_per_week']) === $days)>{{ $days }} {{ $days === 1 ? 'day' : 'days' }} per week</option>
                                @endfor
                            </select>
                        </label>
                        <button class="text-btn" id="suggest-days" type="button">Apply this many days from the earliest date</button>
                    </div>
                    <div class="control-card">
                        <label for="per-person">Per-person schedule
                            <select class="control" id="per-person" name="per_person_schedule" @disabled($draft['packing'] === 'pateela')>
                                <option value="same" @selected(old('per_person_schedule', $draft['per_person_schedule']) === 'same')>Same menu for everyone</option>
                                @if ($draft['packing'] !== 'pateela')
                                    <option value="personalized" @selected(old('per_person_schedule', $draft['per_person_schedule']) === 'personalized')>Personalized menu per person</option>
                                @endif
                            </select>
                        </label>
                        @if ($draft['packing'] === 'pateela')
                            <input type="hidden" name="per_person_schedule" value="same">
                            <p class="muted">A whole pateela is one dish for all 30 persons.</p>
                        @else
                            <p class="muted">Same menu multiplies the dish by the serving size. Personalized splits the persons across dishes.</p>
                        @endif
                    </div>
                    <div class="control-card">
                        <label for="address">Delivery address
                            <textarea class="control" id="address" name="address" required placeholder="House, street, area, city">{{ old('address', $draft['address']) }}</textarea>
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <section class="days-panel">
                    <div class="panel-title"><strong>Your Delivery Schedule &amp; Daily Menu</strong></div>
                    <div id="days-root" style="margin-top:12px;"></div>
                    <button class="btn btn-ghost" id="add-day" type="button" style="width:100%;">+ Add Another Delivery Day</button>
                </section>
                <div class="calc-card" id="calc-root"></div>
            </div>
        </div>

        <div class="wizard-actions">
            <a class="btn btn-ghost" href="{{ route('order.packing') }}">← Back</a>
            <button class="btn btn-red" type="submit">Continue →</button>
        </div>
    </form>

    <script id="delivery-state" type="application/json">
        {!! json_encode([
            'minDate' => $minDate,
            'serving' => (int) $draft['serving_size'],
            'packing' => $draft['packing'],
            'menusVary' => (bool) $draft['menus_vary'],
            'mode' => $draft['per_person_schedule'],
            'fees' => [
                'separatePerPerson' => (int) config('noshmark.separate_packing_per_person'),
                'pateela' => (int) config('noshmark.pateela_price'),
                'delivery' => (int) config('noshmark.delivery_fee'),
            ],
            'menu' => $items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (int) $item->price_per_person,
                'image' => asset($item->image),
            ])->values(),
            'deliveries' => $draft['deliveries'],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('js/delivery.js') }}"></script>
@endpush
