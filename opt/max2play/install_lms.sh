#!/bin/sh
installcheck=$(dpkg -s logitechmediaserver | grep 'Status: install' | wc -l)

source=$2

#Only Check if installed 
if [ "$1" = "check" ]; then	
	echo "installed=$installcheck"
else
	if [ "1" -gt "$installcheck" ] || [ "$1" = "update" ]; then
		echo `date +"%Y-%m-%d %H:%M|"` > /opt/max2play/cache/install_lms.txt
		wget -O /opt/max2play/lms.deb $source -o /opt/max2play/cache/install_lms.txt
		echo "| START INSTALL | " >> /opt/max2play/cache/install_lms.txt
		dpkg -i /opt/max2play/lms.deb >> /opt/max2play/cache/install_lms.txt
		echo "Installation abgeschlossen"
		rm /opt/max2play/cache/install_lms.txt
		ln -s /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/ /usr/share/squeezeboxserver/CPAN/arch/5.18/
		sleep 3
		/etc/init.d/logitechmediaserver restart
	else
		echo "Ist bereits installiert - installed=$installcheck"
	fi
fi



