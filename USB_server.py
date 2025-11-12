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

# 🔍 Scan for existing mounted USB drives
def scan_existing_usb():
    global usb_event
    print("🔍 Scanning for existing USB drives...")

    # Check common mount points
    possible_paths = [
        "/media/instaprint",
        "/mnt/usb",
        "/mnt/USBDrive"
    ]

    # Also check /mnt/* directories
    try:
        for item in os.listdir("/mnt"):
            path = os.path.join("/mnt", item)
            if os.path.isdir(path):
                possible_paths.append(path)
    except Exception as e:
        print(f"⚠️ Error scanning /mnt: {e}")

    # Scan each possible path
    for mount_path in possible_paths:
        if not os.path.exists(mount_path):
            continue

        try:
            # Try to list files
            files = os.listdir(mount_path)
            pdf_files = [f for f in files if f.lower().endswith(".pdf")]

            if pdf_files:
                print(f"✅ Found existing USB at {mount_path} with {len(pdf_files)} PDF files")
                files_info = []
                for f in pdf_files:
                    file_path = os.path.join(mount_path, f)
                    page_count = 1  # fallback default
                    try:
                        import PyPDF2
                        with open(file_path, 'rb') as pdf_file:
                            pdf_reader = PyPDF2.PdfReader(pdf_file)
                            page_count = len(pdf_reader.pages)
                            print(f"📄 {f}: {page_count} pages")
                    except Exception as e:
                        print(f"⚠️ Could not count pages for {f}: {e}, defaulting to 1")
                        page_count = 1

                    files_info.append({
                        "name": f,
                        "path": file_path,
                        "pages": page_count
                    })

                usb_event = {
                    "status": "inserted",
                    "files": files_info,
                    "label": os.path.basename(mount_path)
                }
                return  # Found USB, stop scanning
        except PermissionError:
            continue
        except Exception as e:
            print(f"⚠️ Error checking {mount_path}: {e}")

    print("ℹ️ No USB drive found with PDF files")

# 🔄 Monitor USB devices
def monitor_usb():
    global usb_event

    # First scan for existing USB drives
    scan_existing_usb()

    context = pyudev.Context()
    monitor = pyudev.Monitor.from_netlink(context)
    monitor.filter_by(subsystem='block')

    for device in iter(monitor.poll, None):
        if device.action == 'add' and device.device_type == 'partition':
            dev_path = device.device_node
            label = device.get('ID_FS_LABEL', 'USBDrive')
            mount_path = f"/mnt/{label}"
            os.makedirs(mount_path, exist_ok=True)

            # ✅ Check if device already mounted (by system automounter)
            result = subprocess.run(['lsblk', '-o', 'NAME,MOUNTPOINT', '-nr'], capture_output=True, text=True)
            already_mounted = False
            for line in result.stdout.splitlines():
                parts = line.split()
                if len(parts) >= 2 and dev_path.split('/')[-1] in parts[0]:
                    if parts[-1].startswith('/'):  # Has a mount point
                        mount_path = parts[-1]
                        already_mounted = True
                        print(f"📂 Found auto-mounted USB at: {mount_path}")
                        break

            # ✅ Try to mount only if not already mounted
            if not already_mounted:
                try:
                    subprocess.run(["sudo", "mount", dev_path, mount_path], check=True)
                    print(f"💾 USB Inserted: {dev_path} Label: {label} Mounted at: {mount_path}")
                except subprocess.CalledProcessError as e:
                    print(f"❌ Failed to mount {dev_path}: {e}")
                    # Try to find if it's mounted elsewhere
                    time.sleep(2)
                    scan_existing_usb()
                    continue

            # 🔍 Scan for PDF files
            try:
                pdf_files = [f for f in os.listdir(mount_path) if f.lower().endswith(".pdf")]
                if pdf_files:
                    files_info = []
                    for f in pdf_files:
                        file_path = os.path.join(mount_path, f)
                        # Count actual PDF pages using PyPDF2
                        page_count = 1  # fallback default
                        try:
                            import PyPDF2
                            with open(file_path, 'rb') as pdf_file:
                                pdf_reader = PyPDF2.PdfReader(pdf_file)
                                page_count = len(pdf_reader.pages)
                                print(f"📄 {f}: {page_count} pages")
                        except Exception as e:
                            print(f"⚠️ Could not count pages for {f}: {e}, defaulting to 1")
                            page_count = 1

                        files_info.append({
                            "name": f,
                            "path": file_path,
                            "pages": page_count
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

# Manual rescan
@app.route('/usb/rescan')
def usb_rescan():
    scan_existing_usb()
    return jsonify(usb_event)

# 🔁 Periodic rescan for auto-mounted drives
def periodic_rescan():
    while True:
        time.sleep(5)  # Rescan every 5 seconds
        # Only rescan if no USB is currently detected
        if usb_event.get("status") == "waiting":
            scan_existing_usb()

# -----------------------------
# RUN SERVER
# -----------------------------
if __name__ == '__main__':
    print("🚀 USB server running on http://0.0.0.0:5004")
    # Start USB monitor thread
    t1 = threading.Thread(target=monitor_usb, daemon=True)
    t1.start()
    # Start periodic rescan thread
    t2 = threading.Thread(target=periodic_rescan, daemon=True)
    t2.start()
    app.run(host='0.0.0.0', port=5004, debug=False)
