#!/bin/sh
# Script listet alle Player im lokalen Netzwerk im Cache-Ordner
IP=`/sbin/ifconfig eth0 | grep "inet" | cut -d ":" -f 2 | cut -d " " -f 1`
if [ -z "$IP" ]; then
    echo "IP is empty"
    IP=`/sbin/ifconfig wlan0 | grep "inet" | cut -d ":" -f 2 | cut -d " " -f 1`    
fi

if [ -z "$IP" ]; then
    echo "IP is still empty"
    echo `date +"%Y-%m-%d %H:%M"` > /opt/max2play/cache/list_devices.txt
else
	SUBNET=${IP%.*}
	OUTPUT=`nmap -p 5002 --open $SUBNET.*`
	echo "$IP $OUTPUT" > /opt/max2play/cache/list_devices.txt
fi
