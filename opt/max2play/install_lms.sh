#!/bin/sh
installcheck=$(dpkg -s lyrionmusicserver | grep 'Status: install' | wc -l)
if [ "1" -gt "$installcheck" ]; then
    installcheck=$(dpkg -s logitechmediaserver | grep 'Status: install' | wc -l)
    if [ "1" -gt "$installcheck" ]; then
      name="logitechmediaserver"
    fi
else
  name="lyrionmusicserver"
fi
source=$2

#Only Check if installed 
if [ "$1" = "check" ]; then	
	echo "installed=$installcheck"
	echo "name=$name"
else
	if [ "1" -gt "$installcheck" ] || [ "$1" = "update" ]; then
		echo `date +"%Y-%m-%d %H:%M|"` > /opt/max2play/cache/install_lms.txt
		apt-get update
		
		# Needed for SSL connections e.g. Github Repositories
		apt-get install libio-socket-ssl-perl -y
		
		# Buster Fix
		RELEASE=$(lsb_release -a 2>/dev/null | grep Codename | sed "s/Codename:\t//")
		if [ "$RELEASE" = "buster" -o "$RELEASE" = "bookworm" ]; then
			apt-get install libcrypt-openssl-rsa-perl -y
			apt --fix-broken install -y
		fi
		
		#Uninstall to remove problems with plugins
		apt-get remove logitechmediaserver -y
		wget -O /opt/max2play/lms.deb $source -a /opt/max2play/cache/install_lms.txt
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
   				# Get compiled CPAN for current Perl Version and Link it if not existing
   				PERLV=$(perl -v | grep -o "(v[0-9]\.[0-9]\+" | sed "s/(v//;s/)//")
   				var=$(awk 'BEGIN{ print "'$PERLV'"<"'5.20'" }')
   				if [ "$var" -eq 0 -a ! -e /usr/share/squeezeboxserver/CPAN/arch/$PERLV/arm-linux-gnueabihf-thread-multi-64int/ ]; then
   					# get CPAN if not existing
   					if [ ! -e /opt/CPAN/$PERLV/arm-linux-gnueabihf-thread-multi-64int/ ]; then
   						wget -O /opt/max2play/cache/CPAN_PERL_ALL.tar.gz cdn.max2play.com/CPAN_PERL_ALL.tar.gz
   						tar -xvzf /opt/max2play/cache/CPAN_PERL_ALL.tar.gz -C /opt/
   						echo "Download CPAN for Perl $PERLV"
   					fi
   					ln -sf /opt/CPAN/$PERLV/arm-linux-gnueabihf-thread-multi-64int/ /usr/share/squeezeboxserver/CPAN/arch/$PERLV/arm-linux-gnueabihf-thread-multi-64int
   					echo "Linking CPAN to Perl $PERLV"
   					sleep 4
   				else
   					ln -sf /opt/CPAN/arm-linux-gnueabihf-thread-multi-64int/ /usr/share/squeezeboxserver/CPAN/arch/5.18/
   					echo "Linking CPAN to Latest"
   				fi
   				;;
		esac				
		
		(echo "y") | apt-get install lame -y
		
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
		#wget -O /opt/max2play/cache/dsdplayer-bin.zip www.max2play.com/downloads/squeezebox-server/dsdplayer-bin.zip
		wget -O /opt/max2play/cache/dsdplayer-bin.zip https://cdn.max2play.com/squeezebox-server/dsdplayer-bin.zip
		unzip -o /opt/max2play/cache/dsdplayer-bin.zip -d /usr/share/squeezeboxserver/Bin/

		if [ "1" -gt $(grep lyrionmusicserver /etc/sudoers.d/max2play | wc -l) ]; then
		  sed -i 's@logitechmediaserver@logitechmediaserver,/etc/init.d/lyrionmusicserver@' /etc/sudoers.d/max2play
	  fi
		sleep 3
		/etc/init.d/logitechmediaserver restart
		/etc/init.d/lyrionmusicserver restart
	else
		echo "Ist bereits installiert - installed=$installcheck"
	fi
fi



