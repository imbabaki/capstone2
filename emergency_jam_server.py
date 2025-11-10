from flask import Flask, jsonify
import RPi.GPIO as GPIO
import time
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# GPIO Setup
JAM_BUTTON_PIN = 4  # Emergency button to clear paper jam

GPIO.setmode(GPIO.BCM)
GPIO.setup(JAM_BUTTON_PIN, GPIO.OUT)
GPIO.output(JAM_BUTTON_PIN, GPIO.HIGH)  # Default OFF (assuming active-low relay)

@app.route('/emergency/clear-jam', methods=['POST'])
def clear_jam():
    """
    Emergency button to clear paper jam.
    Activates GPIO4 relay for 1 second to help clear jammed paper.
    """
    try:
        print("🚨 Emergency jam clear activated!")

        # Activate relay (turn ON)
        GPIO.output(JAM_BUTTON_PIN, GPIO.LOW)  # Active LOW
        time.sleep(1)  # Keep active for 1 second
        GPIO.output(JAM_BUTTON_PIN, GPIO.HIGH)  # Turn OFF

        print("✅ Jam clear completed")

        return jsonify({
            'success': True,
            'message': 'Emergency jam clear activated for 1 second'
        })
    except Exception as e:
        print(f"❌ Error clearing jam: {e}")
        return jsonify({
            'success': False,
            'message': str(e)
        }), 500

@app.route('/emergency/status', methods=['GET'])
def status():
    """Check if emergency jam server is running"""
    return jsonify({
        'success': True,
        'message': 'Emergency jam server is running'
    })

if __name__ == "__main__":
    try:
        print("🚀 Emergency Jam Server Running on 0.0.0.0:5006")
        app.run(host="0.0.0.0", port=5006)
    finally:
        GPIO.cleanup()
