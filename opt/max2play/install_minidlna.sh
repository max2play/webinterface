#!/bin/sh

if [ "$1" = "install" ]; then
	echo "Set up Sources.list to get Backport for miniDLNA"
	sed -i 's/^deb http\:\/\/ports.ubuntu.com\/ubuntu-ports\/ trusty-backports main restricted$/deb http:\/\/ports.ubuntu.com\/ubuntu-ports\/ trusty-backports main restricted universe multiverse/' /etc/apt/sources.list
	apt-get update
	apt-get install minidlna/trusty-backports

	sed -i 's/#db_dir=\/var\/cache\/minidlna/db_dir=\/opt\/max2play\/cache\/minidlna/' /etc/minidlna.conf
	sed -i 's/#inotify=yes/inotify=yes/' /etc/minidlna.conf
	sed -i 's/^media_dir=.*/media_dir=\/media\/usb/' /etc/minidlna.conf	

else

	/etc/init.d/minidlna stop
	echo "Y" | apt-get remove --purge minidlna
	

fi
