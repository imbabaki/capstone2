import RPi.GPIO as GPIO
import time

# GPIO setup
relay_pin =24
# GPIO18 (pin 12 on Pi 4 Model B)
GPIO.setmode(GPIO.BCM)
GPIO.setup(relay_pin, GPIO.OUT)

motor_running = False  # track motor state

try:
    print("Press Enter to toggle the motor ON/OFF. Press Ctrl+C to exit.")

    while True:
        input()  # wait for Enter key
        motor_running = not motor_running  # toggle state

        if motor_running:
            GPIO.output(relay_pin, GPIO.HIGH)
            print("Motor started.")
        else:
            GPIO.output(relay_pin, GPIO.LOW)
            print("Motor stopped.")

finally:
    GPIO.output(relay_pin, GPIO.LOW)
    GPIO.cleanup()
