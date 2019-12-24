#!/bin/bash

# Auto reconnects to a WiFi AP
# Based on https://www.raspberrypi.org/forums/viewtopic.php?f=91&t=16054&start=25#p656295
#
# Set up cron, as root:
# crontab -e
# */5 * * * * /home/pi/meteo/raspberry-startup/wifi_autoconnect.sh > /var/logs/wifi_connection.log 2>&1
#
# Side note: interesting read on WPA protection, to avoid having your WiFi password in plain text
# https://www.raspberrypi.org/documentation/configuration/wireless/wireless-cli.md
#
# 1. wpa_passphrase "wifi name"
# 2. Type in actual password
# 3. Replace content in /etc/wpa_supplicant/wpa_supplicant.conf

# Fixed IP in router setting, allowing external access
raspberyIp="192.168.0.253"
APName=$(cat /etc/wpa_supplicant/wpa_supplicant.conf | grep ssid | cut -d'"' -f2)

if ifconfig wlan0 | grep -q "inet ${raspberyIp}"; then
   echo "$(date) - Network is up!"
elif iwlist wlan0 scan|grep ${APName} &>/dev/null; then
   echo "$(date) - Network connection down! ${APName} is available, connecting..."
   ip link set wlan0 up
else
   echo "$(date) - Network is down or ${APName} is not available."
fi
