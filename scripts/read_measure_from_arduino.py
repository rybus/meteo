#!/usr/bin/python
import MySQLdb
import sys, serial, argparse

# TODO: remove passwords, use command arguments or prompt (better)
db = MySQLdb.connect(host="localhost",    # your host, usually localhost
                     user="root",         # your username
                     passwd="toudidou",  # your password
                      db="meteo")        # name of the data base

cursor = db.cursor()

parser = argparse.ArgumentParser(description="Serial port")
parser.add_argument('--port', dest='port', required=True)

args = parser.parse_args()

strPort = args.port

print('reading from serial port %s...' % strPort)

device = serial.Serial(strPort, 9600)
while True:
    line = device.readline().strip()
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
