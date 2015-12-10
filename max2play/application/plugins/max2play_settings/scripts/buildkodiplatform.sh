#!/bin/bash
# Get PVR-Addons Working on all Systems
# nötige Pakete installieren
apt-get install libtinyxml-dev -y

# get PVR Addon working on Raspberry Pi
if [ "$(cat /proc/cpuinfo | grep Hardware | grep BCM270 | wc -l)" -gt "0" ]; then
	echo "Set Compiler to 4.7 version"
	apt-get install fonts-roboto javascript-common libhdhomerun1 libjs-jquery -y	
	apt-get install gcc-4.7 g++-4.7 -y
	update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-4.6 60 --slave /usr/bin/g++ g++ /usr/bin/g++-4.6
    update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-4.7 40 --slave /usr/bin/g++ g++ /usr/bin/g++-4.7
    echo "2" | update-alternatives --config gcc
fi
# Platform installieren: http://forum.kodi.tv/showthread.php?tid=220990&pid=2013039
cd /opt
git clone https://github.com/Pulse-Eight/platform.git
cd platform
cmake -DCMAKE_INSTALL_PREFIX=/usr/local
make && sudo make install

# Zuerst Kodi Plattform installieren und VORHER kodi in Version 15.1 kompilieren / installieren
cd /opt
git clone https://github.com/xbmc/kodi-platform.git
cd kodi-platform
cmake -DCMAKE_INSTALL_PREFIX=/usr/local
# Achtung: new p8-platform Files!
# Change needed
sed -i "s@-lplatform@-lp8-platform@" /opt/kodi-platform/CMakeFiles/kodiplatform.dir/link.txt
make && sudo make install
ldconfig

if [ "$(cat /proc/cpuinfo | grep Hardware | grep BCM270 | wc -l)" -gt "0" ]; then
	echo "Reset Compiler to first version"
	echo "0" | update-alternatives --config gcc
fi
