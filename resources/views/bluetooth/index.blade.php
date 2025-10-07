
<div class="container">
    <h2>Bluetooth Received Files</h2>

    <form action="{{ route('bluetooth.enable') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success mb-3">
            Enable Bluetooth Pairing
        </button>
    </form>

    <div id="progress-area"></div>

    <ul id="file-list">
        @foreach($files as $file)
            <li>
                {{ $file }}
                <a href="{{ route('bluetooth.print', $file) }}" class="btn btn-primary btn-sm">Print</a>
            </li>
        @endforeach
    </ul>
</div>


<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>

    console.log("Connecting to Socket.IO...");

    const socket = io("http://192.168.4.1:5000", {
        transports: ["websocket", "polling"] // ✅ Ensures fallback
    });

    socket.on("connect", () => {
        console.log("✅ Socket.IO Connected!");
    });

    socket.on("connect_error", (err) => {
        console.error("❌ Connection Error:", err);
    });

    socket.on("progress", (data) => {
        let bar = document.getElementById("progress-" + data.filename);
        if (!bar) {
            document.getElementById("progress-area").innerHTML += `
                <div class="mb-2">
                    <strong>${data.filename}</strong>
                    <div class="progress">
                        <div id="progress-${data.filename}" class="progress-bar" 
                            role="progressbar" style="width: 0%">0%</div>
                    </div>
                </div>`;
            bar = document.getElementById("progress-" + data.filename);
        }
        bar.style.width = data.percent + "%";
        bar.innerText = data.percent + "%";
    });

    socket.on("new_file", (data) => {
        console.log("📁 New file received:", data);

        document.getElementById("file-list").innerHTML += `
            <li>
                ${data.filename}
                <a href="/bluetooth/print/${data.filename}" class="btn btn-primary btn-sm">Print</a>
            </li>`;

        // ✅ Redirect (Fallback if no redirect sent)
        const redirectUrl = data.redirect || `/edit/upload/${data.filename}`;
        console.log("🔀 Redirecting to:", redirectUrl);
        window.location.href = redirectUrl;
    });
</script>

