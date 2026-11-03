@extends('layouts.order')

@section('title', $order->number.' — NOSHMARK')

@section('content')
    <div class="summary-head">
        <div>
            <h1>{{ $order->number }}</h1>
            <p class="muted">{{ $order->scheduleLabel() }} · {{ $order->scheduleSummary() }}</p>
        </div>
        <a class="btn btn-ghost" href="{{ route('orders.index') }}">All orders</a>
    </div>

    <section class="summary-card" style="margin-top:16px;">
        <div class="detail-grid">
            <div class="detail-tile"><span>Serving</span><strong>{{ $order->serving_size }} persons</strong></div>
            <div class="detail-tile"><span>Packing</span><strong>{{ $order->packingLabel() }}</strong></div>
            <div class="detail-tile"><span>Per-person schedule</span><strong>{{ $order->perPersonLabel() }}</strong></div>
            <div class="detail-tile"><span>Paid in full</span><strong>PKR {{ number_format($order->amount_paid) }}</strong></div>
        </div>
        <div class="selected-list">
            @foreach ($order->items as $item)
                <span class="pill">{{ $item->name }} × {{ $item->quantity }}</span>
            @endforeach
        </div>
        <p class="muted" style="margin-top:8px;">{{ $order->address }}</p>
    </section>

    <h2 style="margin:22px 0 10px;">Menu for each delivery day</h2>
    @foreach ($order->deliveries as $delivery)
        <article class="summary-card {{ $delivery->isLocked() ? 'locked' : '' }}">
            <div class="summary-head">
                <h3>{{ $delivery->delivery_date->format('l, j M Y') }}</h3>
                <span class="pill">{{ $delivery->paid ? 'Paid' : 'Unpaid cycle' }}</span>
            </div>
            @if ($delivery->isLocked())
                <p class="muted">This day is inside the 2-day lead time, so the menu is locked.</p>
                @foreach ($delivery->items as $line)
                    <p>{{ $line->name }} · PKR {{ number_format($line->line_total) }}</p>
                @endforeach
            @else
                <form method="POST" action="{{ route('orders.delivery.update', [$order, $delivery]) }}">
                    @csrf
                    @php
                        $lines = $delivery->items->values();
                        $extra = ($order->per_person_schedule === 'personalized' && $order->packing !== 'pateela') ? 1 : 0;
                        $slots = max(1, $lines->count() + $extra);
                    @endphp
                    @for ($index = 0; $index < $slots; $index++)
                        @php $line = $lines[$index] ?? null; @endphp
                        <div class="day-line" style="margin-top:10px;">
                            <select class="control line-menu" name="lines[{{ $index }}][menu_item_id]">
                                <option value="">Choose a dish</option>
                                @foreach ($menu as $item)
                                    <option value="{{ $item->id }}" @selected((int) ($line->menu_item_id ?? 0) === $item->id)>{{ $item->name }} · PKR {{ number_format($item->price_per_person) }}</option>
                                @endforeach
                            </select>
                            @if ($order->per_person_schedule === 'personalized' && $order->packing !== 'pateela')
                                <label class="line-field">Persons
                                    <input class="control" type="number" min="0" max="{{ $order->serving_size }}" name="lines[{{ $index }}][persons]" value="{{ $line->persons ?? 0 }}">
                                </label>
                            @else
                                <label class="line-field">Qty
                                    <input class="control" type="number" min="1" max="20" name="lines[{{ $index }}][quantity]" value="{{ $line->quantity ?? 1 }}" @disabled($order->packing === 'pateela')>
                                </label>
                                @if ($order->packing === 'pateela')
                                    <input type="hidden" name="lines[{{ $index }}][quantity]" value="1">
                                @endif
                            @endif
                        </div>
                    @endfor
                    <label class="inline-check">
                        <input type="checkbox" name="confirm_payment" value="1">
                        If this changes the price, charge 100% of the new total.
                    </label>
                    <button class="btn btn-red" type="submit" style="margin-top:10px;">Update this day</button>
                </form>
            @endif
        </article>
    @endforeach

    <section class="summary-card">
        <h2>Next repeat</h2>
        <p class="muted">{{ $order->scheduleSummary() }} Add the next cycle, set each day’s menu, then pay 100% of that cycle.</p>
        @php $due = $due ?? $order->unpaidQuote(); @endphp
        @if ($due['count'] > 0)
            <div class="due-line"><span>Dishes</span><span>PKR {{ number_format($due['dishes']) }}</span></div>
            <div class="due-line"><span>Packing</span><span>PKR {{ number_format($due['packing']) }}</span></div>
            <div class="due-line"><span>Delivery</span><span>PKR {{ number_format($due['delivery']) }}</span></div>
            <div class="due-total"><span>Due now</span><strong>PKR {{ number_format($due['total']) }}</strong></div>
            <form method="POST" action="{{ route('orders.pay-cycle', $order) }}" style="margin-top:12px;">
                @csrf
                <button class="btn btn-red" type="submit">Pay full cycle — PKR {{ number_format($due['total']) }}</button>
            </form>
        @else
            <form method="POST" action="{{ route('orders.extend', $order) }}" style="margin-top:12px;">
                @csrf
                <button class="btn btn-green" type="submit">Add the next {{ strtolower($order->scheduleLabel()) }} deliveries</button>
            </form>
        @endif
    </section>
@endsection
