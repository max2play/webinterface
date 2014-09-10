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
		(echo "y") | apt-get install lame		
		
		#(echo "y") | apt-get install flac		
		#(echo "y") | apt-get install faad
		#(echo "y") | apt-get install sox
		#rm /usr/share/squeezeboxserver/Bin/arm-linux/faad /usr/share/squeezeboxserver/Bin/arm-linux/flac /usr/share/squeezeboxserver/Bin/arm-linux/sox
		#ln /usr/bin/faad /usr/share/squeezeboxserver/Bin/arm-linux/faad
		#ln /usr/bin/flac /usr/share/squeezeboxserver/Bin/arm-linux/flac
		#ln /usr/bin/sox /usr/share/squeezeboxserver/Bin/arm-linux/sox
		#Fix Perl 5.18 CPAN: Paket libungif-bin und libungif.so symlink fixen, CPAN> Image::Scale manuell, Font + Hebrew raus aus buildme
		#Fix http://forums.slimdevices.com/showthread.php?99566-Perl-5-14-vs-5-16-vs-5-18&p=772369&viewfull=1#post772369
		#ln -f /usr/lib/arm-linux-gnueabihf/libgif.a /usr/lib/libungif.a
		#ln -f /usr/lib/arm-linux-gnueabihf/libgif.so.4.1.6 /usr/lib/libungif.so
		
		#Image::Scale FIX, if file not exists
		if [ -e /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/auto/Image/Scale/Scale.so ]; then
			echo "NO Image-Fix"
		else	
			unzip -o /opt/max2play/cpan_fix_image.zip -d /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int
			chmod 777 /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/auto/Image/Scale/Scale.so
			echo "Image Fix"
		fi	
		
		#Fix ubuntu 14 interpreter
		ln /lib/arm-linux-gnueabihf/ld-linux.so.3 /lib/ld-linux.so.3
		sleep 3
		/etc/init.d/logitechmediaserver restart
	else
		echo "Ist bereits installiert - installed=$installcheck"
	fi
fi



