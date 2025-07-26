<!-- resources/views/Bluetooth.blade.php -->



<div class="text-center mt-5">
    <h2>Bluetooth File Transfer</h2>
    <p>Waiting for PDF file...</p>
    <button onclick="startBluetooth()" class="btn btn-primary">Enable Bluetooth</button>

    <div id="status" class="mt-3"></div>
</div>

<script>
function startBluetooth() {
    document.getElementById('status').innerText = '⚙️ Simulating Bluetooth activation...';

    // In reality, this step will be handled by system services or integration
    // On Raspberry Pi, you'll use `bluetoothctl` or similar.
    setTimeout(() => {
        document.getElementById('status').innerText = '📥 Ready to receive PDF via Bluetooth.';
    }, 2000);
}
</script>

