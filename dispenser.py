import RPi.GPIO as GPIO

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

# Your pin setup and logic here

# Finally reset all pins safely
GPIO.cleanup()
