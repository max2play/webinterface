#!/bin/bash
# XU4: get PVR-Addons Working...
# nötige Pakete installieren
apt-get install libtinyxml-dev -y

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
make && sudo make install
ldconfig

