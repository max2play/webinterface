#!/bin/sh
echo "Squeezelite und Shairport starten, falls XBMC nicht läuft entsprechend autostart.conf"
echo "Check auf laufenden XBMC - Pulseaudio VS Alsa bringt Probleme - Alternativ Check auf USB-DAC (Nur Squeezelite)"

xbmcrunning=$(ps -Al | grep "xbmc.bin\|kodi.bin" | wc -l)

useusbdac=$(grep -a USE_USB_DAC=1 /opt/max2play/audioplayer.conf | wc -l)

if [ "1" -gt "$xbmcrunning" -o "0" -lt "$useusbdac" ]
then
    autostart_squeezelite=$(cat /opt/max2play/autostart.conf | grep squeezelite=1 | wc -l)    
    if [ "0" -lt "$autostart_squeezelite" ]
	then
		running_squeezelite=$(ps -Al | grep squeezelite | wc -l)
		if [ "1" -gt "$running_squeezelite" ]; then
			sudo /etc/init.d/squeezelite start
		fi
    fi   
fi

if [ "1" -gt "$xbmcrunning" ]; then
	
	autostart_shairport=$(cat /opt/max2play/autostart.conf | grep shairport=1 | wc -l)    
    if [ "0" -lt "$autostart_shairport" ]
        then
        running_shairport=$(ps -Al | grep shairport | wc -l)
        if [ "1" -gt "$running_shairport" ]; then
        	sudo /etc/init.d/shairport start
        fi
    fi        
    
    autostart_gmediarender=$(cat /opt/max2play/autostart.conf | grep gmediarender=1 | wc -l)    
    if [ "0" -lt "$autostart_gmediarender" ]
        then
        running_gmediarender=$(ps -Al | grep gmediarender | wc -l)
        if [ "1" -gt "$running_gmediarender" ]; then
        	sudo /etc/init.d/gmediarender start
        fi
    fi        
    
    autostart_mpd=$(cat /opt/max2play/autostart.conf | grep "^mpd=1" | wc -l)
    if [ "0" -lt "$autostart_mpd" ]
        then
        running_mpd=$(ps -Al | grep mpd | wc -l)
        if [ "1" -gt "$running_mpd" ]
	        then
	        sudo /etc/init.d/mpd start	        
	    fi
    fi
	
	autostart_jivelite=$(cat /opt/max2play/autostart.conf | grep jivelite=1 | wc -l)
    running_jivelite=$(ps -Al | grep jivelite | wc -l)
    if [ "0" -lt "$autostart_jivelite" -a  "1" -gt "$running_jivelite" ]
        then
        USER=$(grep -a "SYSTEM_USER" /opt/max2play/audioplayer.conf | sed -n -e 's/^[A-Z_]*\=//p')
        if [ "$USER" = "pi" ]; then
          export DISPLAY=':0'
          running_xserver=$(ps -Al | grep "lxsession\|startx\|Xorg" | wc -l)
          if [ "1" -gt "$running_xserver" ]; then
          	sudo su -l pi -c /usr/bin/startx > /dev/null 2>&1 &
          	sleep 10
          fi         
          sudo -u pi -H /opt/jivelite/jivelite/bin/jivelite > /dev/null 2>&1 &
        else
          export DISPLAY=':0'
          sudo -u odroid -H /opt/jivelite/jivelite/bin/jivelite > /dev/null 2>&1 &
        fi
    fi
fi

autostart_presence_detection=$(cat /opt/max2play/autostart.conf | grep presence_detection=1 | wc -l)
running_presence_detection=$(ps -Al | grep fritzbox_devic | wc -l)
if [ "0" -lt "$autostart_presence_detection" -a  "1" -gt "$running_presence_detection" ]
   then
     /opt/max2play/fritzbox_devices.sh &
fi


autostart_bluetooth=$(cat /opt/max2play/autostart.conf | grep bluetooth=1 | wc -l)
if [ "0" -lt "$autostart_bluetooth" ]; then
	/var/www/max2play/application/plugins/bluetooth/scripts/autostart.sh
fi


autostart_xserver=$(cat /opt/max2play/autostart.conf | grep autostartxserver=1 | wc -l)
if [ "0" -lt "$autostart_xserver" ]; then	
	running_xserver=$(ps -Al | grep "lxsession\|startx\|Xorg" | wc -l)	
	if [ "1" -gt "$running_xserver" ]; then
		USER=$(grep -a "SYSTEM_USER" /opt/max2play/audioplayer.conf | sed -n -e 's/^[A-Z_]*\=//p')		
		if [ "$USER" = "pi" ]; then
			#Connect to correct pulse PID
			export XDG_RUNTIME_DIR=/run/user/1000
		fi
		echo "start X-Server"
		export DISPLAY=':0'
		sudo su -l $USER -c startx > /dev/null 2>&1 &
		sleep 5
		#Kill start_pulseaudio process
	fi
fi


autoreconnect_wifi=$(cat /opt/max2play/options.conf | grep autoreconnect_wifi=1 | wc -l)
if [ "$autoreconnect_wifi" -gt "0" ]; then
   if [ "$(LANG=C && sudo /sbin/ifconfig eth0 | grep 'inet addr:' | wc -l)" -lt "1" -a "$(LANG=C && sudo /sbin/ifconfig wlan0 | grep 'inet addr:' | wc -l)" -lt "1" ]; then
      # Set timer to wait, if some other process is starting wifi interface
      sleep 10
      if [ "$(LANG=C && sudo /sbin/ifconfig eth0 | grep 'inet addr:' | wc -l)" -lt "1" -a "$(LANG=C && sudo /sbin/ifconfig wlan0 | grep 'inet addr:' | wc -l)" -lt "1" ]; then     
	      echo "Network connection down! Attempting reconnection."
	      #sudo rm /var/run/wpa_supplicant/wlan0
	      sudo /sbin/ifdown --force wlan0
	      sudo /sbin/ifup --force wlan0
      fi
   fi
fi

#Multisqueeze
autostart_multisqueeze=$(cat /opt/max2play/autostart.conf | grep multisqueeze=1 | wc -l)
if [ "$autostart_multisqueeze" -gt "0" -a -e /var/www/max2play/application/plugins/multisqueeze/scripts/autostart.sh ]; then
	/var/www/max2play/application/plugins/multisqueeze/scripts/autostart.sh
fi

#Multishairport
autostart_multishairport=$(cat /opt/max2play/autostart.conf | grep multishairport=1 | wc -l)
if [ "$autostart_multishairport" -gt "0" -a -e /var/www/max2play/application/plugins/multishairport/scripts/autostart.sh ]; then
	/var/www/max2play/application/plugins/multishairport/scripts/autostart.sh
fi

#Custom Autostart
if [ -e /opt/max2play/custom_autostart.sh ]; then
	/opt/max2play/custom_autostart.sh
fi