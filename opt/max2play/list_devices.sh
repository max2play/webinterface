#!/bin/sh
IP=`/sbin/ifconfig eth0 | grep "inet" | cut -d ":" -f 2 | cut -d " " -f 1`
SUBNET=${IP%.*}
OUTPUT=`nmap -p 5002 --open $SUBNET.*`
echo "$IP $OUTPUT" > /opt/max2play/cache/list_devices.txt
