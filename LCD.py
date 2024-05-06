#!/usr/bin/env python

from RPLCD.i2c import CharLCD

# Crear el LCD
PCF8574_address = 0x27
PCF8574A_address = 0x3F
try:
    lcd = CharLCD('PCF8574', PCF8574_address)
except:
    try:
        lcd = CharLCD('PCF8574', PCF8574A_address)
    except:
        print("Error del adaptador I2C ¡")
        exit(1)

lcd.write_string("hola")
