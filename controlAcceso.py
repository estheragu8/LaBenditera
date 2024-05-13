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

#configurando LCD
try:
    lcd = CharLCD('PCF8574', PCF8574_address)
except:
    try:
        lcd = CharLCD('PCF8574', PCF8574A_address)
    except:
        print("Error del adaptador I2C ¡")

#Almacenar en la base de datos el registro del usuario
def escribirRegistro(_cnx, _cursor, _email, _tipo, _marca):
    query = "INSERT INTO Acceso (UsuarioEmail,MarcaTiempo,Tipo) VALUES ('{0}','{1}','{2}')".format(
        _email, _marca, _tipo)
    _cursor.execute(query)
    _cnx.commit()

#La tarjeta se almacenará en la base de datos para poder asignarla a un usuario facilmente desde la interfaz web
def escribirTarjeta(_cnx, _cursor, _idTarjeta):
    query2 = "DELETE FROM Tarjeta WHERE 1"
    query = "INSERT INTO Tarjeta (Id) VALUES ('{0}')".format(
        _idTarjeta)
    _cursor.execute(query2)
    _cursor.execute(query)
    _cnx.commit()

#Obtenemos las horas del usuario que ha pasado la tarjeta
def obtenerHoras(_cursor, _email):
    query = "SELECT MinutosDisponibles, MinutosBono Horas FROM {0} WHERE Email='{1}'".format("Bono", _email)
    _cursor.execute(query)
    return _cursor.fetchone()

#Devuelve el email y el nombre del usuario asociado a la tarjeta
def devolverUsuario(_cursor, _id):
    query = "SELECT Email, Nombre FROM Usuario WHERE IdTarjeta={0}".format(_id)
    _cursor.execute(query)
    return _cursor.fetchone()

#Devuelve numero de marcajes para comprobar si es una entrada o una salida
def devolverNumeroMarcajes(_cursor, _email):
    query = "SELECT COUNT(UsuarioEmail) FROM Acceso WHERE UsuarioEmail='{0}' GROUP BY UsuarioEmail".format(
        _email)
    _cursor.execute(query)

    try:
        return _cursor.fetchall()[0][0]
    except IndexError:
        return 0

#Muestra en la LCD el mensaje indicado
def mensaje(_line1, _line2):
    lcd.clear()
    lcd.write_string(_line1)
    lcd.cursor_pos = (1, 0)
    lcd.write_string(_line2)

#Reproduce el sonido en el buzzer
def zum(_veces, _tiempo):
    for i in range(0, _veces):
        # Enciende el zumbador
        GPIO.output(pin_zumbador, GPIO.LOW)
        t.sleep(_tiempo)  # Espera 2 segundos

        # Apaga el zumbador
        GPIO.output(pin_zumbador, GPIO.HIGH)
        t.sleep(_tiempo)  # Espera 2 segundos

#Mandamos el mail al usuario (sin bonos o casi sin bonos)
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
    # configurar hardware
    reader = SimpleMFRC522()

    cnx = mysql.connector.connect(
        host="localhost", user="phpmyadmin", passwd="root", database="LaBenditera")
    cursor_db = cnx.cursor()
    variable = "false"
    zum(1,1)
    while (True):
        if(variable == "false"):
            variable = "true"
            mensaje("Esperando marca...", "")
        if reader.read_id_no_block():
            idTarjeta = reader.read_id()
            if idTarjeta:
                variable = "false"
                print(idTarjeta)
                nombre = devolverUsuario(cursor_db, idTarjeta)
                if nombre:
                    email = nombre[0]
                    minutos = obtenerHoras(cursor_db, email)
                    if(minutos[0]):
                        horas = minutos[0]/60
                    else:
                        horas = minutos[1]/60
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
                            cnx, cursor_db, email, tipo, marca)
                        if(tipo == 'entrada'):
                            if(horas > 0):
                                mensaje("Hola", "{0}! :)".format(nombre[1]))
                                zum(3, 0.1)
                            else:
                                mensaje("{0}".format(nombre[1]), "SIN HORAS.")
                                zum(12, 0.05)
                        else:
                            if(horas <= 0):
                                mandarMail("Te has quedado sin horas en tu bono... :(", emailSinBonos, email)
                            elif(horas <= 3):
                                mandarMail("Te estás quedando sin horas en tu bono... :(", emailCasiSinBonos, email)
                            mensaje("Hasta pronto", " {0}".format(horas))
                            zum(1, 1)
                        
                        sleep(2)
                    except:
                        print(idTarjeta)
                        mensaje("Usuario no registrado.", "")
                        sleep(1)
                else:
                    escribirTarjeta(
                        cnx, cursor_db, idTarjeta)
                    zum(1, 0.5)
                    mensaje("Tarjeta lista.", "")
                    sleep(1)
        else:
            sleep(0.5)
            if(variable == "false"):
                variable = "true"
                mensaje("Esperando marca...", "")


if __name__ == '__main__':
    main()
