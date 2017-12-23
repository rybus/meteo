#include <SoftwareSerial.h>
#include <DHT.h>
#include <DallasTemperature.h>
#include <OneWire.h>
#include "RS485_protocol.h"

// Sensor pins
#define DHT_PIN  5
#define ONEWIRE_PIN 6

// RS485 pins
#define SERIAL_RX_PIN        10  
#define SERIAL_TX_PIN        11
#define SERIAL_DIRECTION_PIN 3

#define RS485_TRANSMIT HIGH
#define RS485_RECEIVE  LOW
#define DHT_TYPE DHT22

// Devices ID
#define MASTER_ARDUINO   0
#define SENSOR_OWR_TMP_1 3
#define SENSOR_OWR_TMP_2 4
#define SENSOR_DHT_TMP_1 2
#define SENSOR_DHT_RHY_1 1

#define TEMPERATURE 2
#define HUMIDITY    1
#define TEN_MINUTES     600000
#define ONE_MINUTE      60000
#define FIFTEEN_MINUTES 900000

enum DS18B20_RCODES {
  READ_OK,
  NO_SENSOR_FOUND,
  INVALID_ADDRESS,
  INVALID_SENSOR
};

OneWire oneWire(ONEWIRE_PIN);
DallasTemperature one_wire_bus(&oneWire);

DHT dht22_sensor(DHT_PIN, DHT_TYPE); 
DeviceAddress dsb18b20_sensor = { 0x28, 0xD3, 0x0D, 0x89, 0x05, 0x00, 0x00, 0x10 };

SoftwareSerial rs485(SERIAL_RX_PIN, SERIAL_TX_PIN);

void setup()
{
  rs485.begin(28800);
  Serial.begin(9600);
  
  dht22_sensor.begin();
  one_wire_bus.begin();

  pinMode(SERIAL_DIRECTION_PIN, OUTPUT);
} 

void convert(float val, byte* bytes_array){
  // Converts a float number in a 4-bytes array
  union {
    float float_variable;
    byte temp_array[4];
  } u;
  u.float_variable = val;
  memcpy(bytes_array, u.temp_array, 4);
}


void fWrite(const byte what)
{
  rs485.write(what);  
}
    
int fAvailable()
{
  return rs485.available();  
}

int fRead()
{
  return rs485.read();  
}

void sendToMaster(byte sensor_id, byte measure_type, float measure = 0.0)
{
    byte bytes_to_send[4];
    convert(measure, &bytes_to_send[0]); 
    
    byte response[] = {
        sensor_id,
        measure_type,
        bytes_to_send[0],
        bytes_to_send[1],
        bytes_to_send[2],
        bytes_to_send[3],
      };
          
    Serial.print("Sending measure... ");
    Serial.print(sensor_id);
    Serial.print(": ");
    Serial.print(measure);
    digitalWrite(SERIAL_DIRECTION_PIN, RS485_TRANSMIT);  
      sendMsg(fWrite, response, sizeof response);
    digitalWrite(SERIAL_DIRECTION_PIN, RS485_RECEIVE);
    Serial.println("OK!");
}



void loop()
{
  sendToMaster(SENSOR_DHT_TMP_1, TEMPERATURE, dht22_sensor.readTemperature());
  delay(1500);
  sendToMaster(SENSOR_DHT_RHY_1, HUMIDITY, dht22_sensor.readHumidity());
  delay(1500);
  one_wire_bus.requestTemperatures(); 
  sendToMaster(SENSOR_OWR_TMP_1,TEMPERATURE, one_wire_bus.getTempC(dsb18b20_sensor));
  delay(1500);
  // This is just to tell the master to capture the temperature.
  sendToMaster(SENSOR_OWR_TMP_2,TEMPERATURE, -127);
  
  delay(30000);
} 
