@extends('admin.layout')

@section('title', 'Edit Price Setting')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-edit"></i> Edit Price
    </h1>
    <a href="{{ route('admin.print-settings.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- Error Display -->
@if($errors->any())
<div class="alert alert-danger mb-2">
    <strong><i class="fas fa-exclamation-circle"></i> Errors:</strong>
    <ul style="margin: 8px 0 0 20px; font-size: 13px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Current Configuration -->
<div class="card mb-2">
    <div class="card-header">
        <i class="fas fa-info-circle" style="color: #3b82f6;"></i> Current Configuration
    </div>
    <div style="padding: 16px;">
        <div class="grid grid-2" style="gap: 8px; margin-bottom: 12px;">
            <div style="background: #f3f4f6; padding: 12px; border-radius: 8px;">
                <div style="font-size: 10px; color: #6b7280; margin-bottom: 4px;">ID</div>
                <div style="font-size: 14px; font-weight: 600;">#{{ $setting->id }}</div>
            </div>
            <div style="background: #f3f4f6; padding: 12px; border-radius: 8px;">
                <div style="font-size: 10px; color: #6b7280; margin-bottom: 4px;">Created</div>
                <div style="font-size: 14px; font-weight: 600;">{{ $setting->created_at->format('M d, Y') }}</div>
            </div>
        </div>
        <div style="background: #d1fae5; padding: 16px; border-radius: 8px; text-align: center;">
            <div style="font-size: 11px; color: #065f46; margin-bottom: 4px;">CURRENT PRICE</div>
            <div style="font-size: 32px; font-weight: bold; color: #059669;">₱{{ number_format($setting->price, 2) }}</div>
            <div style="font-size: 12px; color: #065f46; margin-top: 4px;">{{ $setting->paper_size }} • {{ ucfirst($setting->color_option) }}</div>
        </div>
    </div>
</div>

<!-- Edit Form -->
<form method="POST" action="{{ route('print-settings.update', $setting) }}">
    @csrf
    @method('PUT')

    <!-- Paper Size -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-file-alt" style="color: #3b82f6;"></i> Paper Size
        </div>
        <div style="padding: 16px;">
            <div class="grid grid-3" style="gap: 8px;">
                <label style="display: flex; flex-direction: column; align-items: center; padding: 12px; border: 2px solid {{ old('paper_size', $setting->paper_size) == 'A4' ? '#3b82f6' : '#d1d5db' }}; background: {{ old('paper_size', $setting->paper_size) == 'A4' ? '#dbeafe' : '#fff' }}; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <input type="radio" name="paper_size" value="A4" {{ old('paper_size', $setting->paper_size) == 'A4' ? 'checked' : '' }} required style="position: absolute; opacity: 0;">
                    <i class="fas fa-file" style="font-size: 24px; color: #3b82f6; margin-bottom: 4px;"></i>
                    <span style="font-size: 14px; font-weight: 600;">A4</span>
                    <span style="font-size: 10px; color: #6b7280;">210x297mm</span>
                </label>
                <label style="display: flex; flex-direction: column; align-items: center; padding: 12px; border: 2px solid {{ old('paper_size', $setting->paper_size) == 'Letter' ? '#10b981' : '#d1d5db' }}; background: {{ old('paper_size', $setting->paper_size) == 'Letter' ? '#d1fae5' : '#fff' }}; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <input type="radio" name="paper_size" value="Letter" {{ old('paper_size', $setting->paper_size) == 'Letter' ? 'checked' : '' }} required style="position: absolute; opacity: 0;">
                    <i class="fas fa-file" style="font-size: 24px; color: #10b981; margin-bottom: 4px;"></i>
                    <span style="font-size: 14px; font-weight: 600;">Letter</span>
                    <span style="font-size: 10px; color: #6b7280;">8.5x11in</span>
                </label>
                <label style="display: flex; flex-direction: column; align-items: center; padding: 12px; border: 2px solid {{ old('paper_size', $setting->paper_size) == 'Legal' ? '#f59e0b' : '#d1d5db' }}; background: {{ old('paper_size', $setting->paper_size) == 'Legal' ? '#fef3c7' : '#fff' }}; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <input type="radio" name="paper_size" value="Legal" {{ old('paper_size', $setting->paper_size) == 'Legal' ? 'checked' : '' }} required style="position: absolute; opacity: 0;">
                    <i class="fas fa-file" style="font-size: 24px; color: #f59e0b; margin-bottom: 4px;"></i>
                    <span style="font-size: 14px; font-weight: 600;">Legal</span>
                    <span style="font-size: 10px; color: #6b7280;">8.5x14in</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Color Option -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-palette" style="color: #ec4899;"></i> Color Option
        </div>
        <div style="padding: 16px;">
            <div class="grid grid-2" style="gap: 12px;">
                <label style="display: flex; align-items: center; padding: 16px; border: 2px solid {{ old('color_option', $setting->color_option) == 'color' ? '#ec4899' : '#d1d5db' }}; background: {{ old('color_option', $setting->color_option) == 'color' ? '#fce7f3' : '#fff' }}; border-radius: 8px; cursor: pointer;">
                    <input type="radio" name="color_option" value="color" {{ old('color_option', $setting->color_option) == 'color' ? 'checked' : '' }} required style="width: 20px; height: 20px; margin-right: 12px;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">
                            <i class="fas fa-palette" style="color: #ec4899;"></i> Color
                        </div>
                        <div style="font-size: 11px; color: #6b7280;">Full color</div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; padding: 16px; border: 2px solid {{ old('color_option', $setting->color_option) == 'grayscale' ? '#6b7280' : '#d1d5db' }}; background: {{ old('color_option', $setting->color_option) == 'grayscale' ? '#f3f4f6' : '#fff' }}; border-radius: 8px; cursor: pointer;">
                    <input type="radio" name="color_option" value="grayscale" {{ old('color_option', $setting->color_option) == 'grayscale' ? 'checked' : '' }} required style="width: 20px; height: 20px; margin-right: 12px;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">
                            <i class="fas fa-adjust" style="color: #6b7280;"></i> B&W
                        </div>
                        <div style="font-size: 11px; color: #6b7280;">Grayscale</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Price -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-peso-sign" style="color: #10b981;"></i> New Price per Page
        </div>
        <div style="padding: 16px;">
            <div style="position: relative;">
                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 28px; font-weight: bold; color: #10b981;">₱</span>
                <input
                    type="number"
                    step="0.01"
                    name="price"
                    id="price"
                    required
                    min="0.01"
                    value="{{ old('price', $setting->price) }}"
                    placeholder="0.00"
                    class="form-control"
                    style="font-size: 28px; font-weight: bold; padding-left: 48px; text-align: left;"
                >
            </div>
            <small style="font-size: 12px; color: #6b7280; margin-top: 8px; display: block;">
                <i class="fas fa-info-circle"></i> Minimum: ₱0.01
            </small>

            <!-- Price Comparison -->
            <div style="margin-top: 16px; padding: 12px; background: #fef3c7; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                <div style="text-align: center;">
                    <div style="font-size: 10px; color: #78350f; margin-bottom: 4px;">OLD</div>
                    <div style="font-size: 16px; font-weight: bold; color: #92400e;">₱{{ number_format($setting->price, 2) }}</div>
                </div>
                <i class="fas fa-arrow-right" style="color: #f59e0b;"></i>
                <div style="text-align: center;">
                    <div style="font-size: 10px; color: #065f46; margin-bottom: 4px;">NEW</div>
                    <div id="new-price-display" style="font-size: 16px; font-weight: bold; color: #10b981;">₱{{ number_format($setting->price, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Guide -->
    <div class="alert alert-info mb-2">
        <strong><i class="fas fa-lightbulb"></i> Suggested Prices:</strong>
        <div style="margin-top: 8px; font-size: 12px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                <span>A4 B&W:</span>
                <span style="font-weight: 600;">₱2-5</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                <span>A4 Color:</span>
                <span style="font-weight: 600;">₱5-15</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                <span>Legal B&W:</span>
                <span style="font-weight: 600;">₱3-7</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Legal Color:</span>
                <span style="font-weight: 600;">₱8-20</span>
            </div>
        </div>
    </div>

    <!-- Important Notice -->
    <div class="alert alert-warning mb-2">
        <strong><i class="fas fa-exclamation-triangle"></i> Important:</strong>
        <ul style="margin: 8px 0 0 20px; font-size: 12px;">
            <li>Price changes affect all future print jobs</li>
            <li>Consider customer impact before changing</li>
            <li>Last updated: {{ $setting->updated_at->format('M d, Y h:i A') }}</li>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-2" style="gap: 8px;">
        <a href="{{ route('admin.print-settings.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-warning">
            <i class="fas fa-save"></i> Update
        </button>
    </div>
</form>

<script>
// Update new price display in real-time
document.getElementById('price').addEventListener('input', function(e) {
    const newPrice = parseFloat(e.target.value) || 0;
    document.getElementById('new-price-display').textContent = '₱' + newPrice.toFixed(2);
});
</script>
@endsection
