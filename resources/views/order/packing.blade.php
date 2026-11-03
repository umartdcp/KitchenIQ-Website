@extends('layouts.order')

@section('title', 'Choose Your Packing — NOSHMARK')

@section('content')
    @include('partials.stepper', ['current' => 3])
    <div class="page-head">
        <h1>Choose Your <span class="scribble">Packing</span></h1>
        <p>Selected menu stays with this order. The repeat is {{ config('noshmark.schedules.'.$draft['schedule'].'.label') }}, starting {{ \Illuminate\Support\Carbon::parse($draft['start_date'])->format('D, j M Y') }}.</p>
    </div>

    <form method="POST" action="{{ route('order.packing.store') }}" id="packing-form">
        @csrf
        <div class="packing-grid">
            <label class="choice-card">
                <input type="radio" name="packing" value="separate" @checked(old('packing', $draft['packing']) === 'separate')
                    data-dishes="{{ $separateBasket['total'] }}"
                    data-packing="{{ config('noshmark.separate_packing_per_person') * $separateServing }}"
                    data-persons="{{ $separateServing }}"
                    data-label="Separate packing">
                <span class="check-badge">✓</span>
                <img src="{{ asset(config('noshmark.packing.separate.image')) }}" alt="Meals packed separately">
                <div class="choice-body">
                    <div class="row-between">
                        <h3>Separate Packing</h3>
                        <div class="pack-price">Rs. {{ number_format(config('noshmark.separate_packing_per_person')) }}</div>
                    </div>
                    <p class="muted">Individually packed meals for each person</p>
                    <p class="pack-meta">Available · Ideal for events, offices &amp; individual serving</p>
                </div>
            </label>
            <label class="choice-card">
                <input type="radio" name="packing" value="pateela" @checked(old('packing', $draft['packing']) === 'pateela')
                    data-dishes="{{ $pateelaBasket['total'] }}"
                    data-packing="{{ config('noshmark.pateela_price') }}"
                    data-persons="30"
                    data-label="Whole pateela">
                <span class="check-badge">✓</span>
                <img src="{{ asset(config('noshmark.packing.pateela.image')) }}" alt="Biryani in a whole pateela">
                <div class="choice-body">
                    <div class="row-between">
                        <h3>Whole Pateela</h3>
                        <div class="pack-price">Rs. {{ number_format(config('noshmark.pateela_price')) }}</div>
                    </div>
                    <p class="muted">A large traditional pateela for group serving. (30 persons)</p>
                    <p class="pack-meta">Available · Serves 30 persons. Perfect for family &amp; large gatherings.</p>
                </div>
            </label>
        </div>

        <div class="notice">Price update: your total follows the packing you select. Whole pateela is only for 30 persons.</div>

        <div class="calc-card" id="packing-calc">
            <h3>Per delivery, before the delivery fee</h3>
            <p class="muted" id="packing-caption"></p>
            <div class="calc-total"><span>Dishes + packing</span><strong id="packing-total"></strong></div>
            <p class="muted">Delivery is PKR {{ number_format(config('noshmark.delivery_fee')) }} for each day you add next.</p>
        </div>

        <div class="wizard-actions">
            <a class="btn btn-ghost" href="{{ route('order.schedule') }}">← Back</a>
            <button class="btn btn-red" type="submit">Continue →</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const radios = document.querySelectorAll('input[name="packing"]');
            const caption = document.getElementById('packing-caption');
            const total = document.getElementById('packing-total');
            function paint() {
                const picked = document.querySelector('input[name="packing"]:checked');
                if (!picked) return;
                const dishes = Number(picked.dataset.dishes);
                const packing = Number(picked.dataset.packing);
                caption.textContent = picked.dataset.label + ' for ' + picked.dataset.persons + ' persons. Dish cost PKR ' + dishes.toLocaleString('en-PK') + ' + packing PKR ' + packing.toLocaleString('en-PK') + '.';
                total.textContent = 'PKR ' + (dishes + packing).toLocaleString('en-PK');
            }
            radios.forEach((radio) => radio.addEventListener('change', paint));
            paint();
        }());
    </script>
@endpush
