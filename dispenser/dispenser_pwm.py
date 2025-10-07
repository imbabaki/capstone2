import RPi.GPIO as GPIO
import time

# GPIO setup
relay_pin = 17  # GPIO17 (pin 11)
GPIO.setmode(GPIO.BCM)
GPIO.setup(relay_pin, GPIO.OUT)

def run_motor(seconds, power_percent):
    """
    Simulate power control using ON/OFF pulses.
    :param seconds: total run time
    :param power_percent: duty cycle (0-100)
    """
    duty_cycle = power_percent / 100.0
    pulse_time = 0.1 # 100ms pulse resolution
    end_time = time.time() + seconds

    print(f"Running motor {power_percent}% for {seconds} seconds...")

    while time.time() < end_time:
        # ON duration
        GPIO.output(relay_pin, GPIO.HIGH)
        time.sleep(pulse_time * duty_cycle)

        # OFF duration
        GPIO.output(relay_pin, GPIO.LOW)
        time.sleep(pulse_time * (1 - duty_cycle))

    # Ensure motor is OFF
    GPIO.output(relay_pin, GPIO.LOW)
    print("Motor stopped.")

try:
    # Example: 3 
    # seconds at 1% "power" (relay simulation)

    run_motor(3.35, 1)

finally:
    GPIO.cleanup()
