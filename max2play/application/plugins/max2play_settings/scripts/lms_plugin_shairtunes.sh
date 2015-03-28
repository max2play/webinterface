#!/bin/bash
#Installer Script for Plugin Shairtunes
apt-get install libcrypt-openssl-rsa-perl libio-socket-inet6-perl libwww-perl avahi-utils libio-socket-ssl-perl --yes --force-yes
wget -O /opt/max2play/cache/libnet-sdp-perl.deb http://www.inf.udec.cl/~diegocaro/rpi/libnet-sdp-perl_0.07-1_all.deb
dpkg -i /opt/max2play/cache/libnet-sdp-perl.deb

wget -O /opt/max2play/cache/ShairTunes.zip https://raw.github.com/StuartUSA/shairport_plugin/master/ShairTunes.zip
mkdir /opt/max2play/cache/ShairTunes
unzip /opt/max2play/cache/ShairTunes.zip  shairport_helper/pre-compiled/shairport_helper-armhf -d /opt/max2play/cache/ShairTunes
cp /opt/max2play/cache/ShairTunes/shairport_helper/pre-compiled/shairport_helper-armhf /usr/local/bin/shairport_helper

#Path /var/lib/squeezeboxserver/cache/InstalledPlugins/Plugins/
cp /opt/max2play/cache/ShairTunes.zip /var/lib/squeezeboxserver/cache/DownloadedPlugins/
echo "ShairTunes: needs-install" >> /var/lib/squeezeboxserver/prefs/plugin/state.prefs

echo "Finished"