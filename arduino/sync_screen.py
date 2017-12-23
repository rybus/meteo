#!/usr/bin/python
import MySQLdb
import sys, serial, argparse

# TODO: remove passwords, use command arguments or prompt (better)
db = MySQLdb.connect(host="localhost",    # your host, usually localhost
                     user="meteo",         # your username
                     passwd="toudidou",  # your password
                      db="meteo")        # name of the data base

cursor = db.cursor()

parser = argparse.ArgumentParser(description="Serial port (arduino connected to sensors)")
parser.add_argument('--port_sensors', dest='port_sensors', required=True)
parser.add_argument('--port_screen', dest='port_screen', required=True)
args = parser.parse_args()

sensorPort = args.port_sensors
screenPort = args.port_screen


print('reading from serial port %s...' % sensorPort)
print('sending for display on serial port %s..' % screenPort)

device = serial.Serial(sensorPort, 9600)
screen = serial.Serial(screenPort, 9600);
while True:
    line = device.readline().strip()
    screen.write(line + "\n")
    measure = line.split(';')
    if len(measure) > 1:
        sql = "INSERT INTO `measure` (`sensor_id`, `value`,  `date`) VALUES (%s, %s, NOW())"
        measure_type,sensor_id,value = measure
        cursor.execute(sql, (sensor_id, value))
        print('inserting value', value, 'for sensor with ID ', sensor_id)
        db.commit()
        if cursor.lastrowid:
            print('last insert id', cursor.lastrowid)
        else:
            print('last insert id not found')
    else:
         print("Connected")
db.close()
