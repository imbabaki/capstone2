from flask import Flask, request, jsonify
import RPi.GPIO as GPIO
import time
import threading
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# ----------------------
# GPIO Setup (Base)
# ----------------------
GPIO.setmode(GPIO.BCM)

sensor_pin = 22  # IR Paper Sensor
relay_pins = {
    "A4": 5,
    "Letter": 24,
    "Legal": 16
}

GPIO.setup(sensor_pin, GPIO.IN)  # ✅ no pull_up_down (same as working version)

# ----------------------
# Global Variables
# ----------------------
motor_running = False
target_count = 0
paper_count = 0
paper_detected = False

# ----------------------
# Helper: Parse Pages
# ----------------------
def parse_pages(pages_str):
    if not pages_str or pages_str.strip() == "":
        return 1
    total = 0
    for part in pages_str.split(","):
        part = part.strip()
        if "-" in part:
            start, end = map(int, part.split("-"))
            total += (end - start + 1)
        else:
            total += 1
    return total

# ----------------------
# Dispense Function
# ----------------------
def dispense_papers(paper_size, count):
    global motor_running, paper_count, paper_detected, target_count

    relay_pin = relay_pins.get(paper_size, relay_pins["A4"])

    # ✅ Initialize only the selected relay
    GPIO.setup(relay_pin, GPIO.OUT)
    GPIO.output(relay_pin, GPIO.HIGH)  # Motor OFF standby (active-low)

    motor_running = True
    target_count = count
    paper_count = 0
    paper_detected = False

    print(f"🟢 Dispensing {count} papers ({paper_size}) using GPIO {relay_pin}...")

    GPIO.output(relay_pin, GPIO.LOW)  # Start motor (active-low)

    try:
        while motor_running:
            state = GPIO.input(sensor_pin)
            print(f"Sensor: {state}, paper_detected={paper_detected}, count={paper_count}/{target_count}")

            # same logic as working code — LOW = blocked
            if state == GPIO.LOW and not paper_detected:
                paper_detected = True

            elif state == GPIO.HIGH and paper_detected:
                paper_detected = False
                paper_count += 1
                print(f"✅ Counted: {paper_count}/{target_count}")

                if paper_count >= target_count:
                    print("🛑 Target reached, stopping motor.")
                    motor_running = False
                    GPIO.output(relay_pin, GPIO.HIGH)  # Stop motor
                    break

            time.sleep(0.02)

    finally:
        GPIO.output(relay_pin, GPIO.HIGH)  # Ensure motor OFF
        motor_running = False
        print(f"Motor (GPIO {relay_pin}) stopped. Dispensing complete.")
        GPIO.cleanup(relay_pin)  # ✅ release that relay pin safely

# ----------------------
# Flask Route
# ----------------------
@app.route('/start', methods=['POST'])
def start_dispense():
    data = request.get_json()
    paper_size = data.get("paper_size", "A4")
    copies = int(data.get("copies", 1))
    pages_str = data.get("pages", "1")

    num_pages = parse_pages(pages_str)
    target_papers = copies * num_pages

    print(f"📥 Request: paper_size={paper_size}, copies={copies}, pages={pages_str}")
    print(f"🎯 Target papers to dispense: {target_papers}")

    threading.Thread(target=dispense_papers, args=(paper_size, target_papers)).start()

    return jsonify({
        "status": "started",
        "paper_size": paper_size,
        "copies": copies,
        "pages": pages_str,
        "target_papers": target_papers
    })

# ----------------------
# Run Flask
# ----------------------
if __name__ == "__main__":
    try:
        print("🚀 Dispenser Server Running on 0.0.0.0:5005 (standby)")
        app.run(host="0.0.0.0", port=5005)
    finally:
        GPIO.cleanup()
