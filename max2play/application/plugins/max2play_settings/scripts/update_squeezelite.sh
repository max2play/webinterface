#!/bin/bash

#$1 is parameter for autostart

if [ "$1" -gt "0" ]; then
	#Deactivate Autostart during Update
	sed -i 's/squeezelite=1/squeezelite=0/' /opt/max2play/autostart.conf
fi

apt-get update
apt-get -y install libav-tools libsoxr-dev lirc liblircclient-dev wiringpi
ldconfig
pushd /tmp
git clone https://github.com/max2play/squeezelite;cd squeezelite
make -f Makefile.m2p
cp /tmp/squeezelite/squeezelite-m2p /opt/squeezelite/squeezelite
cp /tmp/squeezelite/scripts/btcheck.sh /opt/squeezelite/
cp /tmp/squeezelite/scripts/btrecon.sh /opt/squeezelite/
chmod 777 /opt/squeezelite/btcheck.sh /opt/squeezelite/btrecon.sh

if [ "$1" -gt "0" ]; then
	#Reactivate Autostart during Update
	sed -i 's/squeezelite=0/squeezelite=1/' /opt/max2play/autostart.conf
fi

#### Finished ######

echo "Finished Update <b><a href='/plugins/max2play_settings/controller/Basic.php?action=reboot&redirecturl=/plugins/max2play_settings/controller/Squeezeplayer.php'>Please Click here to Reboot Now!</a></b>"


exit 0