#!/bin/sh
installcheck=$(dpkg -s logitechmediaserver | grep 'Status: install' | wc -l)

#Only Check if installed 
if [ "$1" = "check" ]; then	
	echo "installed=$installcheck"
else
	if [ "1" -gt "$installcheck" ]; then
		echo `date +"%Y-%m-%d %H:%M|"` > /opt/max2play/cache/install_lms.txt
		wget -O /opt/max2play/lms.deb http://downloads.slimdevices.com/LogitechMediaServer_v7.8.0/logitechmediaserver_7.8.0_all.deb -o /opt/max2play/cache/install_lms.txt
		echo "| START INSTALL | " >> /opt/max2play/cache/install_lms.txt
		dpkg -i /opt/max2play/lms.deb >> /opt/max2play/cache/install_lms.txt
		echo "Installation abgeschlossen"
		rm /opt/max2play/cache/install_lms.txt
	else
		echo "Ist bereits installiert - installed=$installcheck"
	fi
fi



