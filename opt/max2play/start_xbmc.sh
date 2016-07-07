#!/bin/sh

# check for running XBMC and stop it if its running
xbmcrunning=$(ps -Al | grep "xbmc.bin\|kodi.bin" | wc -l)

# Only stop Audioplayer for XBMC when no USB-DAC is used
useusbdac=$(grep -a USE_USB_DAC=1 /opt/max2play/audioplayer.conf | wc -l)

if [ "1" -gt "$xbmcrunning" ]; then

	if [ "1" -gt "$useusbdac" ]; then
		/etc/init.d/squeezelite stop	
	fi
	
	/etc/init.d/shairport stop
	if [ -e /etc/init.d/mpd ]; then 
		/etc/init.d/mpd stop
	fi
	
	if [ "$(lsb_release -r | grep '8.0' | wc -l)" -lt "1" ]; then
		# Fix for XU3/XU4 and Ubuntu 15.04
		killall pulseaudio
		# TODO: remove PID Files!
	fi
	
	# Add this also to init-script # also add -foreground black -> remove with -foreground white on stop 
	# foreground black is specifically needed if desktop is restarting in background or any other console tasks are running
	sudo sh -c "TERM=linux setterm -foreground black --clear all >/dev/tty0"
	
	if [ -e /usr/local/bin/kodi ]; then
		/usr/local/bin/kodi
	elif [ -e /usr/bin/kodi-standalone ]; then
        /usr/bin/kodi-standalone
    else 	
		/usr/local/bin/xbmc
	fi
else
	killall -9 xbmc.bin
	killall -9 kodi.bin
	
	sudo sh -c "TERM=linux setterm -foreground white >/dev/tty0"
	
	autostartsqueeze=$(grep -a squeezelite=1 /opt/max2play/autostart.conf | wc -l)
	if [ "0" -lt "$autostartsqueeze" ]; then		
		/etc/init.d/squeezelite start
	fi
	
	autostartshairport=$(grep -a shairport=1 /opt/max2play/autostart.conf | wc -l)
	if [ "0" -lt "$autostartshairport" ]; then
		/etc/init.d/shairport start
	fi
	
	autostartmpd=$(grep -a mpd=1 /opt/max2play/autostart.conf | wc -l)
	if [ "0" -lt "$autostartmpd" ]; then
		/etc/init.d/mpd start
	fi
fi