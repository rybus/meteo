#!/usr/bin/python
import MySQLdb
import sys, serial, argparse
import time
import datetime


# TODO: remove passwords, use command arguments or prompt (better)
db = MySQLdb.connect(host="localhost",    # your host, usually localhost
                     user="meteo",         # your username
                     passwd="toudidou",  # your password
                      db="meteo")        # name of the data base
db.autocommit(True)
cursor = db.cursor()

parser = argparse.ArgumentParser(description="Serial port (arduino connected to sensors)")
parser.add_argument('--port', dest='port', required=True)
args = parser.parse_args()

screenPort = args.port
query = ("SELECT * FROM measure "
         "WHERE sensor_id = %s ORDER BY id DESC LIMIT 1")


print('sending for display on serial port %s..' % screenPort)
humidite_serre = temperature_serre = temperature_interieur = temperature_exterieur = -127;

while True:
    try:
        ser = serial.Serial(screenPort, 9600, timeout=1)
        time.sleep(2)
        for sensor_id in range(1, 5):
            cursor.execute(query, [sensor_id])
            for (id, sensor_id, value, date) in cursor:
                d = datetime.datetime.strptime(str(date), "%Y-%m-%d %H:%M:%S")
                message = str(d.strftime('%H:%M')) +";"+str(sensor_id)+ ";"+ str(value)+ "\n".encode()
                print message
                ser.write(message)
                time.sleep(5)
        ser.close();
        time.sleep(60)
    except (OSError, serial.SerialException):
        pass
