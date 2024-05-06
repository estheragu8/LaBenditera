#!/usr/bin/python

import RPi.GPIO as GPIO
from mfrc522 import SimpleMFRC522
from time import sleep
from time import mktime
from datetime import datetime
import time as t
import mysql.connector
from RPLCD.i2c import CharLCD

PCF8574_address = 0x27
PCF8574A_address = 0x3F

# Define el número de pin que estás utilizando para controlar el zumbador
pin_zumbador = 4

# Configura el modo del pin como salida
GPIO.setmode(GPIO.BCM)
GPIO.setup(pin_zumbador, GPIO.OUT)

try:
    lcd = CharLCD('PCF8574', PCF8574_address)
except:
    try:
        lcd = CharLCD('PCF8574', PCF8574A_address)
    except:
        print("Error del adaptador I2C ¡")

def escribirRegistro(_cnx, _cursor, _tabla, _email, _tipo, _marca):
    query = "INSERT INTO Acceso (UsuarioEmail,MarcaTiempo,Tipo) VALUES ('{0}','{1}','{2}')".format(
        _email, _marca, _tipo)
    _cursor.execute(query)
    _cnx.commit()

def escribirTarjeta(_cnx, _cursor, _idTarjeta):
    query2 = "DELETE FROM Tarjeta WHERE 1"
    query = "INSERT INTO Tarjeta (Id) VALUES ('{0}')".format(
        _idTarjeta)
    _cursor.execute(query2)
    _cursor.execute(query)
    _cnx.commit()

def mostrarDatos(_cursor, _tabla):
    query = "SELECT * FROM {0}".format(_tabla)
    _cursor.execute(query)
    if _tabla == "usuario":
        for (id, nombre, apellido) in _cursor:
            print(id, nombre, apellido)

def devolverNombreUsuario(_cursor, _id):
    # print("devuelve el email del usuario asociado a la tarjeta")
    query = "SELECT Email, Nombre FROM Usuario WHERE IdTarjeta={0}".format(_id)
    _cursor.execute(query)
    return _cursor.fetchone()


def devolverNumeroMarcajes(_cursor, _email):
    query = "SELECT COUNT(UsuarioEmail) FROM Acceso WHERE UsuarioEmail='{0}' GROUP BY UsuarioEmail".format(
        _email)
    _cursor.execute(query)

    try:
        return _cursor.fetchall()[0][0]
    except IndexError:
        return 0


def devolverUltimoMarcaje(_cursor, _email):
    try:
        # print("devuelve el ultimo fichaje del usuario {0}".format(_id))
        query = "SELECT MAX(MarcaTiempo) FROM Acceso WHERE UsuarioEmail='{0}' GROUP BY UsuarioEmail".format(
            _email)
        _cursor.execute(query)
        return _cursor.fetchone()
    except:
        return t.gmtime(0)


def mensajeBienvenida():
    lcd.clear()
    lcd.write_string("Esperando marca...")
    print("Esperando marca...")

def zum(_tiempo):
    # Enciende el zumbador
    GPIO.output(pin_zumbador, GPIO.LOW)
    print("Zumbador encendido")
    t.sleep(_tiempo)  # Espera 2 segundos

    # Apaga el zumbador
    GPIO.output(pin_zumbador, GPIO.HIGH)
    print("Zumbador apagado")
    t.sleep(_tiempo)  # Espera 2 segundos


def main():
    # setup()
    # configurar hardware
    reader = SimpleMFRC522()

    cnx = mysql.connector.connect(
        host="localhost", user="phpmyadmin", passwd="root", database="LaBenditera")
    cursor_db = cnx.cursor()
    variable = "false"
    while (True):
        if(variable == "false"):
            variable = "true"
            mensajeBienvenida()
        if reader.read_id_no_block():
            variable = "false"
            idTarjeta = reader.read_id()
            if idTarjeta:
                escribirTarjeta(
                            cnx, cursor_db, idTarjeta)
                nombre = devolverNombreUsuario(cursor_db, idTarjeta)
                if nombre:
                    email = nombre[0]
                    print (email)
                    numMarca = devolverNumeroMarcajes(cursor_db, email)

                    if numMarca % 2 == 0:
                        tipo = 'entrada'
                    else:
                        tipo = 'salida'

                    ultMarca = devolverUltimoMarcaje(cursor_db, email)
                    print(ultMarca)
                    try:
                        secMarca = int(t.mktime(ultMarca[0].timetuple()))
                    except:
                        secMarca = 0

                    secNow = int(t.mktime(datetime.now().timetuple()))

                    #if secNow - secMarca < 15*60:  # tiempo minimo 15 minutos
                     #   marca = secMarca + 15*60
                    #else:

                    marca = secNow
                    marca = t.strftime("%Y-%m-%d %H:%M", t.localtime(marca))
                    try:
                        escribirRegistro(
                            cnx, cursor_db, "Acceso", email, tipo, marca)
                        print("registrando...")
                        lcd.clear()
                        if(tipo == 'entrada'):
                            lcd.write_string("Hola")
                            lcd.cursor_pos = (1, 0)
                            lcd.write_string("{0}! :)".format(nombre[1]))
                            zum(0.1)
                            zum(0.1)
                            zum(0.1)
                        else:
                            lcd.write_string("Hasta pronto")
                            lcd.cursor_pos = (1, 0)
                            lcd.write_string("{0}! :)".format(nombre[1]))
                            zum(1)
                        
                        sleep(2)
                    except:
                        lcd.clear()
                        lcd.write_string("Usuario no registrado 1")
                        sleep(1)
            else:
                print("Usuario no registrado")
                sleep(1)
        else:
            sleep(0.5)
            mensajeBienvenida()


if __name__ == '__main__':
    main()
