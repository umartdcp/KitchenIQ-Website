@extends('layouts.order')

@section('title', 'My orders — NOSHMARK')

@section('content')
    <div class="page-head" style="text-align:left;margin-left:0;">
        <h1>My orders</h1>
        <p>Open an order to change the menu on a future delivery day, or to pay the next repeat.</p>
    </div>
    @if ($orders->isEmpty())
        <div class="summary-card">
            <p>You have not placed an order yet.</p>
            <a class="btn btn-red" href="{{ route('order.menu') }}" style="margin-top:12px;">Choose your menu</a>
        </div>
    @else
        <div class="order-list">
            @foreach ($orders as $order)
                <a class="order-card" href="{{ route('orders.show', $order) }}">
                    <div>
                        <h2>{{ $order->number }}</h2>
                        <p class="muted">{{ $order->scheduleLabel() }} · {{ $order->serving_size }} persons · {{ $order->packingLabel() }}</p>
                    </div>
                    <div style="text-align:right;">
                        <strong>PKR {{ number_format($order->grand_total) }}</strong>
                        <p class="muted">{{ ucfirst($order->payment_status) }} · {{ $order->deliveries->count() }} deliveries</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
