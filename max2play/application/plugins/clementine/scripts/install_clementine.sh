#!/bin/bash

cd /opt/max2play/cache
wget http://ftp.de.debian.org/debian/pool/main/c/clementine/clementine_1.0.1+dfsg-2+b1_armhf.deb
dpkg -i clementine_1.0.1+dfsg-2+b1_armhf.deb 
wget http://ftp.de.debian.org/debian/pool/main/libe/libechonest/libechonest1.2_1.2.1-1_armhf.deb
dpkg -i libechonest1.2_1.2.1-1_armhf.deb
wget http://ftp.de.debian.org/debian/pool/main/libi/libimobiledevice/libimobiledevice2_1.1.1-4_armhf.deb
wget http://ftp.de.debian.org/debian/pool/main/u/usbmuxd/libusbmuxd1_1.0.7-2_armhf.deb
dpkg -i libusbmuxd1_1.0.7-2_armhf.deb 
wget http://ftp.de.debian.org/debian/pool/main/libt/libtasn1-3/libtasn1-3_2.13-2+deb7u1_armhf.deb
dpkg -i libtasn1-3_2.13-2+deb7u1_armhf.deb 
dpkg -i libimobiledevice2_1.1.1-4_armhf.deb 
wget http://ftp.de.debian.org/debian/pool/main/libl/liblastfm/liblastfm0_0.4.0~git20090710-2_armhf.deb
dpkg -i liblastfm0_0.4.0~git20090710-2_armhf.deb 
wget http://ftp.de.debian.org/debian/pool/main/p/projectm/libprojectm2_2.1.0+dfsg-1_armhf.deb
dpkg -i libprojectm2_2.1.0+dfsg-1_armhf.deb 
wget http://ftp.de.debian.org/debian/pool/main/g/glew/libglew1.7_1.7.0-3_armhf.deb
dpkg -i libglew1.7_1.7.0-3_armhf.deb 
wget http://ftp.de.debian.org/debian/pool/main/f/ftgl/libftgl2_2.1.3~rc5-4_armhf.deb
dpkg -i libftgl2_2.1.3~rc5-4_armhf.deb 
wget http://ftp.de.debian.org/debian/pool/main/t/ttf-dejavu/ttf-dejavu-core_2.33-3_all.deb
dpkg -i ttf-dejavu-core_2.33-3_all.deb 
dpkg -i libprojectm2_2.1.0+dfsg-1_armhf.deb 
echo "Y" | apt-get -f install
wget http://ftp.de.debian.org/debian/pool/main/g/gst-plugins-good0.10/gstreamer0.10-pulseaudio_0.10.31-3+nmu1_armhf.deb
dpkg -i gstreamer0.10-pulseaudio_0.10.31-3+nmu1_armhf.deb
wget http://ftp.de.debian.org/debian/pool/main/g/gst-plugins-base0.10/gstreamer0.10-alsa_0.10.36-1.1_armhf.deb
dpkg -i gstreamer0.10-alsa_0.10.36-1.1_armhf.deb
#Create Desktop Icon
cp /var/www/max2play/application/plugins/clementine/scripts/clementine.desktop /home/odroid/Desktop/
echo "Clementine Install Finished"
