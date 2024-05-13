import RPi.GPIO as GPIO
import time

# Define el número de pin que estás utilizando para controlar el zumbador
pin_zumbador = 4

# Configura el modo del pin como salida
GPIO.setmode(GPIO.BCM)
GPIO.setup(pin_zumbador, GPIO.OUT)

try:
    # Enciende el zumbador
    GPIO.output(pin_zumbador, GPIO.LOW)
    print("Zumbador encendido")
    time.sleep(2)  # Espera 2 segundos

    # Apaga el zumbador
    GPIO.output(pin_zumbador, GPIO.HIGH)
    print("Zumbador apagado")
    time.sleep(1)  # Espera 1 segundo

except KeyboardInterrupt:
    # Maneja la interrupción de teclado (Ctrl+C)
    GPIO.cleanup()
