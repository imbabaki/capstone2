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


@push('scripts')
<script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
<script>
    const socket = io("http://127.0.0.1:5000"); // Flask runs here

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
        document.getElementById("file-list").innerHTML += `
            <li>
                ${data.filename}
                <a href="/bluetooth/print/${data.filename}" class="btn btn-primary btn-sm">Print</a>
            </li>`;
    });
</script>