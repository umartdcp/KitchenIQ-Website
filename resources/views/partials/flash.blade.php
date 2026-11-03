@if (session('status'))
    <div class="flash" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert" role="alert">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
        @foreach ((array) session('quote_errors', []) as $error)
            @if (! $errors->has('delivery') || $error !== $errors->first('delivery'))
                <p>{{ $error }}</p>
            @endif
        @endforeach
    </div>
@endif
