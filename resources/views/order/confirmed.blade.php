@extends('layouts.order')

@section('title', 'Order confirmed — NOSHMARK')

@section('content')
    @include('partials.stepper', ['current' => 5, 'complete' => true])
    <section class="confirm-hero summary-card">
        <div class="confirm-badge" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M5 12.5l4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1>Order confirmed</h1>
        <p class="muted" style="margin-top:8px;">{{ $order->number }} is paid in full. PKR {{ number_format($order->amount_paid) }} via {{ $order->paymentLabel() }}.</p>
        <div class="detail-grid" style="margin-top:18px;text-align:left;">
            <div class="detail-tile"><span>Plan</span><strong>{{ $order->scheduleLabel() }}</strong></div>
            <div class="detail-tile"><span>Serving</span><strong>{{ $order->serving_size }} persons</strong></div>
            <div class="detail-tile"><span>Packing</span><strong>{{ $order->packingLabel() }}</strong></div>
            <div class="detail-tile"><span>Deliveries</span><strong>{{ $order->deliveries->count() }}</strong></div>
        </div>
        <p style="margin-top:14px;">{{ $order->scheduleSummary() }}</p>
        <div class="selected-list" style="justify-content:center;">
            @foreach ($order->items as $item)
                <span class="pill">{{ $item->name }} × {{ $item->quantity }}</span>
            @endforeach
        </div>
        <div class="wizard-actions" style="justify-content:center;">
            <a class="btn btn-red" href="{{ route('orders.show', $order) }}">Change a delivery menu</a>
            <a class="btn btn-ghost" href="{{ route('order.menu') }}">Place another order</a>
        </div>
    </section>
@endsection
