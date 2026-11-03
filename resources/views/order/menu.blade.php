@extends('layouts.order')

@section('title', 'Choose Your Menu — NOSHMARK')
@section('body-class', 'app-body has-dock')

@section('content')
    @include('partials.stepper', ['current' => 1])

    @php
        $serving = (int) old('serving_size', $draft['serving_size']);
        $quantities = old('qty', $draft['items']);
        $sizes = config('noshmark.serving_sizes');
        if ($draft['packing'] === 'pateela' && ! in_array($serving, $sizes, true)) {
            $serving = (int) config('noshmark.pateela_persons');
        }
    @endphp

    <div class="menu-toolbar">
        <div>
            <h1 style="font-size:clamp(36px,5vw,52px);">Choose Your <span class="scribble">Menu</span></h1>
            <p class="muted">Curated Pakistani favourites, freshly prepared for your gathering.</p>
            @if ($draft['packing'] === 'pateela')
                <p class="notice" style="text-align:left;">Whole pateela is selected, so this order serves 30 persons. Choose 2, 4, 8, or 12 to switch back to separate packing.</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('order.menu.store') }}" id="menu-form">
        @csrf
        <div class="serving-box" style="margin-bottom:16px;width:max-content;">
            <div>
                <strong>Serving Size</strong>
                <span>(Persons)</span>
            </div>
            <div class="sizes">
                @foreach ($sizes as $size)
                    <label class="size-option">
                        <input type="radio" name="serving_size" value="{{ $size }}" @checked($serving === $size)>
                        <span>{{ $size }}</span>
                    </label>
                @endforeach
                @if ($draft['packing'] === 'pateela')
                    <label class="size-option">
                        <input type="radio" name="serving_size" value="30" @checked($serving === 30)>
                        <span>30</span>
                    </label>
                @endif
            </div>
        </div>

        <div class="filters" role="tablist">
            <button class="filter is-active" type="button" data-filter="all">All Dishes</button>
            @foreach ($categories as $category)
                <button class="filter" type="button" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
            @endforeach
        </div>
        <input class="control search-field" id="menu-search" type="search" placeholder="Search dishes" style="max-width:320px;margin-bottom:16px;">

        <div class="dish-grid" id="dish-grid">
            @foreach ($items as $item)
                @php $qty = (int) ($quantities[$item->id] ?? 0); @endphp
                <article class="dish-card {{ $qty > 0 ? 'is-picked' : '' }}" data-category="{{ $item->category->slug }}" data-name="{{ strtolower($item->name) }}" data-price="{{ $item->price_per_person }}" data-id="{{ $item->id }}" data-image="{{ asset($item->image) }}">
                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}">
                    <div class="dish-body">
                        <h3>{{ $item->name }}</h3>
                        <p>{{ $item->description }}</p>
                        <div class="dish-row">
                            <div class="price"><b>PKR {{ number_format($item->price_per_person) }}</b> / person</div>
                            <div class="qty">
                                <button type="button" data-qty="-1" aria-label="Decrease {{ $item->name }}">−</button>
                                <input name="qty[{{ $item->id }}]" value="{{ $qty }}" inputmode="numeric" aria-label="Quantity for {{ $item->name }}">
                                <button type="button" data-qty="1" aria-label="Increase {{ $item->name }}">+</button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="dock">
            <div class="dock-label">Selected Dishes (<span id="dock-count">0</span>)</div>
            <div class="dock-chips" id="dock-chips"></div>
            <div class="dock-total">
                <span>Total per Delivery</span>
                <strong id="dock-total">PKR 0</strong>
            </div>
            <button class="btn btn-green" type="submit">Continue →</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/menu.js') }}"></script>
@endpush
