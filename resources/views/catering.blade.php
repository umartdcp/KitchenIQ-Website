@extends('layouts.marketing')

@section('title', 'Catering — NOSHMARK')

@section('content')
    <div class="page-head">
        <h1>Catering &amp; packing</h1>
        <p>The dish cost follows the number of persons. Packing is added on top, then a delivery fee for each day.</p>
    </div>
    <div class="packing-grid">
        <article class="plain-card">
            <img src="{{ asset('images/menu/kebab.jpg') }}" alt="Grilled kebabs for individual servings">
            <h2>Separate Packing</h2>
            <p class="muted">Individually packed meals for each person. PKR {{ number_format(config('noshmark.separate_packing_per_person')) }} per person, each delivery day.</p>
        </article>
        <article class="plain-card">
            <img src="{{ asset('images/menu/biryani.jpg') }}" alt="Biryani in a large serving bowl">
            <h2>Whole Pateela</h2>
            <p class="muted">One traditional pateela for the table. Available for 30 persons at PKR {{ number_format(config('noshmark.pateela_price')) }} per delivery, plus the dish cost for 30.</p>
        </article>
    </div>
@endsection
