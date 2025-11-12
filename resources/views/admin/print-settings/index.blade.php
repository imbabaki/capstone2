@extends('admin.layout')

@section('title', 'Price Settings')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-dollar-sign"></i> Prices
    </h1>
    <a href="{{ route('print-settings.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add
    </a>
</div>

<!-- Stats -->
<div class="grid grid-3 mb-2">
    <div class="stat-mini blue">
        <div class="stat-mini-value">{{ $settings->count() }}</div>
        <div class="stat-mini-label">Total</div>
    </div>
    <div class="stat-mini green">
        <div class="stat-mini-value">{{ $settings->pluck('paper_size')->unique()->count() }}</div>
        <div class="stat-mini-label">Sizes</div>
    </div>
    <div class="stat-mini purple">
        @if($settings->count() > 0)
            <div class="stat-mini-value">₱{{ number_format($settings->min('price'), 0) }}-{{ number_format($settings->max('price'), 0) }}</div>
        @else
            <div class="stat-mini-value">N/A</div>
        @endif
        <div class="stat-mini-label">Range</div>
    </div>
</div>

<!-- Price List -->
@forelse ($settings as $setting)
    <div class="price-item {{ $setting->color_option }}">
        <div class="price-item-header">
            <div class="flex-1">
                <div class="price-item-badges">
                    <span class="badge badge-{{ $setting->paper_size === 'A4' ? 'blue' : ($setting->paper_size === 'Letter' ? 'green' : 'yellow') }}">
                        {{ $setting->paper_size }}
                    </span>
                    <span class="badge badge-{{ $setting->color_option === 'color' ? 'pink' : 'gray' }}">
                        <i class="fas fa-{{ $setting->color_option === 'color' ? 'palette' : 'adjust' }}"></i>
                        {{ ucfirst($setting->color_option) }}
                    </span>
                </div>
                <div class="price-item-price">
                    ₱{{ number_format($setting->price, 2) }}
                    <small>/page</small>
                </div>
            </div>
            <div style="font-size: 11px; color: #9ca3af;">#{{ $setting->id }}</div>
        </div>

        <div class="price-item-actions">
            <a href="{{ route('print-settings.edit', $setting) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('print-settings.destroy', $setting) }}" method="POST" style="flex: 1;" onsubmit="return confirm('Delete this?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
@empty
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fas fa-inbox"></i>
        </div>
        <div class="empty-state-title">No price settings</div>
        <div class="empty-state-desc">Add your first pricing</div>
        <a href="{{ route('print-settings.create') }}" class="btn btn-primary mt-2">
            <i class="fas fa-plus-circle"></i> Add Price Setting
        </a>
    </div>
@endforelse

<!-- Help -->
<div class="alert alert-info mt-2">
    <strong><i class="fas fa-info-circle"></i> Quick Guide:</strong>
    <ul style="margin: 8px 0 0 20px; font-size: 13px;">
        <li>Tap <strong>Add</strong> to create new pricing</li>
        <li>Tap <strong>Edit</strong> to modify prices</li>
        <li>Set prices for different paper sizes & colors</li>
    </ul>
</div>
@endsection
