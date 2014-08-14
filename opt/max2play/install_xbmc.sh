#!/bin/sh
installcheck=$(dpkg -s xbmc | grep 'Status: install' | wc -l)

source=$2

#Only Check if installed 
if [ "$1" = "check" ]; then	
	echo "installed=$installcheck"
else
	if [ "1" -gt "$installcheck" ] || [ "$1" = "update" ]; then
		echo `date +"%Y-%m-%d %H:%M|"` > /opt/max2play/cache/install_xbmc.txt
		wget -O /opt/max2play/xbmc.deb $source -o /opt/max2play/cache/install_xbmc.txt
		echo "| START INSTALL | " >> /opt/max2play/cache/install_xbmc.txt
		dpkg -i /opt/max2play/xbmc.deb >> /opt/max2play/cache/install_xbmc.txt
		echo "Installation abgeschlossen"
		rm /opt/max2play/cache/install_xbmc.txt
		
	else
		echo "Ist bereits installiert - installed=$installcheck"
	fi
fi



