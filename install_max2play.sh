#!/bin/bash
# Preparations for different Systems: ODROID U3/C1/Raspberry PI
echo "#### Max2Play-Installer for ODROID U3/C1/Raspberry PI ####"
echo "This script installs Max2Play-Scripts to /opt/max2play and the webinterface to /var/www/max2play"
echo " - On first start it will do an update/upgrade and expand filesystem and get Max2Play files - then it automatically rebootes"
echo " - On second start it installs all the fancy stuff and brings the webinterface to life"
echo "Depending on the system (ODROID/PI) and Linux Version (Debian/Ubuntu) it installs or compiles differents packages and its dependencies like squeezelite, Kodi, shairport, samba, etc." 
echo "Edit Parameters on top of script to change the default behavior of this script!"
echo ""
echo "Add Execute rights with 'chmod 777 install_max2play.sh'"
echo "RUN with 'sudo install_max2play.sh 2>&1 | tee install_max2play.log' to save Install-Logfile and see output on console!"
echo ""

# expand Filesystem during install
EXPAND_FILESYSTEM="N"

# set to Y if you want default password "max2play"
CHANGE_PASSWORD="Y" 

# leave empty to keep current hostname
# CHANGE_HOSTNAME="max2play"
CHANGE_HOSTNAME="max2play" 
SHAIRPORT="SHAIRPORT_SYNC"
PROJECT="max2play" #, max2play, squeezeplug, hifiberry, etc.

CWD=$(pwd)

if [ "$(whoami)" != "root" ]; then
	echo "Run this script with sudo OR as root! Otherwise it won't install correctly!"
	exit 1
fi

HW_RASPBERRY=$(cat /proc/cpuinfo | grep Hardware | grep -i "BCM2708\|BCM2709" | wc -l)
HW_ODROID=$(cat /proc/cpuinfo | grep Hardware | grep -i Odroid | wc -l)

LINUX=$(lsb_release -a 2>/dev/null | grep Distributor | sed "s/Distributor ID:\t//")
RELEASE=$(lsb_release -a 2>/dev/null | grep Codename | sed "s/Codename:\t//")

echo "Linux is $LINUX"
echo "Release is $RELEASE"

if [ "$HW_ODROID" -gt "0" ]; then
  USER=odroid
  echo "Hardware is odroid"
  FREESPACE=$(df -km /dev/mmcblk0p2 | tail -1 | awk '{print $4}')
  if [ "$FREESPACE" -lt "500" ] && [ "$LINUX" == "Ubuntu" ]; then
  	echo "Only $FREESPACE MB memory available - Run sudo odroid-utility.sh first to expand filesystem manually and Reboot!"
  	exit 1
  fi  
fi

if [ "$HW_RASPBERRY" -gt "0" ]; then
  USER=pi  
  echo "Hardware is Raspberry"  
  # Remove further not wanted packages?
  echo "Y" | sudo apt-get remove wolfram-engine
  
  FREESPACE=$(df -km /dev/root | tail -1 | awk '{print $4}')
  if [ "$FREESPACE" -lt "500" ]; then
  	echo "Only $FREESPACE MB memory available - Run sudo raspbi-config.sh first to expand filesystem manually and Reboot!"
  	exit 1
  fi  
fi

if [ ! -e /opt/max2play/ ]; then
	sudo apt-get update
	echo "Y" | sudo apt-get upgrade	
	pushd $CWD
	# get Live Version!
	wget shop.max2play.com/media/downloadable/currentversion/max2play_complete.zip
	unzip max2play_complete.zip -d max2play
	mkdir /opt
	sudo cp -r max2play/opt/* /opt
	chmod -R 777 /opt/max2play/
	
	#Expand FS!
	if [ "$EXPAND_FILESYSTEM" == "Y" ]; then
		sudo /opt/max2play/expandfs.sh
		echo "Expand Filesystem finished"
		reboot
		exit 0
	fi
fi

chmod 666 /etc/fstab
echo "##USERMOUNT" >> /etc/fstab
cp /etc/fstab /etc/fstab.sav
chmod 666 /etc/fstab.sav

crontab -u $USER -l > cronmax2play
sudo echo "* * * * * /opt/max2play/start_audioplayer.sh > /dev/null 2>&1" >> cronmax2play
crontab -u $USER cronmax2play
rm cronmax2play

sudo echo "Y" | apt-get install apache2 php5 php5-json
sudo a2enmod rewrite
rm /etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/000-default
cp max2play/CONFIG_SYSTEM/apache2/sites-enabled/max2play.conf /etc/apache2/sites-enabled/
sed -i 's/LogLevel warn/LogLevel error/' /etc/apache2/apache2.conf
cp -r max2play/max2play/ /var/www/max2play 
sudo /etc/init.d/apache2 restart
sudo echo "Y" | apt-get install samba samba-common samba-common-bin mc ntfs-3g cifs-utils

sudo apt-get install debconf-utils
if [ "$HW_RASPBERRY" -gt "0" ] || [ "$LINUX" == "Debian" ]; then  	
  	sed -i 's/# de_DE.UTF-8 UTF-8/de_DE.UTF-8 UTF-8/;s/# it_IT.UTF-8 UTF-8/it_IT.UTF-8 UTF-8/;s/# fr_FR.UFT-8 UTF-8/fr_FR.UFT-8 UTF-8/;s/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/;s/# en_GB.UTF-8 UTF-8/en_GB.UTF-8 UTF-8/' /etc/locale.gen
  	locale-gen
else	
	locale-gen ru_RU.UTF-8 
	locale-gen it_IT.UTF-8
	locale-gen fr_FR.UFT-8
	locale-gen de_DE.UTF-8 
fi

export LANG=en_GB.UTF-8
dpkg-reconfigure -f noninteractive locales
echo "Europe/Berlin" > /etc/timezone
dpkg-reconfigure -f noninteractive tzdata

sudo apt-get install ifplugd
sudo echo "Y" | apt-get install nmap
sudo echo "Y" | apt-get remove xscreensaver

# HD-Idle aktivieren
dpkg -i max2play/hd-idle_1.05_armhf.deb
sudo sed -i 's/START_HD_IDLE=.*/START_HD_IDLE=true/' /etc/default/hd-idle

sudo echo "Y" | apt-get install usbmount
cp -f max2play/CONFIG_SYSTEM/usbmount/usbmount.conf /etc/usbmount/usbmount.conf


echo "Y" | apt-get install nettle-dev caps libasound2-dev
pushd /tmp
wget http://www.thedigitalmachine.net/tools/alsaequal-0.6.tar.bz2
tar xvjf alsaequal-0.6.tar.bz2 
cd alsaequal/
patch ctl_equal.c < $CWD/max2play/OTHER/alsaequal_ctl_equal.patch
make
mkdir /usr/lib/alsa-lib
make install
mkdir /usr/lib/arm-linux-gnueabihf/alsa-lib/
cp -R /usr/lib/alsa-lib/* /usr/lib/arm-linux-gnueabihf/alsa-lib/

#Squeezelite
echo -e "Y\ny\n" | apt-get install libav-tools cmake
# Debian Wheezy soxr
if [ "$HW_RASPBERRY" -gt "0" ] || [ "$LINUX" == "Debian" ]; then	
	echo -e "Y\ny\n" | apt-get install libavformat-dev ffmpeg libmpg123-dev libfaad-dev libvorbis-dev libmad0-dev libflac-dev libasound2-dev
	pushd /tmp
	wget -O soxr.tar.gz --max-redirect=3 "http://downloads.sourceforge.net/project/soxr/soxr-0.1.1-Source.tar.xz"
	tar -xf soxr.tar.gz
	cd soxr*
	./go
	cd Release
	make install
else
   	echo -e "Y\ny\n" | apt-get install libsoxr-dev   	
fi

pushd /tmp
git clone https://code.google.com/p/squeezelite/
cd squeezelite
OPTS="-DFFMPEG -DRESAMPLE -DVISEXPORT -DDSD" make
mkdir /opt/squeezelite
mkdir /opt/squeezelite/log
chmod 777 /opt/squeezelite/log
cp /tmp/squeezelite/squeezelite /opt/squeezelite/
pushd $CWD

#### Squeezeboxserver Basic ####
echo "Y" | apt-get install libungif-bin

#### Shairport install
echo "Y" | apt-get install libssl-dev libavahi-client-dev libasound2-dev autoconf libtool libdaemon-dev libpopt-dev
pushd /tmp
# Switch between Shairport AND Shairport-Sync!
if [ "$SHAIRPORT" = "SHAIRPORT_SYNC" ]; then
	# install Shairport-Sync
	echo "Y" | apt-get install avahi-daemon
	git clone https://github.com/mikebrady/shairport-sync.git
	cd shairport*
	autoreconf -i -f		
	
	if [ "$LINUX" == "Debian" ] && [ "$HW_ODROID" -gt "0" ]; then
		# add ac_cv_func_malloc_0_nonnull=yes to configure odroid wheezy
		ac_cv_func_malloc_0_nonnull=yes ac_cv_func_realloc_0_nonnull=yes ./configure --with-alsa --with-avahi --with-ssl=openssl --with-soxr
	else
		./configure --with-alsa --with-avahi --with-ssl=openssl --with-soxr
	fi
			
	make
	mkdir -p /opt/shairport/log
	chmod 777 /opt/shairport/log
	cp -f shairport-sync /opt/shairport/shairport
else
	# install Normal Shairport	
	git clone https://github.com/abrasive/shairport.git
	cd shairport
	./configure
	make
	mkdir -p /opt/shairport/log
	chmod 777 /opt/shairport/log
	cp shairport /opt/shairport
fi
pushd $CWD


#### Install DLNA CLIENT ####
if [ "$LINUX" == "Debian" ]; then
	#Doesnt work on Ubuntu 14.04
	pushd /tmp
	git clone https://github.com/hzeller/gmrender-resurrect.git
	cd gmrender-resurrect
	echo "Y" | sudo apt-get install autoconf automake libtool
	echo "Y" | sudo apt-get install libupnp-dev libgstreamer0.10-dev \
	    gstreamer0.10-plugins-base gstreamer0.10-plugins-good \
	    gstreamer0.10-plugins-bad gstreamer0.10-plugins-ugly \
	    gstreamer0.10-ffmpeg \
	    gstreamer0.10-pulseaudio gstreamer0.10-alsa
	sudo ./autogen.sh
	sudo ./configure
	sudo make
	sudo make install	
	pushd $CWD
fi


#fix exzessives Logging in syslog & co (cron)
cp -f max2play/CONFIG_SYSTEM/rsyslog.conf /etc/rsyslog.conf

#Copy Config Files / Update Max2Play einmalig nötig
echo "1.0" > /var/www/max2play/application/config/version.txt

#Save default audioplayer config
cp /opt/max2play/audioplayer.conf /opt/max2play/audioplayer.conf.sav

if [ "$HW_RASPBERRY" -gt "0" ]; then
	pushd $CWD					
	
	#Raspberry PI: User PI nutzen!
	echo "SYSTEM_USER=pi" >> /opt/max2play/audioplayer.conf	
	
	#Usbmount Fix
	sudo sed -i 's/odroid/pi/' /etc/usbmount/usbmount.conf
	
	#Squeezeplug Header & CSS & Plugin-Auswahl
	if [ "$PROJECT" == "squeezeplug" ]; then
		# Install Plugin squeezeplug
		/opt/max2play/install_plugin.sh http://shop.max2play.com/media/downloadable/beta/squeezeplug.tar
		cp -f /var/www/max2play/application/plugins/squeezeplug/view/header_custom.php /var/www/max2play/application/view/
		cp -f /var/www/max2play/application/plugins/squeezeplug/scripts/custom.css /var/www/max2play/public/
		cp -f /var/www/max2play/application/plugins/squeezeplug/scripts/plugins.xml /var/www/max2play/application/config/plugins.xml
	fi	
	
	#Raspberry: asound.conf.pi (Equalizer Options)
	cp -f max2play/CONFIG_SYSTEM/asound.conf.pi /etc/asound.conf
	
	#Kodi - http://michael.gorven.za.net/
	echo "deb http://archive.mene.za.net/raspbian wheezy contrib" >> /etc/apt/sources.list.d/mene.list
	sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-key 5243CDED
	sudo sudo apt-get update
	echo "Y" | apt-get install kodi
	sudo echo "KERNEL==\"tty[0-9]*\", GROUP=\"tty\", MODE=\"0660\"" >> /etc/udev/rules.d/99-input.rules 
	sudo usermod -a -G tty pi
	sudo sh -c "echo \"gpu_mem=128\" >> /boot/config.txt"
	
	#Hifi Berry - TODO: add to webinterface
	# sudo sh -c "echo \"dtoverlay=hifiberry-dac\" >> /boot/config.txt"
	
	#iqaudio fix
	sudo sh -c "echo \"dtoverlay=iqaudio-dacplus\" >> /boot/config.txt"
	sudo sh -c "echo \"options snd-rpi-iqaudio-dac index=-2\" >> /etc/modprobe.d/alsa-base.conf"	
	
	#Default Soundoutput
	sudo sed -i 's/SQUEEZELITE_PARAMETER.*/SQUEEZELITE_PARAMETER=-o default:CARD=ALSA -a 120::16:/' /opt/max2play/audioplayer.conf	
	sudo sed -i 's/SHAIRPORT_PARAMETER.*/SHAIRPORT_PARAMETER=-d default:CARD=ALSA/' /opt/max2play/audioplayer.conf			
	
	#Add Autostart Kodi / XBMC	
	sudo sed -i 's/^exit 0/#Max2Play\nsudo -u pi -H -s \/opt\/max2play\/autostart_xbmc.sh > \/dev\/null 2>\&1 \&\n\nexit 0/' /etc/rc.local
	sudo sed -i 's/^exit 0/#Max2Play Start Audioplayer\nsudo -u pi -H -s \/opt\/max2play\/start_audioplayer.sh > \/dev\/null 2>\&1 \&\n\nexit 0/' /etc/rc.local
	
	#Remove Bash history & Clean up the system
	apt-get --yes autoremove
	apt-get --yes autoclean
	apt-get --yes clean
	rm /root/.bash_history
	cd /
	history -c
	
	echo "TODO: Update to latest Version in Webinterface - Check hd-idle (1.5 might not work)"
	echo "TODO: Run raspbi-config at least one time AND Reboot!"	
fi

pushd $CWD
#Sudoers
cp -f max2play/CONFIG_SYSTEM/sudoers.d/max2play /etc/sudoers.d/
#Network
cp -f max2play/CONFIG_SYSTEM/network/* /etc/network/
chmod 666 /etc/network/*
#Samba
cp -f max2play/CONFIG_SYSTEM/samba/smb.conf /etc/samba/
#Udev Rules
cp -f max2play/CONFIG_SYSTEM/udev/rules.d/* /etc/udev/rules.d/

#Sound Volume to 100%
echo "Gesamtlautstärke auf 100% setzen: alsamixer"
sudo su - $USER -c 'amixer -q set "PCM" 100'
sudo amixer -q set "PCM" 100
sudo alsactl store 0

#Add Net-Availability Check for Mountpoints to /etc/rc.local
sudo sed -i "s/^exit 0/#Network Check for Mountpoints\nCOUNTER=0;while \[ -z \"\$\(\/sbin\/ifconfig eth0 \| grep -i 'inet ad'\)\" -a -z \"\$\(\/sbin\/ifconfig wlan0 \| grep -i 'inet ad'\)\" -a \"\$COUNTER\" -lt \"5\" \]; do echo \"Waiting for network\";COUNTER=\$\(\(COUNTER+1\)\);sleep 3;done;\/bin\/mount -a\n\nexit 0/" /etc/rc.local

#Change Password to default
if [ "$CHANGE_PASSWORD" = "Y" ]; then 
	echo -e "max2play\nmax2play\n" | passwd
fi
if [ "$CHANGE_HOSTNAME" = "" ]; then
	cat /etc/hostname > /opt/max2play/playername.txt
	cat /etc/hostname > /opt/max2play/playername.txt.sav
else
	echo "$CHANGE_HOSTNAME" > /etc/hostname
fi
chmod 666 /etc/hostname


#ODROID U3:
if [ "$HW_ODROID" -gt "0" ]; then
	pushd $CWD
	
	echo "SYSTEM_USER=odroid" >> /opt/max2play/audioplayer.conf   	
	
	#### Squeezeboxserver unter Ubuntu 14.04 (Perl 5.18) ####			
	ln -sf /usr/lib/arm-linux*/libgif.a /usr/lib/libungif.a
	ln -sf /usr/lib/arm-linux*/libgif.so /usr/lib/libungif.so	
	
	pushd $CWD
	echo "CPAN-Fixes auf Image kopieren für Perl 5.18"
	tar xfvz max2play/CPAN-7.9_7.8_Perl5.18.tar.gz -C /opt
	
	# ODROID: asound.conf (Equalizer Options)
	cp -f max2play/CONFIG_SYSTEM/asound.conf /etc/asound.conf
	
	#Desktopbackground kopieren und einrichten
	cp -f max2play/OTHER/m2p_odroid_desktop.jpg /home/odroid/Pictures/
	cp -rf max2play/CONFIG_USER/pcmanfm/ /home/odroid/.config
	
	# Shortcut XBMC
	cp -rf max2play/CONFIG_USER/Desktop/* /home/odroid/Desktop	
	
	# Autostart XBMC
	cp -rf max2play/CONFIG_USER/lxsession/Lubuntu/* /home/odroid/.config/lxsession/Lubuntu
	
	# eth0 Start by ifplugd 
	cp -rf max2play/CONFIG_SYSTEM/default/ifplugd /etc/default/ifplugd
	
	#Default Soundoutput
	sudo sed -i 's/SQUEEZELITE_PARAMETER.*/SQUEEZELITE_PARAMETER=-o plug:dmixer/' /opt/max2play/audioplayer.conf	
	sudo sed -i 's/SHAIRPORT_PARAMETER.*/SHAIRPORT_PARAMETER=-d plug:dmixer/' /opt/max2play/audioplayer.conf	
	
	#ODROID C1:
	#udev persistent net rules Mac-Adresse von eth0 ist falsch
	echo "Y" | apt-get install iw
	#nano /etc/default/autogetty # remove enabled for 100%CPU usage bash
	
	#Rights to start XBMC/Kodi
	sed -i 's@www-data:/var/www:/usr/sbin/nologin@www-data:/var/www:/bin/bash@' /etc/passwd
	
	#for Shairtunes 
	echo "Y" | sudo apt-get install libao-dev
	echo "y\ny" | perl -MCPAN -e 'install IO::Socket::INET6'
	
	# Disable IPv6
	#echo "net.ipv6.conf.all.disable_ipv6 = 1" >> /etc/sysctl.conf
	#echo "net.ipv6.conf.default.disable_ipv6 = 1" >> /etc/sysctl.conf
	#echo "net.ipv6.conf.lo.disable_ipv6 = 1" >> /etc/sysctl.conf		
	
	# Odroid Wheezy Debian	
	if [ "$LINUX" == "Debian" ]; then
		echo "Y" | apt-get install iw 
		sudo usermod -a -G audio odroid
		sudo usermod -a -G video odroid	
		echo "/usr/local/lib" >> /etc/ld.so.conf.d/arm-linux-gnueabihf.conf
		ldconfig
		# solve Problem with new Modulename in Equalizer
		sed -i 's/module "Eq10";//' /etc/asound.conf
		
		# TODO: Kodi/XBMC install by webinterface not working
		#Add Autostart Kodi / XBMC - Only works if user is auto logged in
		sudo sed -i 's/^exit 0/#Max2Play\nsudo -u odroid -H -s \/opt\/max2play\/autostart_xbmc.sh > \/dev\/null 2>\&1 \&\n\nexit 0/' /etc/rc.local
				
		echo "TODO: set autologin for user odroid - if no user is logged in, XBMC will not start from webinterface"
	else
		#Powerkey Handling on U3
		sed -i 's/value=3/value=0/' /etc/xdg/xfce4/xfconf/xfce-perchannel-xml/xfce4-power-manager.xml 
		sed -i 's/#HandlePowerKey=.*/HandlePowerKey=ignore/' /etc/systemd/logind.conf
	fi
	
	echo "TODO: Remove LAN-Address before saving Image (generates new one on first start): rm /etc/smsc95xx_mac_addr"
	echo "TODO: ODROID C1: use asound.conf.c1, udev persistant net rules eth0, install iw, nano /etc/default/autogetty -> remove "
	echo "TODO: Update to latest Version in Webinterface! IMPORTANT - otherwise some Scripts are missing!"
	echo "TODO: REBOOT !!!"
fi

#Remove Install Files in local directory
rm -R max2play
rm -R max2play_complete.zip
rm install_max2play.sh
