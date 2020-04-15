#!/bin/bash
# Script listet alle Player im lokalen Netzwerk im Cache-Ordner

# Script darf nur einmal gleichzeitig laufen!
scriptrunning=$(ps -Al | grep "list_device" | wc -l)
if [ "$scriptrunning" -gt "2" ]; then
	exit 0
fi

IP=`/sbin/ip addr show eth0 | grep "inet " | grep -o 'inet [0-9.]\+' | grep -o '[0-9.]\+'`
if [ -z "$IP" ]; then
    echo "IP is empty"
    IP=`/sbin/ip addr show wlan0 | grep "inet " | grep -o 'inet [0-9.]\+' | grep -o '[0-9.]\+' | grep -v '169.254'`    
fi

if [ -z "$IP" ]; then
        echo "IP is still empty"
        echo `date +"%Y-%m-%d %H:%M"` > /opt/max2play/cache/list_devices.txt
else        
        SUBNET=${IP%.*}
        OUTPUT=`nmap -R -p 80 --open $SUBNET.* | grep -o -e "\([a-zA-Z0-9\.\-]\+ ([0-9\.]\+)\)\|\([0-9\.]\{6,\}\)$" | sed 's/ (/|/;s/)//'`
        FILEWRITE=`date +"%Y-%m-%d %H:%M"`
        # Check for Max2Play Webinterface
        while read -r line ; do
          ADDRESS=$(echo "$line" | sed 's/^.*|//')
          CHECK=$(wget -T 3 -t 1 -q -O - "$@" $ADDRESS/style.css | grep WUoqrET9fUeobQW7jkRT8E0i7KZn-EPnyo3HZu7kw | wc -l)
          if [ "$CHECK" -gt "0" ]; then
                if [ "$(echo $line | grep \| | wc -l)" -lt "1" ]; then
                	# Add | as the host has no hostname
                	line="|$line"
                fi 
                if [ "$ADDRESS" = "$IP" ]; then
                	FILEWRITE="$FILEWRITE\n$line|current"
                else
                	FILEWRITE="$FILEWRITE\n$line"
                fi
          fi
        done <<<"$OUTPUT"
        
        echo -e "$FILEWRITE" > /opt/max2play/cache/list_devices.txt
fi

exit 0
