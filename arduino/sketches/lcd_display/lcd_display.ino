#include <Adafruit_GFX.h> 
#include <Adafruit_TFTLCD.h>

#define LCD_CS A3 // Chip Select goes to Analog 3
#define LCD_CD A2 // Command/Data goes to Analog 2
#define LCD_WR A1 // LCD Write goes to Analog 1
#define LCD_RD A0 // LCD Read goes to Analog 0

#define LCD_RESET A4 

// Assign human-readable names to some common 16-bit color values:
#define	BLACK   0x0000
#define	BLUE    0x001F
#define	RED     0xF800
#define	GREEN   0x07E0
#define CYAN    0x07FF
#define MAGENTA 0xF81F
#define YELLOW  0xFFE0
#define WHITE   0xFFFF

#define SENSOR_OWR_TMP_1 3
#define SENSOR_OWR_TMP_2 4
#define SENSOR_DHT_TMP_1 2
#define SENSOR_DHT_RHY_1 1

Adafruit_TFTLCD tft(LCD_CS, LCD_CD, LCD_WR, LCD_RD, LCD_RESET);

float greenhouseTemperature = -127;
String greenhouseTemperatureDate = "01/01/1971 00:00";

float outdoorTemperature = -127;
String outdoorTemperatureDate = "01/01/1971 00:00";

float greenhouseHumidity = -127;
String greenhouseHumidityDate = "01/01/1971 00:00";

float indoorTemperature = -127;
String indoorTemperatureDate = "01/01/1971 00:00";

void setup(void) {
  Serial.begin(9600);
  tft.reset();
  uint16_t identifier = tft.readID();
  tft.begin(identifier);
  tft.setRotation(3);
  tft.fillScreen(BLACK);
  printMeasures("initialization");

}

void loop(void) {
  String message = Serial.readString();

   if (!message.equals("")) {
    String date = String(getValue(message, ';', 0));
    int sensor_id = String(getValue(message, ';', 1)).toInt();
    float measure = String(getValue(message, ';', 2)).toFloat();
   
    switch(sensor_id) {
      case SENSOR_OWR_TMP_1:
        outdoorTemperature = measure;
        outdoorTemperatureDate = date;
        break;
      case SENSOR_DHT_TMP_1:
        greenhouseTemperature = measure;
        greenhouseTemperatureDate = date;
        break;
      case SENSOR_DHT_RHY_1:
        greenhouseHumidity = measure;
        greenhouseHumidityDate = date;
        break;
     case SENSOR_OWR_TMP_2:
       indoorTemperature = measure;
       indoorTemperatureDate = date;
       break;        
    }
    tft.fillScreen(BLACK);
    printMeasures(message);
  }
}

String getValue(String data, char separator, int index)
{
    int found = 0;
    int strIndex[] = { 0, -1 };
    int maxIndex = data.length() - 1;

    for (int i = 0; i <= maxIndex && found <= index; i++) {
        if (data.charAt(i) == separator || i == maxIndex) {
            found++;
            strIndex[0] = strIndex[1] + 1;
            strIndex[1] = (i == maxIndex) ? i+1 : i;
        }
    }
    return found > index ? data.substring(strIndex[0], strIndex[1]) : "";
}

void printMeasures(String message) 
{
  int cursorMargin = 5;

  tft.fillScreen(BLACK);
  tft.setTextColor(WHITE);
  tft.setCursor(0, cursorMargin);
  tft.setTextColor(WHITE);  tft.setTextSize(3);
  tft.println("Temperature serre");
  tft.setTextSize(1);
  tft.println(greenhouseTemperatureDate);
  cursorMargin += 33;
  tft.setCursor(20, cursorMargin);
  tft.setTextColor(BLUE); tft.setTextSize(3);
  if (greenhouseTemperature == -127) {
    tft.println("?");
  } else {
    tft.print(greenhouseTemperature);
    tft.setTextSize(2);
    tft.print(char(248));
    tft.setTextSize(3);
    tft.println("C");
  }
  
  cursorMargin += 25;
  tft.setCursor(0, cursorMargin);
  tft.setTextColor(WHITE);  tft.setTextSize(3);
  tft.println("Humidite serre");
  tft.setTextSize(1);
  tft.println(greenhouseHumidityDate);
  cursorMargin += 33;
  tft.setCursor(20, cursorMargin);
  tft.setTextColor(BLUE); tft.setTextSize(3);
  if (greenhouseHumidity == -127) {
    tft.println("?");
  } else {
    tft.print(greenhouseHumidity);
    tft.setTextSize(2);
    tft.println("%");
  }

  cursorMargin += 25;
  tft.setCursor(0, cursorMargin);
  tft.setTextColor(WHITE);  tft.setTextSize(3);
  tft.println("Temperature ext.");
  tft.setTextSize(1);
  tft.println(outdoorTemperatureDate);
  cursorMargin += 33;
  tft.setCursor(20, cursorMargin);
  tft.setTextColor(BLUE); tft.setTextSize(3);
  if (outdoorTemperature == -127) {
    tft.println("?");
  } else {
    tft.print(outdoorTemperature);
    tft.setTextSize(2);
    tft.print(char(248));
    tft.setTextSize(3);
    tft.println("C");
  }
  
  cursorMargin += 25;
  tft.setCursor(0, cursorMargin);
  tft.setTextColor(WHITE);  tft.setTextSize(3);
  tft.println("Temperature int.");
    tft.setTextSize(1);
  tft.println(indoorTemperatureDate);
   cursorMargin += 33;
  tft.setCursor(20, cursorMargin);
  tft.setTextColor(BLUE); tft.setTextSize(3);
  if (indoorTemperature == -127) {
    tft.println("?");
  } else {
    tft.print(indoorTemperature);
    tft.setTextSize(2);
    tft.print(char(248));
    tft.setTextSize(3);
    tft.println("C");
  }

}
