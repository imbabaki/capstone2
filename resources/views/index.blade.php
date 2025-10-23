<div class="container mt-4">
    <h2 class="mb-3">📡 Bluetooth Received Files</h2>

    <form action="{{ route('bluetooth.enable') }}" method="POST" class="mb-4">
        @csrf
        <button type="submit" class="btn btn-success">Enable Bluetooth Pairing</button>
    </form>

    <div id="progress-area" class="mb-4"></div>

    <ul id="file-list" class="list-group">
        @foreach($files as $file)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $file }}
                <a href="{{ route('bluetooth.print', $file) }}" class="btn btn-primary btn-sm">Print</a>
            </li>
        @endforeach
    </ul>
</div>

<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
    console.log("Connecting to Socket.IO...");

    const socket = io("http://192.168.4.1:5001", {
        transports: ["websocket", "polling"]
    });

    socket.on("connect", () => console.log("✅ Socket.IO Connected!"));
    socket.on("connect_error", (err) => console.error("❌ Connection Error:", err));

    socket.on("progress", (data) => {
        let bar = document.getElementById("progress-" + data.filename);
        if (!bar) {
            document.getElementById("progress-area").innerHTML += `
                <div class="mb-2">
                    <strong>${data.filename}</strong>
                    <div class="progress">
                        <div id="progress-${data.filename}" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                    </div>
                </div>`;
            bar = document.getElementById("progress-" + data.filename);
        }
        bar.style.width = data.percent + "%";
        bar.innerText = data.percent + "%";
    });

    // Track the last redirected file
    let lastRedirected = null;

    socket.on("new_file", (data) => {
        console.log("📁 New file received:", data);

        // Add file to list
        document.getElementById("file-list").innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                ${data.filename}
                <a href="/bluetooth/print/${data.filename}" class="btn btn-primary btn-sm">Print</a>
            </li>`;

        // ✅ Auto redirect only if it’s a new file
        if (data.redirect && lastRedirected !== data.filename) {
            lastRedirected = data.filename;
            console.log("🔀 Redirecting to:", data.redirect);

            // Small delay to ensure file is fully available
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 500);
        }
    });
</script>
