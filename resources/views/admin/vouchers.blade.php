<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Voucher Management</h3>
                <a href="{{ route('admin.vouchers.generate') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-2"></i>Generate Vouchers
                </a>
            </div>

            <div class="card-body">
                <div class="mb-4 d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.vouchers.index', ['filter' => 'all']) }}" 
                       class="btn {{ $filter == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                        All
                    </a>
                    <a href="{{ route('admin.vouchers.index', ['filter' => 'active']) }}" 
                       class="btn {{ $filter == 'active' ? 'btn-success' : 'btn-outline-success' }}">
                        Active
                    </a>
                    <a href="{{ route('admin.vouchers.index', ['filter' => 'used']) }}" 
                       class="btn {{ $filter == 'used' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Used
                    </a>
                    <a href="{{ route('admin.vouchers.index', ['filter' => 'expired']) }}" 
                       class="btn {{ $filter == 'expired' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Expired
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Code</th>
                                <th>Amount</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Expires</th>
                                <th>Used At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $voucher)
                            <tr>
                                <td class="font-monospace fw-bold">{{ $voucher->code }}</td>
                                <td>₱{{ number_format($voucher->amount, 2) }}</td>
                                <td>{{ $voucher->source }}</td>
                                <td>
                                    @if($voucher->is_used)
                                        <span class="badge bg-secondary">Used</span>
                                    @elseif($voucher->isExpired())
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-success">
                                            Active ({{ $voucher->daysRemaining() }}d left)
                                        </span>
                                    @endif
                                </td>
                                <td><small>{{ $voucher->created_at->format('M d, Y h:i A') }}</small></td>
                                <td><small>{{ $voucher->expires_at ? $voucher->expires_at->format('M d, Y') : 'N/A' }}</small></td>
                                <td><small>{{ $voucher->used_at ? $voucher->used_at->format('M d, Y h:i A') : '-' }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    No vouchers found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $vouchers->links() }}
                </div>

                <div class="mt-4 d-flex gap-2">
                    <a href="{{ route('admin.voucher.settings') }}" class="btn btn-outline-primary">
                        <i class="bi bi-gear me-2"></i>Voucher Settings
                    </a>
                    <a href="{{ route('start') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>