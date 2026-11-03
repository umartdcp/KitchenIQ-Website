@extends('layouts.order')

@section('title', 'Review & Pay — NOSHMARK')

@section('content')
    @include('partials.stepper', ['current' => 5])
    <div class="page-head" style="text-align:left;margin-left:0;">
        <h1>Review Your Order &amp; Pay</h1>
        <p>Please review your subscription, selected menu, packing, and total below, then pay in full to confirm.</p>
    </div>

    <div class="review-layout">
        <div>
            <section class="summary-card">
                <div class="summary-head">
                    <h2>Your Subscription Details</h2>
                    <a class="edit-link" href="{{ route('order.schedule') }}">Edit Plan</a>
                </div>
                <div class="detail-grid">
                    <div class="detail-tile">
                        <span>Subscription Plan</span>
                        <strong>{{ $quote['schedule_label'] }}</strong>
                        <small class="muted">{{ $quote['days_per_week'] }} {{ $quote['days_per_week'] === 1 ? 'day' : 'days' }} / week</small>
                    </div>
                    <div class="detail-tile">
                        <span>Serving Size</span>
                        <strong>{{ $quote['serving_size'] }} {{ $quote['serving_size'] === 1 ? 'Person' : 'Persons' }}</strong>
                    </div>
                    <div class="detail-tile">
                        <span>Packing</span>
                        <strong>{{ $quote['packing_label'] }}</strong>
                        <small class="muted"><a class="edit-link" href="{{ route('order.packing') }}">Change</a></small>
                    </div>
                    <div class="detail-tile">
                        <span>Selected Delivery Dates</span>
                        <strong>{{ $quote['delivery_count'] }} {{ $quote['delivery_count'] === 1 ? 'Delivery' : 'Deliveries' }}</strong>
                        <small class="muted">{{ \Illuminate\Support\Carbon::parse($quote['days'][0]['date'])->format('D, j M') }} – {{ \Illuminate\Support\Carbon::parse($quote['days'][array_key_last($quote['days'])]['date'])->format('D, j M Y') }}</small>
                    </div>
                </div>
                <p style="margin-top:12px;"><strong>{{ $quote['schedule_summary'] }}</strong></p>
                <p class="muted">{{ $quote['per_person_label'] }}. Deliver to {{ $draft['address'] }}.</p>
            </section>

            <section class="summary-card">
                <div class="summary-head">
                    <h2>Selected Menu</h2>
                    <a class="edit-link" href="{{ route('order.menu') }}">Edit Menu</a>
                </div>
                <div class="selected-list">
                    @foreach ($draft['items'] as $id => $quantity)
                        @php $item = $menu->get((int) $id); @endphp
                        @if ($item)
                            <span class="pill">{{ $item->name }} · PKR {{ number_format($item->price_per_person) }} / person × {{ $quantity }}</span>
                        @endif
                    @endforeach
                </div>
            </section>

            <section class="summary-card">
                <div class="summary-head">
                    <h2>Your Daily Menu</h2>
                    <a class="edit-link" href="{{ route('order.delivery') }}">Edit Menu / Dates</a>
                </div>
                @foreach ($quote['days'] as $day)
                    <article class="day-review">
                        <img src="{{ asset($day['lines'][0]['image']) }}" alt="">
                        <div>
                            <strong>{{ \Illuminate\Support\Carbon::parse($day['date'])->format('D, j M') }}</strong>
                            @foreach ($day['lines'] as $line)
                                <p><strong>{{ $line['name'] }}</strong>
                                    @if ($line['persons'])
                                        · {{ $line['persons'] }} persons
                                    @else
                                        · qty {{ $line['quantity'] }}
                                    @endif
                                    · PKR {{ number_format($line['line_total']) }}
                                </p>
                                <p class="muted">{{ $line['description'] }}</p>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </section>
        </div>

        <form class="cost-card" method="POST" action="{{ route('order.pay') }}">
            @csrf
            <h2>Order Cost (PKR)</h2>
            <div class="cost-line"><span>Dishes ({{ $quote['delivery_count'] }} {{ $quote['delivery_count'] === 1 ? 'delivery' : 'deliveries' }})</span><span>PKR {{ number_format($quote['dishes_total']) }}</span></div>
            <div class="cost-line"><span>{{ $quote['packing_label'] }}</span><span>PKR {{ number_format($quote['packing_total']) }}</span></div>
            <div class="cost-line"><span>Delivery ({{ $quote['delivery_count'] }})</span><span>PKR {{ number_format($quote['delivery_total']) }}</span></div>
            <div class="pay-total"><span>Total Payable</span><strong>PKR {{ number_format($quote['grand_total']) }}</strong></div>
            <p class="pay-note">100% payment is required at checkout to confirm your subscription and delivery schedule.</p>
            <p style="margin:14px 0 8px;font-weight:800;">Select Payment Method</p>
            <input type="hidden" name="payment_method" id="payment-method" value="{{ old('payment_method', 'easypaisa') }}">
            <div class="pay-methods">
                @foreach (config('noshmark.payment_methods') as $key => $label)
                    <button class="pay-method {{ old('payment_method', 'easypaisa') === $key ? 'is-selected' : '' }}" type="button" data-pay="{{ $key }}">{{ $label }}</button>
                @endforeach
            </div>
            <button class="btn pay-btn" type="submit">Pay Full Amount — PKR {{ number_format($quote['grand_total']) }} →</button>
            <div class="trust-row">
                <span>Secure payment</span>
                <span>Fresh meals</span>
                <span>On-time delivery</span>
            </div>
        </form>
    </div>
@endsection
