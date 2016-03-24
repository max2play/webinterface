#!/bin/bash
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
        OUTPUT=`nmap -p 80 --open $SUBNET.* | grep -o -e "[a-zA-Z0-9\.]\+ ([0-9\.]\+)$" | sed 's/ (/|/;s/)//'`
        echo `date +"%Y-%m-%d %H:%M"` > /opt/max2play/cache/list_devices.txt
        # Check for Max2Play Webinterface
        while read -r line ; do
          ADDRESS=$(echo "$line" | sed 's/^.*|//')
          CHECK=$(wget -T 3 -t 1 -q -O - "$@" $ADDRESS | grep max2play | wc -l)
          if [ "$CHECK" -gt "0" ]; then
                if [ "$ADDRESS" = "$IP" ]; then
                	echo "$line|current" >> /opt/max2play/cache/list_devices.txt
                else
                	echo "$line" >> /opt/max2play/cache/list_devices.txt
                fi
          fi
        done <<<"$OUTPUT"
fi

exit 0
