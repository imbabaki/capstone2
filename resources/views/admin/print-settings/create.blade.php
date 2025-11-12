@extends('admin.layout')

@section('title', 'Add Price')

@section('content')
<!-- Header -->
<div class="page-header mb-2">
    <h1 class="page-title">
        <i class="fas fa-plus-circle"></i> Add Price
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

<!-- Form -->
<form method="POST" action="{{ route('print-settings.store') }}">
    @csrf

    <!-- Paper Size -->
    <div class="card mb-2">
        <div class="card-header">
            <i class="fas fa-file-alt" style="color: #3b82f6;"></i> Paper Size
        </div>
        <div style="padding: 16px;">
            <div class="grid grid-3" style="gap: 8px;">
                <label class="radio-card" style="display: flex; flex-direction: column; align-items: center; padding: 16px; border: 2px solid #d1d5db; background: #fff; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <input type="radio" name="paper_size" value="A4" {{ old('paper_size') == 'A4' ? 'checked' : '' }} required class="radio-input">
                    <i class="fas fa-file" style="font-size: 28px; color: #3b82f6; margin-bottom: 8px;"></i>
                    <span style="font-size: 14px; font-weight: 600;">A4</span>
                    <span style="font-size: 10px; color: #6b7280;">210x297mm</span>
                </label>
                <label class="radio-card" style="display: flex; flex-direction: column; align-items: center; padding: 16px; border: 2px solid #d1d5db; background: #fff; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <input type="radio" name="paper_size" value="Letter" {{ old('paper_size') == 'Letter' ? 'checked' : '' }} required class="radio-input">
                    <i class="fas fa-file" style="font-size: 28px; color: #10b981; margin-bottom: 8px;"></i>
                    <span style="font-size: 14px; font-weight: 600;">Letter</span>
                    <span style="font-size: 10px; color: #6b7280;">8.5x11in</span>
                </label>
                <label class="radio-card" style="display: flex; flex-direction: column; align-items: center; padding: 16px; border: 2px solid #d1d5db; background: #fff; border-radius: 8px; cursor: pointer; transition: all 0.2s;">
                    <input type="radio" name="paper_size" value="Legal" {{ old('paper_size') == 'Legal' ? 'checked' : '' }} required class="radio-input">
                    <i class="fas fa-file" style="font-size: 28px; color: #f59e0b; margin-bottom: 8px;"></i>
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
                <label class="radio-card" style="display: flex; align-items: center; padding: 16px; border: 2px solid #d1d5db; background: #fff; border-radius: 8px; cursor: pointer;">
                    <input type="radio" name="color_option" value="color" {{ old('color_option') == 'color' ? 'checked' : '' }} required style="width: 20px; height: 20px; margin-right: 12px;">
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">
                            <i class="fas fa-palette" style="color: #ec4899;"></i> Color
                        </div>
                        <div style="font-size: 11px; color: #6b7280;">Full color</div>
                    </div>
                </label>
                <label class="radio-card" style="display: flex; align-items: center; padding: 16px; border: 2px solid #d1d5db; background: #fff; border-radius: 8px; cursor: pointer;">
                    <input type="radio" name="color_option" value="grayscale" {{ old('color_option') == 'grayscale' ? 'checked' : '' }} required style="width: 20px; height: 20px; margin-right: 12px;">
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
            <i class="fas fa-peso-sign" style="color: #10b981;"></i> Price per Page
        </div>
        <div style="padding: 16px;">
            <div style="position: relative;">
                <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 28px; font-weight: bold; color: #10b981;">₱</span>
                <input
                    type="number"
                    step="0.01"
                    name="price"
                    value="{{ old('price') }}"
                    required
                    min="0.01"
                    placeholder="0.00"
                    class="form-control"
                    style="font-size: 28px; font-weight: bold; padding-left: 48px; text-align: left;"
                >
            </div>
            <small style="font-size: 12px; color: #6b7280; margin-top: 8px; display: block;">
                <i class="fas fa-info-circle"></i> Minimum: ₱0.01
            </small>
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

    <!-- Action Buttons -->
    <div class="grid grid-2" style="gap: 8px;">
        <a href="{{ route('admin.print-settings.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save
        </button>
    </div>
</form>

<script>
// Handle radio card selection
document.querySelectorAll('.radio-card').forEach(card => {
    const radio = card.querySelector('input[type="radio"]');

    // Set initial state
    if (radio.checked) {
        card.style.borderColor = getColorForValue(radio.name, radio.value);
        card.style.background = getBgColorForValue(radio.name, radio.value);
    }

    card.addEventListener('click', function() {
        // Uncheck all siblings
        const name = radio.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            const parentCard = r.closest('.radio-card');
            if (parentCard) {
                parentCard.style.borderColor = '#d1d5db';
                parentCard.style.background = '#fff';
            }
        });

        // Check this one
        radio.checked = true;
        card.style.borderColor = getColorForValue(radio.name, radio.value);
        card.style.background = getBgColorForValue(radio.name, radio.value);
    });
});

function getColorForValue(name, value) {
    if (name === 'paper_size') {
        if (value === 'A4') return '#3b82f6';
        if (value === 'Letter') return '#10b981';
        if (value === 'Legal') return '#f59e0b';
    }
    if (name === 'color_option') {
        if (value === 'color') return '#ec4899';
        if (value === 'grayscale') return '#6b7280';
    }
    return '#d1d5db';
}

function getBgColorForValue(name, value) {
    if (name === 'paper_size') {
        if (value === 'A4') return '#dbeafe';
        if (value === 'Letter') return '#d1fae5';
        if (value === 'Legal') return '#fef3c7';
    }
    if (name === 'color_option') {
        if (value === 'color') return '#fce7f3';
        if (value === 'grayscale') return '#f3f4f6';
    }
    return '#fff';
}
</script>

<style>
.radio-input {
    position: absolute;
    opacity: 0;
}
</style>
@endsection
