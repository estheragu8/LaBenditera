#!/usr/bin/python3

import sys
sys.path.append('/usr/lib/python3/dist-packages')
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
emailSinBonos = "<div id=':zi' class='Am aiL Al editable LW-avf tS-tW' hidefocus='true' aria-label='Cuerpo del mensaje' g_editable='true' role='textbox' aria-multiline='true' contenteditable='true' tabindex='1' style='direction: ltr; min-height: 226px;' itacorner='6,7:1,1,0,0' spellcheck='false' aria-owns=':11w' aria-controls=':11w' aria-expanded='false' jslog='159835; u014N:xr6bB,cOuCgd,Kr2w4b'>Estimado usuario,<br><br>Esperamos que estés disfrutando de tu experiencia con nosotros en La Benditera. Queremos informarte que las horas de tu bono actual se han agotado, por lo que es necesario adquirir una renovación para continuar accediendo a nuestros servicios.<br><br>Te recomendamos realizar la renovación antes de tu próxima visita para evitar inconvenientes al fichar tu entrada. Si tienes horas negativas, estas se descontarán automáticamente del nuevo bono.<br><br>Para verificar el estado actual de tu bono y proceder con la renovación, puedes hacer clic en el siguiente enlace:<br><a href='https://docs.google.com/spreadsheets/d/e/2PACX-1vTgqoMPI8XeX59VRGVDRZax5MqlcRUdYnwvbVH56ZtI0y0fTVG7Clw6j79-r5vPW4asEix7Huno2u8l/pubhtml?gid=0&amp;single=true'>Verificar y renovar mi bono</a><div><br>Agradecemos tu preferencia y estamos ansiosos por verte pronto.<br><br>Atentamente,<br><br>El Equipo de La Benditera.</div></div>"
emailCasiSinBonos = "<div id=':zi' class='Am aiL Al editable LW-avf tS-tW' hidefocus='true' aria-label='Cuerpo del mensaje' g_editable='true' role='textbox' aria-multiline='true' contenteditable='true' tabindex='1' style='direction: ltr; min-height: 226px;' itacorner='6,7:1,1,0,0' spellcheck='false' aria-owns=':11w' aria-controls=':11w' aria-expanded='false' jslog='159835; u014N:xr6bB,cOuCgd,Kr2w4b'>Estimado usuario,<br><br>Esperamos que estés disfrutando de tu experiencia con nosotros en La Benditera. Queremos informarte que las horas de tu bono actual están a punto de agotarse. <br><br>Te recomendamos realizar la renovación antes de tu próxima visita para evitar inconvenientes al fichar tu entrada. <br><br>Para verificar el estado actual de tu bono y proceder con la renovación, puedes hacer clic en el siguiente enlace:<br><a href='https://docs.google.com/spreadsheets/d/e/2PACX-1vTgqoMPI8XeX59VRGVDRZax5MqlcRUdYnwvbVH56ZtI0y0fTVG7Clw6j79-r5vPW4asEix7Huno2u8l/pubhtml?gid=0&amp;single=true'>Verificar y renovar mi bono</a><div><br>Agradecemos tu preferencia y estamos ansiosos por verte pronto.<br><br>Atentamente,<br><br>El Equipo de La Benditera.</div></div>"

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

def obtenerHoras(_cursor, _email):
    query = "SELECT MinutosDisponibles FROM {0} WHERE Email='{1}'".format("Bono", _email)
    print(query)
    _cursor.execute(query)
    return _cursor.fetchone()

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

def zum(_veces, _tiempo):
    for i in range(0, _veces):
        # Enciende el zumbador
        GPIO.output(pin_zumbador, GPIO.LOW)
        t.sleep(_tiempo)  # Espera 2 segundos

        # Apaga el zumbador
        GPIO.output(pin_zumbador, GPIO.HIGH)
        t.sleep(_tiempo)  # Espera 2 segundos

def mandarMail(_asunto, _cuerpo, _destino):
    import smtplib
    from email.mime.multipart import MIMEMultipart
    from email.mime.text import MIMEText

    # Configuración del servidor SMTP de Gmail
    smtp_server = 'smtp.gmail.com'
    smtp_port = 587  # Puerto SMTP para TLS

    # Tu dirección de correo electrónico de Gmail y contraseña
    gmail_username = 'info.estheragullo@gmail.com'
    gmail_password = 'bljc mgru ncmq wztg'

    # Dirección de correo electrónico del destinatario
    to_email = _destino

    # Construye el mensaje
    msg = MIMEMultipart()
    msg['From'] = gmail_username
    msg['To'] = to_email
    msg['Subject'] = _asunto

    # Cuerpo del correo electrónico
    body = _cuerpo

    # Adjunta el cuerpo del mensaje al objeto MIMEMultipart
    msg.attach(MIMEText(body, 'html'))

    # Inicia una conexión SMTP segura con el servidor de Gmail
    with smtplib.SMTP(smtp_server, smtp_port) as server:
        server.starttls()  # Inicia cifrado TLS
        # Inicia sesión en el servidor SMTP
        server.login(gmail_username, gmail_password)
        # Envía el correo electrónico
        server.send_message(msg)

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
            idTarjeta = reader.read_id()
            if idTarjeta:
                variable = "false"
                try: 
                    nombre = devolverNombreUsuario(cursor_db, idTarjeta)
                    if nombre:
                        email = nombre[0]
                        print (email)
                        numMarca = devolverNumeroMarcajes(cursor_db, email)

                        if numMarca % 2 == 0:
                            tipo = 'entrada'
                        else:
                            tipo = 'salida'

                        secNow = int(t.mktime(datetime.now().timetuple()))

                        marca = secNow
                        marca = t.strftime("%Y-%m-%d %H:%M", t.localtime(marca))
                        try:
                            escribirRegistro(
                                cnx, cursor_db, "Acceso", email, tipo, marca)
                            lcd.clear()
                            if(tipo == 'entrada'):
                                if(horas > 0):
                                    lcd.write_string("Hola")
                                    lcd.cursor_pos = (1, 0)
                                    lcd.write_string("{0}! :)".format(nombre[1]))
                                    zum(3, 0.1)
                                else:
                                    lcd.write_string("{0}".format(nombre[1]))
                                    lcd.cursor_pos = (1, 0)
                                    lcd.write_string("SIN HORAS.")
                                    zum(12, 0.05)
                            else:
                                minutos = obtenerHoras(cursor_db, email)
                                horas = minutos[0]/60
                                if(horas <= 0):
                                    mandarMail("Te estás quedando sin horas en tu bono... :(", emailCasiSinBonos, "info.estheragullo@gmail.com")
                                elif(horas <= 3):
                                    mandarMail("Te has quedado sin horas en tu bono... :(", emailSinBonos, "info.estheragullo@gmail.com")
                                lcd.write_string("Hasta pronto")
                                lcd.cursor_pos = (1, 0)
                                lcd.write_string("{0}! :) {1}".format(nombre[1], horas))
                                zum(1, 1)
                            
                            sleep(2)
                        except:
                            lcd.clear()
                            lcd.write_string("Usuario no registrado 1")
                            sleep(1)
                except:
                    escribirTarjeta(
                            cnx, cursor_db, idTarjeta)
                    zum(1, 0.5)
                    lcd.clear()
                    lcd.write_string("Tarjeta lista.")
                    sleep(1)
        else:
            sleep(0.5)
            if(variable == "false"):
                variable = "true"
                mensajeBienvenida()


if __name__ == '__main__':
    main()
