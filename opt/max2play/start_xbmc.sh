#!/bin/sh

# check for running XBMC and stop it if its running
xbmcrunning=$(ps -Al | grep xbmc.bin | wc -l)

# Only stop Audioplayer for XBMC when no USB-DAC is used
useusbdac=$(grep -a USE_USB_DAC=1 /opt/max2play/audioplayer.conf | wc -l)

# this script has xbmc in its name...
if [ "1" -gt "$xbmcrunning" ]; then

	if [ "1" -gt "$useusbdac" ]; then
		/etc/init.d/squeezelite stop	
	fi
	
	/etc/init.d/shairport stop
	/etc/init.d/squeezeslave stop
		
	/usr/local/bin/xbmc
else
	killall -9 xbmc.bin
	
	autostartsqueeze=$(grep -a squeezelite=1 /opt/max2play/autostart.conf | wc -l)
	if [ "0" -lt "$autostartsqueeze" ]; then
		/etc/init.d/squeezelite start
	fi
	
	autostartshairport=$(grep -a shairport=1 /opt/max2play/autostart.conf | wc -l)
	if [ "0" -lt "$autostartshairport" ]; then
		/etc/init.d/shairport start
	fi
fi