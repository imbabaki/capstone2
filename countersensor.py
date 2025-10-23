import RPi.GPIO as GPIO
import time

# GPIO setup
sensor_pin = 18 
GPIO.setmode(GPIO.BCM)
GPIO.setup(sensor_pin, GPIO.IN)

paper_count = 0
paper_detected = False

print("📄 IR Paper Counter Started")
print("Sensor ready... Blocking = LOW, Clear = HIGH")
print("Press Ctrl+C to stop.\n")

try:
    while True:
        state = GPIO.input(sensor_pin)

        # When beam is blocked (LOW)
        if state == GPIO.LOW and not paper_detected:
            paper_detected = True
            print("Paper detected (blocking beam)...")

        # When beam becomes clear again (HIGH)
        elif state == GPIO.HIGH and paper_detected:
            paper_count += 1
            paper_detected = False
            print(f"✅ Paper passed! Total count: {paper_count}")

        time.sleep(0.02)  # debounce delay (20ms)

except KeyboardInterrupt:
    print("\nCounting stopped by user.")
finally:
    GPIO.cleanup()
