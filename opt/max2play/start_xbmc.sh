#!/bin/sh
# Only stop Audioplayer for XBMC when no USB-DAC is used
useusbdac=$(grep -a USE_USB_DAC=1 /opt/max2play/audioplayer.conf | wc -l)

if [ "1" -gt "$useusbdac" ]; then
	/etc/init.d/squeezelite stop	
fi

/etc/init.d/shairport stop
/etc/init.d/squeezeslave stop
	
/usr/local/bin/xbmc