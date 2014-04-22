#!/bin/sh
autostart_xbmc=$(cat /opt/max2play/autostart.conf | grep xbmc=1 | wc -l)
if [ "0" -lt "$autostart_xbmc" ]
    then
    /opt/max2play/start_xbmc.sh
fi

