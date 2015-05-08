#!/bin/sh
installcheck=$(dpkg -s logitechmediaserver | grep 'Status: install' | wc -l)

source=$2

#Only Check if installed 
if [ "$1" = "check" ]; then	
	echo "installed=$installcheck"
else
	if [ "1" -gt "$installcheck" ] || [ "$1" = "update" ]; then
		echo `date +"%Y-%m-%d %H:%M|"` > /opt/max2play/cache/install_lms.txt
		#Uninstall to remove problems with plugins
		apt-get remove logitechmediaserver
		wget -O /opt/max2play/lms.deb $source -o /opt/max2play/cache/install_lms.txt
		echo "| START INSTALL | " >> /opt/max2play/cache/install_lms.txt
		dpkg -i /opt/max2play/lms.deb >> /opt/max2play/cache/install_lms.txt
		echo "Installation abgeschlossen"
		rm /opt/max2play/cache/install_lms.txt
		
		#Parse Version from $source to link to correct CPAN-Folder
		case "$source" in 
   			*"7.8"* ) 
   			    if [ -e /opt/CPAN/7.8 ]; then
   					ln -sf /opt/CPAN/7.8/arm-linux-gnueabihf-thread-multi-64int/ /usr/share/squeezeboxserver/CPAN/arch/5.18/
   					echo "Linking CPAN to 7.8"
   					wget -O /opt/max2play/cache/LMS_7.8_CPAN_Compress.zip shop.max2play.com/media/downloadable/currentversion/LMS_7.8_CPAN_Compress.zip
   					unzip -o /opt/max2play/cache/LMS_7.8_CPAN_Compress.zip -d /opt/CPAN/7.8/arm-linux-gnueabihf-thread-multi-64int/auto/
					chmod 777 /opt/CPAN/7.8/arm-linux-gnueabihf-thread-multi-64int/auto/Compress/Raw/Zlib/Zlib.so
					echo "Compress Fix for 7.8"
   				else
   					ln -sf /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/ /usr/share/squeezeboxserver/CPAN/arch/5.18/
   					echo "Linking CPAN to Latest"
   				fi;;
   			* ) 
   				ln -sf /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/ /usr/share/squeezeboxserver/CPAN/arch/5.18/
   				echo "Linking CPAN to Latest";;
		esac	
				
		(echo "y") | apt-get install lame		
		
		#(echo "y") | apt-get install flac		
		#(echo "y") | apt-get install faad
		#(echo "y") | apt-get install sox
		#rm /usr/share/squeezeboxserver/Bin/arm-linux/faad /usr/share/squeezeboxserver/Bin/arm-linux/flac /usr/share/squeezeboxserver/Bin/arm-linux/sox
		#ln /usr/bin/faad /usr/share/squeezeboxserver/Bin/arm-linux/faad
		#ln /usr/bin/flac /usr/share/squeezeboxserver/Bin/arm-linux/flac
		#ln /usr/bin/sox /usr/share/squeezeboxserver/Bin/arm-linux/sox
		#git clone https://github.com/Logitech/slimserver-vendor -b public/7.9
		#Fix Perl 5.18 CPAN: Paket libungif-bin und libungif.so symlink fixen, CPAN> Image::Scale manuell, Font + Hebrew raus aus buildme
		#Fix http://forums.slimdevices.com/showthread.php?99566-Perl-5-14-vs-5-16-vs-5-18&p=772369&viewfull=1#post772369
		#ln -f /usr/lib/arm-linux-gnueabihf/libgif.a /usr/lib/libungif.a
		#ln -f /usr/lib/arm-linux-gnueabihf/libgif.so.4.1.6 /usr/lib/libungif.so
		
		#Add squeezeboxserver to group audio (e.g. for waveinput plugin)
		usermod -a -G audio squeezeboxserver
		
		#Image::Scale FIX, if file not exists
		#if [ -e /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/auto/Image/Scale/Scale.so ]; then
	    #	echo "NO Image-Fix"
		#else	
			unzip -o /opt/max2play/cpan_fix_image.zip -d /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int
			chmod 777 /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/auto/Image/Scale/Scale.so
			echo "Image Fix"
		#fi	
		
		#Fix ubuntu 14 interpreter
		ln /lib/arm-linux-gnueabihf/ld-linux.so.3 /lib/ld-linux.so.3
		
		#Audio Fix für DSD
		wget -O /opt/max2play/cache/CPAN_AUDIO_DSD_7.9.tar shop.max2play.com/media/downloadable/beta/CPAN_AUDIO_DSD_7.9.tar
		tar -xf /opt/max2play/cache/CPAN_AUDIO_DSD_7.9.tar -C /opt		
		wget -O /opt/max2play/cache/dsdplayer-bin.zip www.max2play.com/downloads/squeezebox-server/dsdplayer-bin.zip
		unzip -o /opt/max2play/cache/dsdplayer-bin.zip -d /usr/share/squeezeboxserver/Bin/
		
		sleep 3
		/etc/init.d/logitechmediaserver restart
	else
		echo "Ist bereits installiert - installed=$installcheck"
	fi
fi



