@php
    $steps = [
        1 => 'Choose Your Menu',
        2 => 'Subscription Schedule',
        3 => 'Choose Packing',
        4 => 'Delivery & Menu',
        5 => 'Review & Pay',
    ];
    $current = $current ?? 1;
    $complete = $complete ?? false;
@endphp
<ol class="stepper">
    @foreach ($steps as $number => $label)
        @php
            $state = $complete || $number < $current ? 'is-done' : ($number === $current ? 'is-current' : '');
        @endphp
        <li class="stepper-step {{ $state }}">
            <span class="stepper-dot">
                @if ($complete || $number < $current)
                    <svg viewBox="0 0 20 20"><path d="M4 10.5l4 4 8-8" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @else
                    {{ $number }}
                @endif
            </span>
            <span class="stepper-label">{{ $label }}</span>
        </li>
    @endforeach
</ol>
