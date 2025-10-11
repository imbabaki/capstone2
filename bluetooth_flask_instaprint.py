import os
import shutil
import time
from flask import Flask
from flask_socketio import SocketIO
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
import eventlet

# ✅ Patch for async support
eventlet.monkey_patch()

# CONFIG
WATCH_FOLDER = "/home/instaprint/uploads"
LARAVEL_UPLOAD_FOLDER = "/var/www/html/laravel/public/storage/uploads"
REDIRECT_URL = "http://192.168.4.1:8000/upload/edit/{filename}"
DELETE_DELAY = 60 # seconds after which the file is removed

# Flask + Socket.IO
app = Flask(__name__)
socketio = SocketIO(app, cors_allowed_origins="*")  # Allow all origins

# FILE WATCHER
class UploadHandler(FileSystemEventHandler):
    def on_created(self, event):
        if not event.is_directory:
            filename = os.path.basename(event.src_path)
            print(f"✅ New file detected: {filename}")

            # Ensure Laravel storage exists
            os.makedirs(LARAVEL_UPLOAD_FOLDER, exist_ok=True)
            target_path = os.path.join(LARAVEL_UPLOAD_FOLDER, filename)

            try:
                # Copy file to Laravel storage
                shutil.copy(event.src_path, target_path)
                print(f"📂 Copied to Laravel storage: {target_path}")

                # Generate redirect URL
                redirect_link = REDIRECT_URL.format(filename=filename)
                print(f"🔀 Emitting: {filename} -> {redirect_link}")

                # Emit new file to clients
                socketio.emit("new_file", {
                    "filename": filename,
                    "redirect": redirect_link
                })

                # ✅ Schedule deletion after delay
                socketio.start_background_task(self.delete_file_after_delay, target_path, DELETE_DELAY)

            except PermissionError as e:
                print(f"❌ Permission denied: {filename}", e)

    def delete_file_after_delay(self, filepath, delay):
        """Remove file after a delay so the next file can be sent."""
        print(f"⏳ File {os.path.basename(filepath)} will be removed in {delay}s")
        time.sleep(delay)
        if os.path.exists(filepath):
            os.remove(filepath)
            print(f"🗑️ Removed file: {os.path.basename(filepath)}")

# ROUTE TO TEST SERVER
@app.route("/")
def index():
    return "Bluetooth Flask Server Running"

# Start watcher in background
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
