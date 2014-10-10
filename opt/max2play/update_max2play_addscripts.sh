#!/bin/sh
#Upsampling Squeezelite
(echo "y") | apt-get install libsoxr0

#Remove error logging to ubuntu
rm /etc/init/whoopsie.conf

#Remove Cron spamming syslog
rm /etc/rsyslog.d/50-default.conf
crontab -u odroid -l > /opt/max2play/cache/cronodroid
sed -i 's/start_audioplayer.sh/start_audioplayer.sh > \/dev\/null 2>\&1/' /opt/max2play/cache/cronodroid
crontab -u odroid /opt/max2play/cache/cronodroid
rm /opt/max2play/cache/cronodroid

if [ -e /opt/max2play/factory/max2play_complete.zip ]; then
    echo "Factory Settings available"
else
	wget http://shop.max2play.com/media/downloadable/currentversion/max2play_complete.zip -O /opt/max2play/factory/max2play_complete.zip	
	echo "Added Factory Settings"
fi

#Add Max2Play Powerbutton to StartUp
powerbutton=$(cat /etc/rc.local | grep pwrbutton | wc -l)
if [ "$powerbutton" -lt "1" ]; then 
	sed -i 's/^exit 0/\/opt\/max2play\/pwrbutton 2>\&1 > \/dev\/null \&\n\nexit 0/' /etc/rc.local
fi	

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
