#!/bin/sh

if [ "$1" = "install" ]; then
	HW_RASPBERRY=$(cat /proc/cpuinfo | grep Hardware | grep -i "BCM2708\|BCM2709\|BCM2837\|BCM2835\|BCM2836" | wc -l)
	if [ "$HW_RASPBERRY" -gt "0" ]; then
		echo "Y" | sudo apt-get install minidlna
	else
		echo "Set up Sources.list to get Backport for miniDLNA"
		sed -i 's/^deb http\:\/\/ports.ubuntu.com\/ubuntu-ports\/ trusty-backports main restricted$/deb http:\/\/ports.ubuntu.com\/ubuntu-ports\/ trusty-backports main restricted universe multiverse/' /etc/apt/sources.list
		apt-get update
		apt-get install minidlna/trusty-backports
	fi

	sed -i 's/#db_dir=\/var\/cache\/minidlna/db_dir=\/opt\/max2play\/cache\/minidlna/' /etc/minidlna.conf
	sed -i 's/#inotify=yes/inotify=yes/' /etc/minidlna.conf
	sed -i 's/^media_dir=.*/media_dir=\/media\/usb/' /etc/minidlna.conf
	sed -i 's/^#network_interface=.*/network_interface=eth0,wlan0/' /etc/minidlna.conf
	
	#Sonderfall? Root Rechte für Daemon
	#sed -i 's/#USER=.*/USER="root"/;s/#GROUP=.*/GROUP="root"/' /etc/default/minidlna

else
	/etc/init.d/minidlna stop
	echo "Y" | apt-get remove --purge minidlna	
fi
