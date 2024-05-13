#!/usr/bin/env python

import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522
from RPLCD.i2c import CharLCD
from time import sleep

# crear el objeto lector
reader = SimpleMFRC522()

# crear el objeto LCD
PCF8574_address = 0x27
PCF8574A_address = 0x3f

try:
    lcd = CharLCD('PCF8574', PCF8574_address)
except:
    try:
        lcd = CharLCD('PCF8574', PCF8574A_address)
    except:
        exit(1)

while (True):
    # leer la tarjeta RFID
    # try:
    id, text = reader.read()
    idt = reader.read_id()
    # finally:
    #    GPIO.cleanup()

    # escribir mensaje en la LCD
    lcd.cursor_pos = (0, 0)
    lcd.write_string("hola "+text)
    print(id)
    print("new " + hex(id)[:-2])
    print(int(hex(id)[:-2], 16))
    print(int("0x037c45bd", 16))
    print(hex(id))
    print(len(hex(id)))
    print(idt)
    print(hex(idt))

    sleep(3)
    # GPIO.cleanup()
