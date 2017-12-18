#!/usr/bin/python
import MySQLdb
import sys, serial, argparse

db = MySQLdb.connect(host="localhost",    # your host, usually localhost
                     user="root",         # your username
                     passwd="toudidou",  # your password
                      db="meteo")        # name of the data base

# you must create a Cursor object. It will let
#  you execute all the queries you need

cursor = db.cursor()
# Use all the SQL you like
cursor.execute("SELECT * from sensor")

# print all the first cell of all the rows
for row in cursor.fetchall():
        print row[0]

# create parser
parser = argparse.ArgumentParser(description="Serial port")
# add expected arguments
parser.add_argument('--port', dest='port', required=True)

# parse args
args = parser.parse_args()

#strPort = '/dev/tty.usbserial-A7006Yqh'
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
