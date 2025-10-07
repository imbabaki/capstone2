import os
import time
from flask import Flask
from flask_socketio import SocketIO
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler

# CONFIG
WATCH_FOLDER = "/home/instaprint/uploads"
REDIRECT_URL = "http://192.168.4.1:8000/upload/edit/{filename}"  # ✅ corrected URL structure

app = Flask(__name__)
socketio = SocketIO(app, cors_allowed_origins="*")

# FILE WATCHER
class UploadHandler(FileSystemEventHandler):
    def on_created(self, event):
        if not event.is_directory:
            filename = os.path.basename(event.src_path)
            print(f"✅ New file detected: {filename}")

            # ✅ Generate redirect URL
            redirect_link = REDIRECT_URL.format(filename=filename)
            print(f"🔀 Redirect URL: {redirect_link}")

            # ✅ Emit new file with redirect info
            socketio.emit("new_file", {
                "filename": filename,
                "redirect": redirect_link
            })

# ROUTE TO TEST SERVER
@app.route("/")
def index():
    return "Bluetooth Flask Server Running"

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
    socketio.run(app, host="0.0.0.0", port=5000)
