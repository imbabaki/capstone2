from gpiozero import Button
from gpiozero.pins.pigpio import PiGPIOFactory
from flask import Flask, Response, jsonify, request
from flask_cors import CORS
import json
import time
import threading
import sys
import os  

# CONFIGURATION
# -----------------------------
PULSE_TIMEOUT = float(os.environ.get("PULSE_TIMEOUT", 0.45))  # seconds after last pulse => coin done
PULSE_TO_VALUE = {1: 1, 5: 5, 10: 10, 20: 20}  # map pulse counts -> pesos

# -----------------------------
# GPIO SETUP
# -----------------------------
factory = PiGPIOFactory()
COIN_PIN = 27  # GPIO pin from your coin slot
coin_input = Button(COIN_PIN, pull_up=True, pin_factory=factory)

# -----------------------------
# GLOBAL VARIABLES
# -----------------------------
coin_value = 1.00   # Each pulse = ₱1
total_amount = 0.0
lock = threading.Lock()

# -----------------------------
# FLASK APP SETUP
# -----------------------------
app = Flask(__name__)
CORS(app, resources={r"/coin/*": {"origins": "*"}})

# -----------------------------
# COIN DETECTION HANDLER
# -----------------------------
def coin_detected():
    global total_amount
    with lock:
        total_amount += coin_value
        print(f"💰 Pulse detected! Total now: ₱{total_amount:.2f}")

coin_input.when_pressed = coin_detected

# -----------------------------
# ROUTES
# -----------------------------

# Get total inserted coins
@app.route('/coin/total')
def get_total():
    with lock:
        return jsonify({"total": total_amount})

# Reset total amount
@app.route('/coin/reset', methods=['POST'])
def reset_total():
    global total_amount
    with lock:
        total_amount = 0.0
    print("🔄 Total reset to ₱0.00")
    return jsonify({"status": "ok", "total": total_amount})

# Real-time coin stream (SSE)
@app.route('/coin/stream')
def stream():
    def event_stream():
        last_sent = None
        while True:
            time.sleep(0.5)
            with lock:
                data = {"total": total_amount}
            payload = json.dumps(data)
            if payload != last_sent:
                last_sent = payload
                yield f"data: {payload}\n\n"
                sys.stdout.flush()  # ✅ ensures instant push

    headers = {
        "Cache-Control": "no-cache",
        "Content-Type": "text/event-stream",
        "Access-Control-Allow-Origin": "*",
        "Connection": "keep-alive"
    }

    return Response(event_stream(), headers=headers)

# -----------------------------
# RUN SERVER
# -----------------------------
if __name__ == '__main__':
    print("🚀 Coin slot server running at http://0.0.0.0:5003")
    app.run(host='0.0.0.0', port=5003, threaded=True, use_reloader=False)
