#!/usr/bin/python
import MySQLdb
import sys, serial, argparse

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

sensorPort = args.port

print('reading from serial port %s...' % sensorPort)

device = serial.Serial(sensorPort, 9600)
measures = [0, 0, 0, 0];
while True:
    line = device.readline().strip()
    measure = line.split(';')
    if len(measure) > 1:

        sql = "INSERT INTO `measure` (`sensor_id`, `value`,  `date`) VALUES (%s, %s, NOW())"
        measure_type,sensor_id,value = measure
        value = format(float(value), '.1f');
        if measures[int(sensor_id) - 1] == 0:
            cursor.execute(sql, (sensor_id, value))
            print('inserting value', value, 'for sensor with ID ', sensor_id)
            db.commit()
            if cursor.lastrowid:
                print('last insert id', cursor.lastrowid)
            else:
                print('last insert id not found')
        # sensors send messages every 30sec. here is to ignore all but one in 15min.
        elif measures[int(sensor_id) - 1] == 30:
            measures[int(sensor_id) - 1] = 0;
        else:
            print('ignored same previous value', value, 'for sensor with ID ', sensor_id)
        measures[int(sensor_id) - 1] += 1;

    else:
         print("Connected")
db.close()
