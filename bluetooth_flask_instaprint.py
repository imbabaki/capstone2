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
WATCH_FOLDER = "/home/instaprint/Downloads"  # folder to watch for new files
LARAVEL_UPLOAD_FOLDER = "/var/www/html/laravel/public/storage/uploads"  # Laravel storage
REDIRECT_URL = "http://192.168.4.1:8000/bluetooth/preview/{filename}"  # ✅ Fixed route
DELETE_DELAY = 300  # ✅ Changed to 5 minutes instead of 60 seconds
PRINTER_NAME = "EPSON_L120_Series"  # CUPS USB printer name

# Flask + Socket.IO
app = Flask(__name__)
socketio = SocketIO(app, cors_allowed_origins="*")

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

        # ✅ Wait for file to be fully written
        time.sleep(2)  # Give Bluetooth transfer time to complete
        
        # Ensure Laravel storage exists
        os.makedirs(LARAVEL_UPLOAD_FOLDER, exist_ok=True)
        target_path = os.path.join(LARAVEL_UPLOAD_FOLDER, filename)

        try:
            # ✅ Wait for stable file size
            prev_size = -1
            stable_count = 0
            max_attempts = 10
            
            for i in range(max_attempts):
                if not os.path.exists(event.src_path):
                    print(f"⚠️ File disappeared: {filename}")
                    return
                    
                curr_size = os.path.getsize(event.src_path)
                if curr_size == prev_size and curr_size > 0:
                    stable_count += 1
                    if stable_count >= 2:  # Stable for 2 checks
                        break
                else:
                    stable_count = 0
                prev_size = curr_size
                time.sleep(0.5)
            
            # Copy file to Laravel storage
            shutil.copy(event.src_path, target_path)
            print(f"📂 Copied to Laravel storage: {target_path}")

            # ✅ DON'T auto-print via Bluetooth - let user configure first
            # The print will happen after payment in the web interface
            
            # 🔀 Auto redirect logic
            redirect_link = REDIRECT_URL.format(filename=filename)
            print(f"🔗 Redirecting to: {redirect_link}")

            # Emit new file to any connected clients
            socketio.emit("new_file", {
                "filename": filename,
                "redirect": redirect_link
            })

            # ✅ Schedule deletion after delay (both locations)
            socketio.start_background_task(
                self.delete_files_after_delay,
                event.src_path,
                target_path,
                DELETE_DELAY
            )

        except PermissionError as e:
            print(f"❌ Permission denied: {filename}", e)
        except Exception as e:
            print(f"⚠️ Unexpected error handling {filename}:", e)

    def delete_files_after_delay(self, original_path, target_path, delay):
        """Remove files after a delay."""
        print(f"⏳ Files will be removed in {delay}s")
        time.sleep(delay)
        
        for filepath in [original_path, target_path]:
            if os.path.exists(filepath):
                try:
                    os.remove(filepath)
                    print(f"🗑️ Removed file: {os.path.basename(filepath)}")
                except Exception as e:
                    print(f"⚠️ Could not remove {filepath}: {e}")

# ==============================
# ROUTES
# ==============================
@app.route("/")
def index():
    return "✅ Bluetooth Auto Transfer Flask Server Running"

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