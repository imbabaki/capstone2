import RPi.GPIO as GPIO, time
GPIO.setmode(GPIO.BCM)
sensor_pin = 22
GPIO.setup(sensor_pin, GPIO.IN, pull_up_down=GPIO.PUD_OFF)

while True:
    print("Sensor:", GPIO.input(sensor_pin))
    time.sleep(0.2)
