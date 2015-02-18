#!/bin/sh
#Upsampling Squeezelite
#(echo "y") | apt-get install libsoxr0

#Remove error logging to ubuntu
rm /etc/init/whoopsie.conf

#Remove Cron spamming syslog
rm /etc/rsyslog.d/50-default.conf
crontab -u odroid -l > /opt/max2play/cache/cronodroid
sed -i 's/start_audioplayer.sh$/start_audioplayer.sh > \/dev\/null 2>\&1/' /opt/max2play/cache/cronodroid
crontab -u odroid /opt/max2play/cache/cronodroid
rm /opt/max2play/cache/cronodroid

if [ -e /opt/max2play/factory/max2play_complete.zip ]; then
    echo "Factory Settings available"
else
	mkdir /opt/max2play/factory
	wget http://shop.max2play.com/media/downloadable/currentversion/max2play_complete.zip -O /opt/max2play/factory/max2play_complete.zip	
	echo "Added Factory Settings"
fi

#Add Max2Play Powerbutton to StartUp Only on ODROID U3!
HW_U3=$(cat /proc/cpuinfo | grep Hardware | grep -i "ODROID-U2/U3" | wc -l)
if [ "$HW_U3" -gt "0" ]; then
	powerbutton=$(cat /etc/rc.local | grep pwrbutton | wc -l)
	if [ "$powerbutton" -lt "1" ]; then 
		sed -i 's/^exit 0/\/opt\/max2play\/pwrbutton 2>\&1 > \/dev\/null \&\n\nexit 0/' /etc/rc.local
	fi
	
	#Update Bootoptions for setting resolution from http://forum.odroid.com/viewtopic.php?f=52&t=2947
	if [ -e /media/boot/boot-auto_edid.scr ]; then
		echo "Boot Options for HDMI existing"
	else	
		wget http://builder.mdrjr.net/tools/boot.scr_ubuntu.tar -O /opt/max2play/cache/boot_scr.tar
		tar -xf /opt/max2play/cache/boot_scr.tar -C /opt/max2play/cache
		rm -Rf /opt/max2play/cache/x
		cp /opt/max2play/cache/x2u2/boot-* /media/boot
		rm -Rf /opt/max2play/cache/x2u2
		cp -f /media/boot/boot.scr /media/boot/boot-auto_edid.scr
	fi
fi

#Disable IPv6 - not working correct yet
#ip -6 neigh flush dev eth0
#ip -6 neigh flush dev wlan0
#echo "net.ipv6.conf.all.disable_ipv6 = 1" > /etc/sysctl.d/01-disable-ipv6.conf

#Check if Install was OK (filesize = 0 problem) otherwise try again...
if [ -s /opt/max2play/start_xbmc.sh ]; then
	echo "OK"
else
	echo "FILESIZE WRONG - Retry Installation"
	sleep 2
	if [ -e /var/www/max2play/application/config/plugins.xml ]; then 
		unzip -o /opt/max2play/cache/webinterface.zip -d /var/www -x \*plugins.xml
	else
		unzip -o /opt/max2play/cache/webinterface.zip -d /var/www
	fi
	
	echo "Install Scripts"
	unzip -o /opt/max2play/cache/scripts.zip -d /
fi


HW_RASPBERRY=$(cat /proc/cpuinfo | grep Hardware | grep -i "BCM2708\|BCM2709" | wc -l)
if [ "$HW_RASPBERRY" -gt "0" ]; then
	sudo sed -i 's/odroid/pi/' /etc/usbmount/usbmount.conf
fi