from flask import Flask, Response, jsonify
from flask_cors import CORS
import pyudev
import threading
import os
import json
import time
import subprocess

app = Flask(__name__)
CORS(app)

usb_event = {"status": "waiting"}

# 🔄 Monitor USB devices
def monitor_usb():
    global usb_event
    context = pyudev.Context()
    monitor = pyudev.Monitor.from_netlink(context)
    monitor.filter_by(subsystem='block')

    for device in iter(monitor.poll, None):
        if device.action == 'add' and device.device_type == 'partition':
            dev_path = device.device_node
            label = device.get('ID_FS_LABEL', 'USBDrive')
            mount_path = f"/mnt/{label}"
            os.makedirs(mount_path, exist_ok=True)

            # ✅ Check if device already mounted
            result = subprocess.run(['lsblk', '-o', 'NAME,MOUNTPOINT', '-nr'], capture_output=True, text=True)
            already_mounted = False
            for line in result.stdout.splitlines():
                if dev_path.split('/')[-1] in line and '/media/' in line:
                    mount_path = line.split()[-1]
                    already_mounted = True
                    print(f"📂 Found already mounted USB at: {mount_path}")
                    break

            # ✅ Try to mount only if not already mounted
            if not already_mounted:
                try:
                    subprocess.run(["sudo", "mount", dev_path, mount_path], check=True)
                    print(f"💾 USB Inserted: {dev_path} Label: {label} Mounted at: {mount_path}")
                except subprocess.CalledProcessError:
                    print(f"❌ Failed to mount {dev_path}")
                    continue

            # 🔍 Scan for PDF files
            try:
                pdf_files = [f for f in os.listdir(mount_path) if f.lower().endswith(".pdf")]
                if pdf_files:
                    files_info = []
                    for f in pdf_files:
                        file_path = os.path.join(mount_path, f)
                        files_info.append({
                            "name": f,
                            "path": file_path,
                            "pages": 1
                        })
                    usb_event = {
                        "status": "inserted",
                        "files": files_info,
                        "label": label
                    }
                    print(f"✅ Found PDF files: {pdf_files}")
                else:
                    usb_event = {"status": "inserted", "files": [], "label": label}
                    print("⚠️ No PDF files found on USB")
            except Exception as e:
                print(f"⚠️ Error reading USB files: {e}")

        elif device.action == 'remove' and device.device_type == 'partition':
            usb_event = {"status": "removed"}
            print(f"❌ USB Removed: {device.device_node}")

# 🔁 Real-time event stream for Laravel
@app.route('/usb/stream')
def stream():
    def event_stream():
        last_state = None
        while True:
            global usb_event
            if usb_event != last_state:
                yield f"data: {json.dumps(usb_event)}\n\n"
                last_state = dict(usb_event)
            time.sleep(1)
    return Response(event_stream(), content_type='text/event-stream')

# Manual check
@app.route('/usb/status')
def usb_status():
    return jsonify(usb_event)

# -----------------------------
# RUN SERVER
# -----------------------------
if __name__ == '__main__':
    print("🚀 USB server running on http://0.0.0.0:5004")
    t = threading.Thread(target=monitor_usb, daemon=True)
    t.start()
    app.run(host='0.0.0.0', port=5004, debug=False)
