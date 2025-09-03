<div class="text-center mt-5">
    <h2>Bluetooth File Transfer</h2>
    <p id="waiting-text">Waiting for PDF file...</p>
    <button id="bluetoothBtn" class="btn btn-primary">Enable Bluetooth</button>

    <div id="status" class="mt-3"></div>

    <ul id="pdfList" class="mt-4 list-group"></ul> {{-- Optional: display received PDFs --}}
</div>

<script>
document.getElementById('bluetoothBtn').addEventListener('click', startBluetooth);

function startBluetooth() {
    const statusEl = document.getElementById('status');
    statusEl.innerText = '⚙️ Activating Bluetooth...';

    fetch('{{ route('bluetooth.enable') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(data => {
        statusEl.innerText = '📥 ' + data.status;

        // Optionally start polling for received PDFs every 3 seconds
        setInterval(fetchReceivedPDFs, 3000);
    })
    .catch(err => {
        statusEl.innerText = '❌ Error activating Bluetooth.';
        console.error(err);
    });
}

function fetchReceivedPDFs() {
    fetch('{{ route('bluetooth.list') }}')
        .then(res => res.json())
        .then(data => {
            const pdfList = document.getElementById('pdfList');
            pdfList.innerHTML = '';
            data.pdfFiles.forEach(file => {
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.textContent = file.name;
                pdfList.appendChild(li);
            });
        })
        .catch(err => console.error(err));
}
</script>