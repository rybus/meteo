#include <SoftwareSerial.h>
#include "RS485_protocol.h"
#include <DallasTemperature.h>
#include <OneWire.h>

#define SERIAL_RX_PIN        10  
#define SERIAL_TX_PIN        11
#define SERIAL_DIRECTION_PIN 3
#define ONEWIRE_PIN 6

#define RS485_TRANSMIT HIGH
#define RS485_RECEIVE  LOW

// Devices ID
#define MASTER_ARDUINO   0
#define SENSOR_OWR_TMP_2 4
#define FIFTEEN_MINUTES 900000
#define ONE_MINUTE      60000
#define TEMPERATURE 2

OneWire oneWire(ONEWIRE_PIN);
DallasTemperature one_wire_bus(&oneWire);

SoftwareSerial rs485(SERIAL_RX_PIN, SERIAL_TX_PIN);
DeviceAddress dsb18b20_sensor = { 0x28, 0x88, 0xE4, 0x88, 0x05, 0x00, 0x00, 0x60 };


float bytes2Float(byte bytes_array[4]) {
   union {
    float float_variable;
    byte temp_array[4];
  } u;
  memcpy(u.temp_array, bytes_array, 4);
  
  return u.float_variable;
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

void setup()
{
  rs485.begin(28800);
  Serial.begin(9600);
  Serial.println("SETUP");
  one_wire_bus.begin(); 
    
  pinMode(SERIAL_DIRECTION_PIN, OUTPUT);
} 

void loop()
{
  byte response[6];
  byte received_bytes[4];
  float temperature;
  
  byte received = recvMsg(
    fAvailable, 
    fRead, 
    response, 
    sizeof(response)
  );

  if (received) {
    if (response[0] == SENSOR_OWR_TMP_2) {
        one_wire_bus.requestTemperatures();
        displayMeasure(SENSOR_OWR_TMP_2, TEMPERATURE, one_wire_bus.getTempC(dsb18b20_sensor));
    } else {
      received_bytes[0] = response[2];
      received_bytes[1] = response[3];
      received_bytes[2] = response[4];
      received_bytes[3] = response[5];
        
      displayMeasure(response[0], response[1], bytes2Float(received_bytes));
    }
  }
  
}


void displayMeasure(byte sensor, byte measure_type, float measure)
{
  Serial.print(measure_type);
  Serial.print(";");
  Serial.print(sensor);
  Serial.print(";");
  Serial.println(measure);
}
