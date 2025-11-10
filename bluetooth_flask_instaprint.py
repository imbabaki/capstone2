import eventlet
# Patch for async support - MUST BE FIRST
eventlet.monkey_patch()

import os
import shutil
import time
import subprocess
from flask import Flask, request, jsonify
from flask_socketio import SocketIO
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler

# CONFIG
WATCH_FOLDER = "/home/instaprint/Downloads"  # folder to watch for new files
LARAVEL_UPLOAD_FOLDER = "/var/www/html/laravel/public/storage/uploads"  # Laravel storage
REDIRECT_URL = "http://192.168.4.1:8000/bluetooth/preview/{filename}"  # ✅ Fixed route
PRINTER_NAME = "EPSON_L120_Series"  # CUPS USB printer name

# Flask + Socket.IO
app = Flask(__name__)
socketio = SocketIO(app,
                   cors_allowed_origins="*",
                   async_mode='eventlet',
                   logger=True,
                   engineio_logger=True)

# Track files and their paths
file_registry = {}

# ==============================
# FILE WATCHER
# ==============================
class UploadHandler(FileSystemEventHandler):
    def __init__(self):
        self.processed_files = {}  # Track processed files to avoid duplicate triggers

    def handle_file(self, event):
        """Handle both new and modified files in the WATCH_FOLDER."""
        if event.is_directory:
            return

        filename = os.path.basename(event.src_path)

        # Only process PDF files
        if not filename.lower().endswith('.pdf'):
            return

        # Avoid processing the same file multiple times within 3 seconds
        # (modified event can trigger multiple times)
        current_time = time.time()
        if filename in self.processed_files:
            time_since_last = current_time - self.processed_files[filename]
            if time_since_last < 3:  # Debounce: ignore if processed within last 3 seconds
                return

        self.processed_files[filename] = current_time
        print(f"✅ File detected: {filename}")

        # Ensure Laravel storage exists
        os.makedirs(LARAVEL_UPLOAD_FOLDER, exist_ok=True)
        target_path = os.path.join(LARAVEL_UPLOAD_FOLDER, filename)

        try:
            # ✅ Wait for stable file size (optimized for faster response)
            prev_size = -1
            stable_count = 0
            max_attempts = 15

            for i in range(max_attempts):
                if not os.path.exists(event.src_path):
                    print(f"⚠️ File disappeared: {filename}")
                    return

                curr_size = os.path.getsize(event.src_path)
                if curr_size == prev_size and curr_size > 0:
                    stable_count += 1
                    if stable_count >= 2:  # Stable for 2 checks (0.4s)
                        break
                else:
                    stable_count = 0
                prev_size = curr_size
                time.sleep(0.2)  # Faster polling: 200ms instead of 500ms

            # Copy file to Laravel storage
            shutil.copy(event.src_path, target_path)
            print(f"📂 Copied to Laravel storage: {target_path}")

            # ✅ DON'T auto-print via Bluetooth - let user configure first
            # The print will happen after payment in the web interface

            # 🔀 Auto redirect logic
            redirect_link = REDIRECT_URL.format(filename=filename)
            print(f"🔗 Redirecting to: {redirect_link}")

            # Emit new file to any connected clients
            print(f"📡 Emitting 'new_file' event via Socket.IO...")
            socketio.emit("new_file", {
                "filename": filename,
                "redirect": redirect_link
            })
            print(f"✅ Socket.IO event emitted successfully")

            # ✅ Register file paths for later deletion (after printing)
            file_registry[filename] = {
                'original_path': event.src_path,
                'target_path': target_path,
                'created_at': time.time()
            }
            print(f"📝 File registered for deletion after printing: {filename}")

        except PermissionError as e:
            print(f"❌ Permission denied: {filename}", e)
        except Exception as e:
            print(f"⚠️ Unexpected error handling {filename}:", e)

    def on_created(self, event):
        """Triggered when a new file appears in the WATCH_FOLDER."""
        self.handle_file(event)

    def on_modified(self, event):
        """Triggered when a file is modified (e.g., Bluetooth overwrites existing file)."""
        self.handle_file(event)

# ==============================
# SOCKET.IO EVENTS
# ==============================
@socketio.on('connect')
def handle_connect():
    print(f"🔌 Socket.IO client connected!")

@socketio.on('disconnect')
def handle_disconnect():
    print(f"🔌 Socket.IO client disconnected!")

# ==============================
# ROUTES
# ==============================
@app.route("/")
def index():
    return "✅ Bluetooth Auto Transfer Flask Server Running"

@app.route("/delete_file", methods=['POST'])
def delete_file():
    """Delete a file after printing is complete."""
    data = request.get_json()
    filename = data.get('filename')

    if not filename:
        return jsonify({'success': False, 'message': 'No filename provided'}), 400

    if filename not in file_registry:
        print(f"⚠️ File not in registry: {filename}")
        return jsonify({'success': False, 'message': 'File not found in registry'}), 404

    file_info = file_registry[filename]
    deleted_files = []

    # Delete both original and target paths
    for filepath in [file_info['original_path'], file_info['target_path']]:
        if os.path.exists(filepath):
            try:
                os.remove(filepath)
                deleted_files.append(filepath)
                print(f"🗑️ Deleted file after printing: {os.path.basename(filepath)}")
            except Exception as e:
                print(f"⚠️ Could not remove {filepath}: {e}")

    # Remove from registry
    del file_registry[filename]

    return jsonify({
        'success': True,
        'message': f'Deleted {len(deleted_files)} file(s)',
        'deleted': deleted_files
    })

# ==============================
# START WATCHER
# ==============================
def start_watcher():
    observer = Observer()
    handler = UploadHandler()
    observer.schedule(handler, WATCH_FOLDER, recursive=False)
    observer.start()
    print(f"👀 Watching folder: {WATCH_FOLDER}")

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()

if __name__ == "__main__":
    socketio.start_background_task(start_watcher)
    socketio.run(app, host="0.0.0.0", port=5001)