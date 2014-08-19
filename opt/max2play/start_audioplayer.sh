#!/bin/sh
echo "Squeezelite und Shairport starten, falls XBMC nicht läuft entsprechend autostart.conf"
echo "Check auf laufenden XBMC - Pulseaudio VS Alsa bringt Probleme"
xbmcrunning=$(ps -Al | grep xbmc | wc -l)
if [ "1" -gt "$xbmcrunning" ]
then
    autostart_squeezelite=$(cat /opt/max2play/autostart.conf | grep squeezelite=1 | wc -l)
    if [ "0" -lt "$autostart_squeezelite" ]
	then
		sudo /etc/init.d/squeezelite start
    fi

    autostart_shairport=$(cat /opt/max2play/autostart.conf | grep shairport=1 | wc -l)
    if [ "0" -lt "$autostart_shairport" ]
        then
        sudo /etc/init.d/shairport start
    fi
    
    autostart_squeezeslave=$(cat /opt/max2play/autostart.conf | grep squeezeslave=1 | wc -l)
    if [ "0" -lt "$autostart_squeezeslave" ]
        then
        running_squeezeslave=$(ps -Al | grep squeezeslave | wc -l)
        if [ "1" -gt "$running_squeezeslave" ]
	        then
	        sudo /etc/init.d/squeezeslave start
	        sleep 4
	        sudo /etc/init.d/squeezeslave stop
	        sleep 4
	        sudo /etc/init.d/squeezeslave start
	    fi
    fi

fi


