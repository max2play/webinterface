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
# TODO Check SSH Deletes
#rm /etc/ssh/*key
#rm /etc/ssh/*pub 

# Clear Log Files!
rm /var/log/syslog

# Replace Name of Host
sed -i "s/raspberrypi/max2play/" /etc/hosts 

# Add Autoresize to rc.local
#sed -i "s@^exit 0@if [ \"\$(grep -a 'AUTORESIZE=1' /boot/config.txt)\" ]; then \n  sed -i 's/AUTORESIZE=1//' /boot/config.txt\n  /opt/max2play/expandfs.sh mmcblk0p2 REBOOT\nfi\nexit 0@" /etc/rc.local
#echo "AUTORESIZE=1" >> /boot/config.txt

#TODO: delete premium-license, set default settings?
# Deactivate ipv6
# Activate Soundcard Selector

su -l pi -c "history -c"
rm /root/.bash_history
history -c

echo "TODO: Clean old modules / Kernel Files"
echo "TODO: Upgrade / Update to latest Kernel and Update Packages"
echo "TODO: Add Autoresize to Boot config"
echo "TODO: Final Check Webinterface!"
echo "finished"
exit 0
