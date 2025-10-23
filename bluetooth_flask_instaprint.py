import os
import shutil
import time
import subprocess
from flask import Flask
from flask_socketio import SocketIO
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
import eventlet

# Patch for async support
eventlet.monkey_patch()

# CONFIG
WATCH_FOLDER = "/home/instaprint/uploads"  # folder to watch for new files
LARAVEL_UPLOAD_FOLDER = "/var/www/html/laravel/public/storage/uploads"  # Laravel storage
REDIRECT_URL = "http://192.168.4.1:8000/upload/edit/{filename}"  # Laravel route
DELETE_DELAY = 60  # seconds after which the file is removed
PRINTER_NAME = "EPSON_L120_Series"  # CUPS USB printer name

# Flask + Socket.IO
app = Flask(__name__)
socketio = SocketIO(app, cors_allowed_origins="*")  # Allow all origins

# ==============================
# FILE WATCHER
# ==============================
class UploadHandler(FileSystemEventHandler):
    def on_created(self, event):
        """Triggered when a new file appears in the WATCH_FOLDER."""
        if event.is_directory:
            return

        filename = os.path.basename(event.src_path)
        print(f"✅ New file detected: {filename}")

        # Ensure Laravel storage exists
        os.makedirs(LARAVEL_UPLOAD_FOLDER, exist_ok=True)
        target_path = os.path.join(LARAVEL_UPLOAD_FOLDER, filename)

        try:
            # Copy file to Laravel storage
            shutil.copy(event.src_path, target_path)
            print(f"📂 Copied to Laravel storage: {target_path}")

            # ✅ Automatically print via USB printer
            print(f"🖨️ Printing file: {filename}")
            result = subprocess.run(
                ["lp", "-d", PRINTER_NAME, target_path],
                capture_output=True,
                text=True
            )

            if result.returncode == 0:
                print(f"✅ Print job sent successfully: {filename}")
            else:
                print(f"❌ Print job failed: {result.stderr}")

            # 🔀 Auto redirect logic
            redirect_link = REDIRECT_URL.format(filename=filename)
            print(f"🔗 Redirecting to: {redirect_link}")

            # Emit new file to any connected clients (optional)
            socketio.emit("new_file", {
                "filename": filename,
                "redirect": redirect_link
            })

            # ✅ Schedule deletion after delay
            socketio.start_background_task(
                self.delete_file_after_delay,
                target_path,
                DELETE_DELAY
            )

        except PermissionError as e:
            print(f"❌ Permission denied: {filename}", e)
        except Exception as e:
            print(f"⚠️ Unexpected error handling {filename}:", e)

    def delete_file_after_delay(self, filepath, delay):
        """Remove file after a delay."""
        print(f"⏳ File {os.path.basename(filepath)} will be removed in {delay}s")
        time.sleep(delay)
        if os.path.exists(filepath):
            os.remove(filepath)
            print(f"🗑️ Removed file: {os.path.basename(filepath)}")

# ==============================
# ROUTES
# ==============================
@app.route("/")
def index():
    return "✅ USB Auto Print Flask Server Running"

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
