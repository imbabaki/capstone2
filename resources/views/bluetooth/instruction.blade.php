<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printing Instructions</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.css') }}">
    <style>
        .progress-container {
            display: none;
        }
        .progress-container.show {
            display: block;
        }
        .emergency-btn {
            display: none;
            margin-top: 1rem;
        }
        .emergency-btn.show {
            display: block;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-success text-white">
                <h3 class="mb-0"><i class="bi bi-printer me-2"></i>Printing Instructions</h3>
            </div>
            <div class="card-body">
                <h5 class="mb-3">📋 Print Details</h5>
                <ul class="list-group mb-4">
                    <li class="list-group-item">📄 File: <strong>{{ $order['file_name'] }}</strong></li>
                    <li class="list-group-item">🔢 Copies: <strong>{{ $order['copies'] }}</strong></li>
                    <li class="list-group-item">📑 Pages: <strong>{{ $order['pages'] ?: 'All' }}</strong></li>
                    <li class="list-group-item">🎨 Color: <strong>{{ ucfirst($order['color_option']) }}</strong></li>
                    <li class="list-group-item">📏 Paper: <strong>{{ $order['paper_size'] }}</strong></li>
                    <li class="list-group-item">🔄 Duplex: <strong>{{ $order['duplex'] }}</strong></li>
                    <li class="list-group-item">💰 Total: <strong>₱{{ number_format($order['calculated_total'], 2) }}</strong></li>
                </ul>

                {{-- Change Voucher Display --}}
                @if(!empty($order['change_voucher_code']))
                <div class="alert alert-success">
                    <h5 class="alert-heading"><i class="bi bi-gift me-2"></i>Your Change Voucher</h5>
                    <hr>
                    <p class="mb-1"><strong>Code:</strong> <span class="h4 font-monospace">{{ $order['change_voucher_code'] }}</span></p>
                    <p class="mb-1"><strong>Amount:</strong> ₱{{ number_format($order['change_amount'], 2) }}</p>
                    <p class="mb-0"><strong>Expires:</strong> {{ $order['change_voucher_expires_at'] }}</p>
                </div>
                @endif

                <button id="print-btn" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-printer-fill me-2"></i>Start Printing
                </button>

                {{-- Printing Progress Section --}}
                <div id="progress-container" class="progress-container mt-4">
                    <div class="alert alert-info">
                        <h5 class="alert-heading"><i class="bi bi-hourglass-split me-2"></i>Printing in Progress</h5>
                        <p id="progress-message" class="mb-2">Preparing to print...</p>
                        <p id="job-info" class="mb-2 small text-muted"></p>
                        <div class="progress" style="height: 25px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>
                    </div>

                    {{-- Emergency Jam Button --}}
                    <button id="emergency-jam-btn" class="btn btn-danger btn-lg w-100 emergency-btn">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Emergency: Clear Paper Jam
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script>
        let printJobId = null;
        let progressInterval = null;
        let printComplete = false;
        const totalPages = {{ $order['page_count'] ?? 1 }};
        const copies = {{ $order['copies'] ?? 1 }};
        const totalSheets = totalPages * copies;

        document.getElementById('print-btn').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Printing...';

            // Show progress container
            document.getElementById('progress-container').classList.add('show');

            fetch("{{ route('bluetooth.printJob') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Start monitoring CUPS print queue
                    startRealTimePrintMonitoring();
                } else {
                    alert('❌ Print failed: ' + data.message);
                    location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert('❌ Print error. Please try again.');
                location.reload();
            });
        });

        function startRealTimePrintMonitoring() {
            const progressBar = document.getElementById('progress-bar');
            const progressMessage = document.getElementById('progress-message');
            const jobInfo = document.getElementById('job-info');
            const emergencyBtn = document.getElementById('emergency-jam-btn');
            let checkCount = 0;
            let hasStartedPrinting = false;

            // Show emergency button after 5 seconds
            setTimeout(() => {
                emergencyBtn.classList.add('show');
            }, 5000);

            // Poll CUPS status every second
            progressInterval = setInterval(() => {
                fetch('http://127.0.0.1:5007/print/check-complete' + (printJobId ? '/' + printJobId : ''))
                    .then(res => res.json())
                    .then(data => {
                        checkCount++;

                        if (data.completed && hasStartedPrinting) {
                            // Print job is complete - auto redirect
                            clearInterval(progressInterval);

                            progressBar.style.width = '100%';
                            progressBar.textContent = '100%';
                            progressBar.classList.remove('progress-bar-animated');
                            progressBar.classList.add('bg-success');
                            progressMessage.innerHTML = '<strong>✅ Printing Complete!</strong> Redirecting...';
                            jobInfo.textContent = 'All pages have been printed successfully.';

                            // Auto redirect after 2 seconds
                            setTimeout(() => {
                                window.location.href = "{{ route('bluetooth.success') }}";
                            }, 2000);
                        } else {
                            // Still printing - update progress
                            hasStartedPrinting = true;

                            // Calculate estimated progress based on time
                            const estimatedTime = totalSheets * 5; // 5 seconds per page
                            const elapsed = checkCount;
                            const progress = Math.min(95, (elapsed / estimatedTime) * 100);

                            progressBar.style.width = progress + '%';
                            progressBar.textContent = Math.floor(progress) + '%';

                            if (checkCount < 3) {
                                progressMessage.textContent = 'Sending job to printer...';
                                jobInfo.textContent = 'Preparing print queue...';
                            } else {
                                progressMessage.textContent = `Printing... (${totalSheets} pages)`;
                                jobInfo.textContent = `Job ${printJobId || 'active'} - Estimated time: ${Math.ceil(estimatedTime - elapsed)}s remaining`;
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error checking print status:', err);
                        // Continue checking even if there's an error
                    });
            }, 1000); // Check every second
        }

        // Emergency Jam Button Handler
        document.getElementById('emergency-jam-btn').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Clearing Jam...';

            fetch('http://127.0.0.1:5006/emergency/clear-jam', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Emergency: Clear Paper Jam';
            })
            .catch(err => {
                console.error(err);
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Emergency: Clear Paper Jam';
            });
        });
    </script>
</body>
</html>