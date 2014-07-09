#!/bin/sh
# Do this if possible as root!
# File is work in progress and not working (more as a reminder what to do...)
# Fix Internet connection

# Get Max2Play
wget shop.max2play.com/media/downloadable/currentversion/max2play_complete.zip
unzip max2play_complete.zip -d max2play

# Update Sources
sudo apt-get update

# Screensaver entfernen
sudo echo "Y" | apt-get remove xscreensaver

# Cronjob für Autostart der Player
#sudo echo "* * * * /opt/max2play/start_audioplayer.sh" >> cron

# Apache2 installieren und Config kopieren
sudo echo "Y" | apt-get install apache2 php5 php5-json
sudo a2enmod rewrite
rm /etc/apache2/sites-enabled/000-default.conf
cp max2play/CONFIG_SYSTEM/apache2/sites-enabled/max2play.conf /etc/apache2/sites-enabled/
cp -r max2play/max2play/ /var/www/max2play 
sudo /etc/init.d/apache2 reload

# Samba aufsetzen für IP-Namensauflösung unter Windows 
sudo echo "Y" | apt-get install samba samba-common

# XBMC über Konsole starten: SSH mit User Login
#  www-data: rechte anpassen für .ssh verzeichnis unter www
#  ssh-keygen -> key export nach linaro

# Shairport Installieren und alle Dienste dazu (ahci, etc.) https://github.com/abrasive/shairport (Doku)

# DAUERHAFT IN MODPROBE SPEICHERN: config für modprobe kopieren
modprobe -rv rtl8192cu;
 
# Sprache anpassen 
sudo apt-get install debconf-utils
locale-gen ru_RU.UTF-8 
locale-gen it_IT.URF-8
locale-gen fr_FR.UFT-8

# nmap installieren für erkennung weiterer Boxen auf port 5002
sudo echo "Y" | apt-get install nmap

# ifplugd installieren -> ETH0 wird nicht auf auto gesetzt in interfaces
sudo apt-get install ifplugd

# Idle Funktion für ext. Festplatte http://blog.sepa.spb.ru/2013/03/precompiled-hd-idle-armhf-deb-package.html 
# -> autostart auf aktiv und Kommentar letzte Zeile raus VORINSTALLIEREN!

# Alsaequal http://www.thedigitalmachine.net/alsaequal.html -> Alsaequal mit Paket Caps installieren

# XBMC 13 Bibliothek
apt-get install nettle-dev
wget http://oph.mdrjr.net/memeka/xbmc_13.0.1-1_armhf.deb
sudo apt-get remove xbmc-frodo-odroid
sudo dpkg -i ./xbmc_13.0.1-1_armhf.deb
sudo apt-mark hold xbmc

# Libshairport
git clone git://github.com/juhovh/shairplay.git
./autogen.sh
./configure
make
sudo make install

# Max2Play Scripte und Dienste
sudo cp -r max2play/opt/ /opt

# Sudoers, Scripts & everything else
pushd max2play/CONFIG_SYSTEM
sudo cp -r . /etc/

# Make some Files Writable
# hostname, ...

#fstab erweitern um ##USERMOUNT

# Remove writeable on sudoers