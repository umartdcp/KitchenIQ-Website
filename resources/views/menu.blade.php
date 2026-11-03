@extends('layouts.marketing')

@section('title', 'Menu — NOSHMARK')

@section('content')
    <div class="page-head">
        <h1>Our Menu</h1>
        <p>Curated Pakistani favourites, priced per person. Create an account to set a serving size and a repeat schedule.</p>
    </div>
    <div class="dish-grid">
        @foreach ($items as $item)
            <article class="dish-card">
                <img src="{{ asset($item->image) }}" alt="{{ $item->name }}">
                <div class="dish-body">
                    <h3>{{ $item->name }}</h3>
                    <p>{{ $item->description }}</p>
                    <div class="dish-row">
                        <div class="price"><b>PKR {{ number_format($item->price_per_person) }}</b> / person</div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <p style="text-align:center;margin-top:22px;">
        @auth
            <a class="btn btn-red" href="{{ route('order.menu') }}">Order from this menu</a>
        @else
            <a class="btn btn-red" href="{{ route('home') }}">Create an account to order</a>
        @endauth
    </p>
@endsection
