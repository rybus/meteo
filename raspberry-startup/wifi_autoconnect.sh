#!/bin/bash

# Auto reconnects to a WiFi AP
# Based on https://www.raspberrypi.org/forums/viewtopic.php?f=91&t=16054&start=25#p656295
#
# Set up cron to run every 5 minutes, as root:
# crontab -e
# */5 * * * * /home/pi/meteo/raspberry-startup/wifi_autoconnect.sh > /var/logs/wifi_connection.log 2>&1
#

# Fixed IP in router setting, allowing external access

set -x
raspberyIp="192.168.0.253"
APName=$(cat /etc/wpa_supplicant/wpa_supplicant.conf | grep ssid | cut -d'"' -f2)

if /sbin/ifconfig wlan0 | grep -q "inet ${raspberyIp}"; then
   echo "$(date) - Network is up!"
else
   echo "$(date) - Network is down, restarting dhcpd"
   systemctl restart dhcpcd
fi
