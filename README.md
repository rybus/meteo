# Meteo on RaspberryPi 3

## Pre requisites

- `pip install MySQL-python`
- `sudo apt-get install libmariadbclient-dev`
- Install [Composer](https://getcomposer.org/)

## Installation

- Clone this repository to your home folder
- `composer install`

## Crontab for WiFi auto re-connection

Wifi connection can drop, this will reconnects it automatically

```bash
# crontab -e
*/5 * * * * /home/pi/meteo/raspberry-startup/wifi_autoconnect.sh > /var/logs/wifi_connection.log 2>&1
```

## Set up auto start for screen and data reading

Copy raspberry-startup/arduino.service and screen.service to `/etc/systemd/system/`.

```bash
systemctl enable arduino
systemctl enable screen
systemctl start arduino
systemctl start screen
```

These commands will make both scripts to run at startup and start them immediately.

## Securing WiFi

Aavoid having your WiFi password in plain text

1. `wpa_passphrase "wifi name"`
2. Type in actual password
3. Replace content in `/etc/wpa_supplicant/wpa_supplicant.conf`

## Arduino Libraries

- https://github.com/adafruit/TFTLCD-Library
- https://github.com/adafruit/Adafruit-GFX-Library
- https://github.com/milesburton/Arduino-Temperature-Control-Library
- https://github.com/adafruit/DHT-sensor-library

## Raspbery links

- https://www.raspberrypi.org/documentation/configuration/wireless/
