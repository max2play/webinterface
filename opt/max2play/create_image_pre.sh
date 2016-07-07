#!/bin/bash
# Do this if possible as root!
# For Preparation of new SD-Card-Images -> remove history, Mac-Specific settings

if [ -e /home/pi ]; then
		USER=pi
	else
		USER=odroid
fi

apt-get --yes autoremove
apt-get --yes autoclean
apt-get --yes clean

rm /home/$USER/.jivelite/userpath/settings/DesktopJive.lua
rm /etc/dhcpcd.duid
rm /etc/dhcpcd.secret
rm /etc/resolv.conf

# Replace Name of Host
sed -i "s/raspberrypi/max2play/" /etc/hosts 

#TODO: delete premium-license, set default settings?

su -l pi -c "history -c"
rm /root/.bash_history
history -c

echo "finished"
exit 0
