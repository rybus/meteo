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

    greenhouseHumidityDate = String(getValue(message, ';', 0));
    greenhouseHumidity =  String(getValue(message, ';', 1)).toFloat();
    
    greenhouseTemperatureDate =  String(getValue(message, ';', 2));
    greenhouseTemperature =  String(getValue(message, ';', 3)).toFloat();
    
    outdoorTemperatureDate = String(getValue(message, ';', 4));
    outdoorTemperature = String(getValue(message, ';', 5)).toFloat();
    
    indoorTemperatureDate = String(getValue(message, ';', 6));
    indoorTemperature = String(getValue(message, ';', 7)).toFloat();


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
  cursorMargin += 29;
  tft.setCursor(20, cursorMargin);
  tft.setTextColor(GREEN); tft.setTextSize(3);
  if (greenhouseTemperature == -127) {
    tft.println("?");
  } else {
    tft.print(greenhouseTemperature);
    tft.setTextSize(2);
    tft.print(char(248));
    tft.setTextSize(3);
    tft.print(" C    ");
    tft.setTextColor(CYAN);
     tft.setCursor(210, cursorMargin);

    tft.println(greenhouseTemperatureDate);

  }
  cursorMargin += 25;
  tft.setCursor(0, cursorMargin);
  tft.setTextColor(WHITE);  tft.setTextSize(3);
  tft.println("Temperature ext.");

  cursorMargin += 30;
  tft.setCursor(20, cursorMargin);
  tft.setTextColor(GREEN); tft.setTextSize(3);
  if (outdoorTemperature == -127) {
    tft.println("?");
  } else {
    tft.print(outdoorTemperature);
    tft.setTextSize(2);
    tft.print(char(248));
    tft.setTextSize(3);
    tft.print(" C    ");
    tft.setTextColor(CYAN);
    tft.setCursor(210, cursorMargin);
    tft.println(outdoorTemperatureDate);
  }

  cursorMargin += 25;
  tft.setCursor(0, cursorMargin);
  tft.setTextColor(WHITE);  tft.setTextSize(3);
  tft.println("Temperature int.");

   cursorMargin += 29;
  tft.setCursor(20, cursorMargin);
  tft.setTextColor(GREEN); tft.setTextSize(3);
  if (indoorTemperature == -127) {
    tft.println("?");
  } else {
    tft.print(indoorTemperature);
    tft.setTextSize(2);
    tft.print(char(248));
    tft.setTextSize(3);
    tft.print(" C    ");
    tft.setTextColor(CYAN);
    tft.setCursor(210, cursorMargin);
    tft.println(indoorTemperatureDate);
  }

}
