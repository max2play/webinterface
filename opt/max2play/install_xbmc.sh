#!/bin/sh
installcheck=$(dpkg -s xbmc | grep 'Status: install' | wc -l)

source=$2

#Only Check if installed 
if [ "$1" = "check" ]; then	
	echo "installed=$installcheck"
else
	if [ "1" -gt "$installcheck" ] || [ "$1" = "update" ]; then
		echo "Remove old version"
		dpkg -r xbmc
		dpkg -r kodi		
		wget -O /opt/max2play/xbmc.deb $source
		dpkg -i /opt/max2play/xbmc.deb
		# XU3/XU4 Ubuntu 15.04
		# apt-get update
		# apt-get install libgnutls-dev
		echo "Installation abgeschlossen"
				
		# Fix Ubuntu 15.04 and ODROID XU4
		if [ "$(cat /proc/cpuinfo | grep Hardware | grep XU3 | wc -l)" -gt "0" ]; then
			# Fix für Bild, das nicht angezeigt wird (Fehler bei Kodi Start)
			rm -R /usr/lib/arm-linux-gnueabihf/mesa-egl/
			apt-get install fonts-roboto javascript-common libhdhomerun1 libjs-iscroll libjs-jquery libshairport2 -y
			# Needed for PVR-Plugins and others
			sudo /var/www/max2play/application/plugins/max2play_settings/scripts/buildkodiplatform.sh
			echo "Update for XU4 done"
		fi
	else
		echo "Is already installed - installed=$installcheck"
	fi
fi
echo "finished"


