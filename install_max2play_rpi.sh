#!/bin/bash
echo "#### Max2Play-Installer for Raspberry PI Bookworm Raspbian Lite ####"
echo "This script installs Max2Play-Scripts to /opt/max2play and the webinterface to /var/www/max2play"
echo " - On first start it will do an update/upgrade and expand filesystem and get Max2Play files - then it automatically rebootes"
echo " - On second start it installs all the fancy stuff and brings the webinterface to life"
echo "Edit Parameters on top of script to change the default behavior of this script!"
echo ""
echo "Add Execute rights with 'chmod 777 install_max2play.sh'"
echo "RUN with 'sudo install_max2play.sh 2>&1 | tee install_max2play.log' to save Install-Logfile and see output on console!"
echo ""

# expand Filesystem during install
EXPAND_FILESYSTEM="N"

# set to Y if you want default password "max2play"
CHANGE_PASSWORD="N" 

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

HW_RASPBERRY=$(cat /proc/cpuinfo | grep Hardware | grep -i "BCM2708\|BCM2709\|BCM2837\|BCM2835\|BCM2836" | wc -l)
if [ "$HW_RASPBERRY" -lt "1" ]; then
  HW_RASPBERRY=$(cat /proc/cpuinfo | grep Model | grep -i "Raspberry" | wc -l)
fi
if [ "$HW_RASPBERRY" -lt "1" ]; then
  echo "No Raspberry Found"
  exit
fi

LINUX=$(lsb_release -a 2>/dev/null | grep Distributor | sed "s/Distributor ID:\t//")
RELEASE=$(lsb_release -a 2>/dev/null | grep Codename | sed "s/Codename:\t//")

if [ "$LINUX" == "Raspbian" ]; then
	LINUX="Debian"
fi
echo "Linux is $LINUX"
echo "Release is $RELEASE"

USER=pi
echo "Hardware is Raspberry"
echo "Starting from Raspbian Jessie Lite you should: expand the filesystem to a size little lower than 4 GB before setting up Max2Play"
p2_start=`fdisk -l /dev/mmcblk0 | grep mmcblk0p2 | awk '{print $2}'`
p2_end_current=`fdisk -l /dev/mmcblk0 | grep mmcblk0p2 | awk '{print $3}'`
if [ "$p2_end_current" -lt "7000000" ]; then
  echo "Do this by checking this values:"
  echo "Partition 2 Start: $p2_start , Partition 2 current end: $p2_end_current"
  echo "Raise Partition 2 to at least 7000 by:"
  echo -e "fdisk /dev/mmcblk0 <<EOF &>> resize\np\nd\n2\nn\np\n2\n$p2_start\n7000000\np\nw\nEOF\n"
  echo "Execute 'sed -i \"s@^exit 0@resize2fs /dev/mmcblk0p2;sed -i \\\"s=resize.*==\\\" /etc/rc.local\nexit 0@\" /etc/rc.local' and reboot to finish Filesystem expand!"
  exit 1
fi
# Remove further not wanted packages?
echo "Y" | sudo apt-get remove wolfram-engine

FREESPACE=$(df -km | grep /dev/mmcblk0p2 | tail -1 | awk '{print $4}')
if [ "$FREESPACE" -lt "500" ]; then
  echo "Only $FREESPACE MB memory available - Run sudo raspbi-config.sh first to expand filesystem manually and Reboot!"
  exit 1
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
	# TODO: check dynamic script needs write access for www-data...

	#Expand FS!
	if [ "$EXPAND_FILESYSTEM" == "Y" ]; then
		sudo /opt/max2play/expandfs.sh
		echo "Expand Filesystem finished"
		reboot
		exit 0
	fi
fi

chmod 666 /etc/fstab
echo -e "\n##USERMOUNT" >> /etc/fstab
cp /etc/fstab /etc/fstab.sav
chmod 666 /etc/fstab.sav

crontab -u $USER -l > cronmax2play
sudo echo "* * * * * /opt/max2play/start_audioplayer.sh > /dev/null 2>&1" >> cronmax2play
crontab -u $USER cronmax2play
rm cronmax2play

sudo echo "Y" | apt-get install apache2 php php-json php-xml -y

sudo a2enmod rewrite
rm /etc/apache2/sites-enabled/000-default.conf /etc/apache2/sites-enabled/000-default
cp max2play/CONFIG_SYSTEM/apache2/sites-enabled/max2play.conf /etc/apache2/sites-enabled/
sed -i 's/LogLevel warn/LogLevel error/' /etc/apache2/apache2.conf
cp -r max2play/max2play/ /var/www/max2play 
sudo /etc/init.d/apache2 restart

sudo echo "Y" | apt-get install samba samba-common samba-common-bin mc ntfs-3g cifs-utils nfs-common git libconfig-dev smbclient debconf-utils ifplugd nmap

echo "Generate Locales for predefined languages..."
sed -i 's/# de_DE.UTF-8 UTF-8/de_DE.UTF-8 UTF-8/;s/# it_IT.UTF-8 UTF-8/it_IT.UTF-8 UTF-8/;s/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/;s/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/;s/# en_GB.UTF-8 UTF-8/en_GB.UTF-8 UTF-8/' /etc/locale.gen
locale-gen

export LANG=en_GB.UTF-8
dpkg-reconfigure -f noninteractive locales
echo "Europe/Berlin" > /etc/timezone
ln -fs /usr/share/zoneinfo/`cat /etc/timezone` /etc/localtime
dpkg-reconfigure -f noninteractive tzdata

sudo echo "Y" | apt-get remove xscreensaver

# HD-Idle aktivieren
wget http://ftp.de.debian.org/debian/pool/main/h/hd-idle/hd-idle_1.05+ds-2~bpo10+1_armhf.deb
dpkg -i hd-idle_1.05+ds-2~bpo10+1_armhf.deb
sudo sed -i 's/START_HD_IDLE=.*/START_HD_IDLE=true/' /etc/default/hd-idle

sudo echo "Y" | apt-get install usbmount
cp -f max2play/CONFIG_SYSTEM/usbmount/usbmount.conf /etc/usbmount/usbmount.conf


### Alsaeuqal
echo "Y" | apt-get install nettle-dev caps libasound2-dev
pushd /tmp
# switch to Github
#wget http://www.thedigitalmachine.net/tools/alsaequal-0.6.tar.bz2
#tar xvjf alsaequal-0.6.tar.bz2 
git clone https://github.com/raedwulf/alsaequal.git
cd alsaequal/
patch ctl_equal.c < $CWD/max2play/OTHER/alsaequal_ctl_equal.patch
make
mkdir /usr/lib/alsa-lib
make install
mkdir /usr/lib/arm-linux-gnueabihf/alsa-lib/
cp -R /usr/lib/alsa-lib/* /usr/lib/arm-linux-gnueabihf/alsa-lib/
cp /usr/lib/ladspa/caps.so /usr/lib/

#Squeezelite
apt-get install gstreamer1.0-libav cmake -y

# Debian Wheezy soxr
apt-get install libavformat-dev libmpg123-dev libfaad-dev libvorbis-dev libmad0-dev libflac-dev libasound2-dev -y
apt-get install ffmpeg -y
apt-get install liblircclient-dev -y
# TODO:  WiringPI not working anymore -> replacement needed...
pushd /tmp
wget -O soxr.tar.gz --max-redirect=3 "http://downloads.sourceforge.net/project/soxr/soxr-0.1.1-Source.tar.xz"
tar -xf soxr.tar.gz
cd soxr-*
./go
cd Release
make install


pushd /tmp
# Use modified Squeezelite from Max2Play (Bluetoothsync, Alsasync)!
git clone https://github.com/max2play/squeezelite
cd squeezelite
make -f Makefile.m2p
mkdir /opt/squeezelite
mkdir /opt/squeezelite/log
chmod 777 /opt/squeezelite/log
cp /tmp/squeezelite/squeezelite-m2p /opt/squeezelite/squeezelite
cp /tmp/squeezelite/scripts/btcheck.sh /opt/squeezelite/
cp /tmp/squeezelite/scripts/btrecon.sh /opt/squeezelite/
chmod 777 /opt/squeezelite/btcheck.sh /opt/squeezelite/btrecon.sh
pushd $CWD

#### Squeezeboxserver Basic ####
apt-get install libungif-bin -y

#### Shairport install
apt-get install libssl-dev libavahi-client-dev libasound2-dev autoconf libtool libdaemon-dev libpopt-dev -y
pushd /tmp
# Switch between Shairport AND Shairport-Sync!
# install Shairport-Sync
apt-get install avahi-daemon -y
git clone https://github.com/mikebrady/shairport-sync.git
cd shairport*
autoreconf -i -f

./configure --with-alsa --with-avahi --with-ssl=openssl --with-soxr

make
mkdir -p /opt/shairport/log
chmod 777 /opt/shairport/log
cp -f shairport-sync /opt/shairport/shairport
pushd $CWD


#### Install DLNA CLIENT ####

#Doesnt work on Ubuntu 14.04
pushd /tmp

if [ "$RELEASE" == "buster" ]; then
  # git clone https://github.com/coldtobi/gmrender-resurrect.git --branch upnp-18
  # sudo apt-get install libupnp-dev libgstreamer0.10-dev libglib2.0-dev gstreamer0.10-plugins-base gstreamer0.10-alsa -y
  sudo apt install gstreamer1.0-alsa gstreamer1.0-plugins-base gstreamer1.0-plugins-good gstreamer1.0-plugins-ugly gstreamer1.0-plugins-bad gstreamer1.0-gl gmediarender -y
  sudo cp /usr/bin/gmediarender /usr/local/bin/gmediarender
else
  git clone https://github.com/hzeller/gmrender-resurrect.git
fi
cd gmrender-resurrect
echo "Y" | sudo apt-get install autoconf automake libtool
echo "Y" | sudo apt-get install libupnp-dev libgstreamer0.10-dev \
    gstreamer0.10-plugins-base gstreamer0.10-plugins-good \
    gstreamer0.10-plugins-bad gstreamer0.10-plugins-ugly \
    gstreamer0.10-pulseaudio gstreamer0.10-alsa -y
#gstreamer0.10-ffmpeg -> debian jessie not available
sudo ./autogen.sh
sudo ./configure
sudo make
sudo make install
pushd $CWD



#fix exzessives Logging in syslog & co (cron)
cp -f max2play/CONFIG_SYSTEM/rsyslog.conf /etc/rsyslog.conf

#Copy Config Files / Update Max2Play einmalig nötig
echo "1.0" > /var/www/max2play/application/config/version.txt

#Save default audioplayer config
cp /opt/max2play/audioplayer.conf /opt/max2play/audioplayer.conf.sav

pushd $CWD
	
# Lirc installieren
apt-get install lirc -y
cp /etc/lirc/lirc_options.conf.dist /etc/lirc/lirc_options.conf
apt-get install -f
# Dhcpcd not needed because of etc/network/interfaces
systemctl disable dhcpcd

#Raspberry PI: User PI nutzen!
echo "SYSTEM_USER=pi" >> /opt/max2play/audioplayer.conf

#Usbmount Fix
sudo sed -i 's/odroid/pi/' /etc/usbmount/usbmount.conf

#Raspberry: asound.conf.pi (Equalizer Options)
cp -f max2play/CONFIG_SYSTEM/asound.conf.pi /etc/asound.conf

#Kodi - http://michael.gorven.za.net/ - Get Kodi from Repository
#echo "deb http://archive.mene.za.net/raspbian wheezy contrib" >> /etc/apt/sources.list.d/mene.list
#sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-key 5243CDED
#sudo sudo apt-get update
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
sudo sed -i 's/SQUEEZELITE_PARAMETER.*/SQUEEZELITE_PARAMETER=-o sysdefault:CARD=ALSA -a 80::: -C 5/' /opt/max2play/audioplayer.conf
sudo sed -i 's/SHAIRPORT_PARAMETER.*/SHAIRPORT_PARAMETER=-d sysdefault:CARD=ALSA/' /opt/max2play/audioplayer.conf

#Add Autostart Kodi / XBMC
sudo sed -i 's/^exit 0/#Max2Play\nsudo -u pi -H -s \/opt\/max2play\/autostart_xbmc.sh > \/dev\/null 2>\&1 \&\n\nexit 0/' /etc/rc.local

#Remove Bash history & Clean up the system
apt-get --yes autoremove
apt-get --yes autoclean
apt-get --yes clean
rm /root/.bash_history
cd /
history -c

echo "TODO: Update to latest Version in Webinterface - Check hd-idle (1.5 might not work)"
echo "TODO: Run raspbi-config at least one time AND Reboot!"

#Debian Jessie Lite:
if [ "$RELEASE" == "jessie" -o "$RELEASE" == "stretch" -o "$RELEASE" == "buster" ]; then
  pushd $CWD
  # optional: run some fixes when upgrading from wheezy
  # https://www.raspberrypi.org/forums/viewtopic.php?t=121880

  echo "Debian Jessie - run fixes for Jessie Lite"

  # install lxde with openbox http://lxlinux.com/
  apt-get install openbox xinit xorg lxde-common lxpanel pcmanfm desktop-file-utils rpi-update -y

  # eth0 Start by ifplugd
  cp -rf max2play/CONFIG_SYSTEM/default/ifplugd /etc/default/ifplugd

  if [ "$RELEASE" == "stretch" -o "$RELEASE" == "buster" ]; then
     echo "Change Network Device Names back to old style eth0"
     if [ "$(grep 'net.ifnames=0' /boot/cmdline.txt | wc -l)" -lt "1"  ]; then
        sed -i 's/rootwait/net.ifnames=0 biosdevname=0 avoid_safe_mode=1 rootwait/' /boot/cmdline.txt
     fi
     echo "Set Autologin to Desktop"
     echo -e "[SeatDefaults]\ngreeter-session=lightdm-gtk-greeter\nautologin-user=pi" >> /usr/share/lightdm/lightdm.conf.d/60-lightdm-gtk-greeter.conf
     echo "Switch Autostart Desktop - Disable autostart"
     systemctl disable lightdm.service

     # Usbmount Fix not needed: https://unix.stackexchange.com/questions/330094/udev-rule-to-mount-disk-does-not-work/330156#330156
     # sed -i "s/^MountFlags=.*/MountFlags=shared/" /lib/systemd/system/systemd-udevd.service
     # Usbmount use Service and bind to Udev Rule
     echo -e "[Unit]\nBindTo=%i.device\nAfter=%i.device\n\n[Service]\nType=oneshot\nTimeoutStartSec=0\nEnvironment=DEVNAME=%I\nExecStart=/usr/share/usbmount/usbmount add\nRemainAfterExit=yes" > /etc/systemd/system/usbmount@.service
     echo -e "# Rules for USBmount -*- conf -*-\nKERNEL==\"sd??\", DRIVERS==\"sbp2\",         ACTION==\"add\",  PROGRAM=\"/bin/systemd-escape -p --template=usbmount@.service \$env{DEVNAME}\", ENV{SYSTEMD_WANTS}+=\"%c\"\nKERNEL==\"sd??\", SUBSYSTEMS==\"usb\",       ACTION==\"add\",  PROGRAM=\"/bin/systemd-escape -p --template=usbmount@.service \$env{DEVNAME}\", ENV{SYSTEMD_WANTS}+=\"%c\"\nKERNEL==\"ub*\", SUBSYSTEMS==\"usb\",       ACTION==\"add\",  PROGRAM=\"/bin/systemd-escape -p --template=usbmount@.service \$env{DEVNAME}\", ENV{SYSTEMD_WANTS}+=\"%c\"\nKERNEL==\"sd??\",                          ACTION==\"remove\",       RUN+=\"/usr/share/usbmount/usbmount remove\"\nKERNEL==\"ub*\",                          ACTION==\"remove\",       RUN+=\"/usr/share/usbmount/usbmount remove\"" > /etc/udev/rules.d/usbmount.rules
     rm /lib/udev/rules.d/usbmount.rules

     echo "Remove spam logging of lircd"
     echo "If \$syslogtag contains 'lircd' and \$msg contains 'Error: Cannot glob' then stop" >> /etc/rsyslog.d/lircd-trash.conf
     systemctl restart rsyslog

     echo "TODO: MANUAL BEFORE FIRST BOOT if you want to make a Master-Image!!! Remove Autoresize Filesystem from Command Line on First Boot! init=/usr/lib/raspi-config/init_resize.sh"
  fi

  #set to anybody for access as user pi
  sed -i "s@^allowed_users=.*@allowed_users=anybody@" /etc/X11/Xwrapper.config

  #Wallpaper
  cp -f max2play/OTHER/m2p_odroid_desktop.jpg /home/pi/m2p_desktop.jpg
  echo "TODO: this will need a restart first... execute separetly"
  sed -i "s@^wallpaper=.*@wallpaper=/home/pi/m2p_desktop.jpg@" /home/pi/.config/pcmanfm/LXDE/desktop-items-0.conf

  #Disable Screensaver
  sed -i 's/@xscreensaver.*$//' /etc/xdg/lxsession/LXDE/autostart
  apt-get remove xscreensaver -y

  # Pulseaudio Crackling sound?
  #sed -i 's/^load-module module-udev-detect$/load-module module-udev-detect tsched=0/' /etc/pulse/default.pa

  #Disable IPv6 for Apache
  sed -i 's/Listen 80/Listen 0.0.0.0:80/' /etc/apache2/ports.conf

  echo "put setupstartpage=1 to /opt/max2play/options.conf"
  echo "optional: run raspbi-config and choose wait for network at boot, as this is done in rc.local!"

  # Howto: https://scribles.net/customizing-boot-up-screen-on-raspberry-pi/
  echo "optional edit Bootscreen and Splashscreen"
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

#Add Net-Availability Check for Mountpoints to /etc/rc.local and make it more robust with "set +e"
sudo sed -i "s/^exit 0/#Network Check for Mountpoints\nCOUNTER=0;while \[ -z \"\$\(\/sbin\/ip addr show eth0 \| grep -i 'inet '\)\" -a -z \"\$\(\/sbin\/ip addr show wlan0 \| grep -i 'inet '\)\" -a \"\$COUNTER\" -lt \"5\" \]; do echo \"Waiting for network\";COUNTER=\$\(\(COUNTER+1\)\);sleep 3;done;set +e;\/bin\/mount -a;set -e;\n\nexit 0/" /etc/rc.local

# Autostart Audioplayer in rc.local (not waiting for cron)
sudo sed -i "s/^exit 0/#Max2Play Start Audioplayer\nsudo -u $USER -H -s \/opt\/max2play\/start_audioplayer.sh > \/dev\/null 2>\&1 \&\n\nexit 0/" /etc/rc.local

#Change Password to default
if [ "$CHANGE_PASSWORD" = "Y" ]; then
	echo -e "max2play\nmax2play\n" | passwd
fi
if [ "$CHANGE_HOSTNAME" = "" ]; then
	cat /etc/hostname > /opt/max2play/playername.txt
	cat /etc/hostname > /opt/max2play/playername.txt.sav
else
	echo "$CHANGE_HOSTNAME" > /etc/hostname
	# edit hosts file
	sudo sed -i "s/raspberrypi/$CHANGE_HOSTNAME/" /etc/hosts
fi
chmod 666 /etc/hostname


chmod 777 /opt/max2play/wpa_supplicant.conf

echo "To Install Autoconfig run: "
echo "sed -i \"s@^#Max2Play\\\$@#Max2Play\nif [ -e /boot/max2play.conf ]; then /opt/max2play/autoconfig.sh; fi\n@\" /etc/rc.local" 

#Remove Install Files in local directory
rm -R max2play
rm -R max2play_complete.zip
rm install_max2play.sh
