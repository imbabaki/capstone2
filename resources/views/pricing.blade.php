@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pricing Details</h2>

    @if (session('data'))
        @php $d = session('data'); @endphp
        <ul>
            <li><strong>File:</strong> {{ $d['file'] }}</li>
            <li><strong>Copies:</strong> {{ $d['copies'] }}</li>
            <li><strong>Page Range:</strong> {{ $d['page_range'] }}</li>
            <li><strong>Color:</strong> {{ $d['color'] }}</li>
            <li><strong>Paper Size:</strong> {{ $d['paper_size'] }}</li>
            <li><strong>Source:</strong> {{ $d['source'] }}</li>
        </ul>

        @php
            $base = 5; // base cost per copy
            $color = $d['color'] === 'color' ? 2 : 0;
            $copies = max(1, intval($d['copies']));
            $total = ($base + $color) * $copies;
        @endphp

        <div class="alert alert-success mt-3">
            <strong>Total Price:</strong> ₱{{ number_format($total, 2) }}
        </div>
    @else
        <p>No pricing info found.</p>
    @endif
</div>
@endsection
