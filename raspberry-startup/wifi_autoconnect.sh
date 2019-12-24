#!/bin/bash
# auto reconnect to wifi
# 
 
IFACE="wlan0"
NET="192.168.0.254"
 
if ifconfig $IFACE &>/dev/null && ifconfig $IFACE | grep -q "inet"; then
   echo "Network is up!"
elif ifconfig $IFACE &>/dev/null && iwlist $IFACE scan|grep $NET &>/dev/null; then
   echo "Network connection down! WIFI AP available, connecting..."
   ifup --force $IFACE
fi
