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
	# Fix IPv6 deaktivieren - Problem: reloadin apache after this fix may crash apache process
	IPV6DISABLED=$(grep -i "Listen 0.0.0.0:80" /etc/apache2/ports.conf | wc -l)
	if [ "$IPV6DISABLED" -lt "1" ]; then 
		echo "Disable IPv6 for Webinterface"		
		sudo sed -i 's/Listen 80/Listen 0.0.0.0:80/' /etc/apache2/ports.conf		
	fi	
	
	NFSINSTALLED=$(dpkg -s nfs-common | grep "Status: install ok" | wc -l)	
	if [ "$NFSINSTALLED" -lt "1" ]; then
		apt-get update
		echo "Y" | apt-get install nfs-common --yes
	fi	
fi

# XU4: check Mac-Adress and remove udev rule with Mac
HW_XU3=$(cat /proc/cpuinfo | grep Hardware | grep -i "ODROID-XU3" | wc -l)
if [ "$HW_XU3" -gt "0" ]; then	
	echo "Remove MAC-Address from UDEV-Rules"
	# cat /etc/udev/rules.d/70-persistent-net.rules remove my Device MAC and eth1 if existing
	sed -i 's/.*00:1e:06:31:06:13.*//' /etc/udev/rules.d/70-persistent-net.rules
	sed -i 's/.*eth1.*//' /etc/udev/rules.d/70-persistent-net.rules
	
	# Fix for wrong FSTAB
	sed -i "s/1##USERMOUNT/1\n\n##USERMOUNT/" /etc/fstab
	
	# Fix IPv6 deaktivieren - Problem: reloadin apache after this fix may crash apache process
	IPV6DISABLED=$(grep -i "Listen 0.0.0.0:80" /etc/apache2/ports.conf | wc -l)
	if [ "$IPV6DISABLED" -lt "1" ]; then 
		echo "Disable IPv6 for Webinterface"		
		sudo sed -i 's/Listen 80/Listen 0.0.0.0:80/' /etc/apache2/ports.conf
	fi
	# Fix for usbmount ntfs/ntfs-3g (otherwise it will not mount correctly)
	apt-get update
	apt-get install at -y
	# Apply Patch for usbmount
	sed -i "s~mount \"-t\$fstype\" \"\${options:+-o\$options}\" \"\$DEVNAME\" \"\$mountpoint\"~echo mount \"-t\$fstype\" \"\${options:\+-o\$options}\" \"\$DEVNAME\" \"\$mountpoint\" >/tmp/usbmount_max2play.sh\n                at -f /tmp/usbmount_max2play.sh now~" /usr/share/usbmount/usbmount
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

#Fix for rc.local file to make Mounting more robust
sed -i 's/done;\/bin\/mount -a/done;set +e;\/bin\/mount -a;set -e;/' /etc/rc.local

HW_RASPBERRY=$(cat /proc/cpuinfo | grep Hardware | grep -i "BCM2708\|BCM2709" | wc -l)
if [ "$HW_RASPBERRY" -gt "0" ]; then
	sudo sed -i 's@/var/lib/mpd:/bin/false@/var/lib/mpd:/bin/bash@' /etc/passwd
	sudo usermod -aG audio mpd
	sudo sed -i 's/odroid/pi/' /etc/usbmount/usbmount.conf
	#Fix for rc.local file	
	sed -i 's/let \"COUNTER++\"/COUNTER=\$\(\(COUNTER+1\)\)/;s/\;mount/\;\/bin\/mount/' /etc/rc.local
	
	#Fix for missing SYSTEM_USER in audioplayer.conf and wrong audioplayer.conf.sav on image creation
	if [ $(grep -i "SYSTEM_USER" /opt/max2play/audioplayer.conf.sav | wc -l) -lt "1" ]; then
		echo "SYSTEM_USER=pi" >> /opt/max2play/audioplayer.conf.sav
		sed -i 's/SQUEEZELITE_PARAMETER.*/SQUEEZELITE_PARAMETER=-o default:CARD=ALSA -a 120::16:/' /opt/max2play/audioplayer.conf.sav
		sed -i 's/SHAIRPORT_PARAMETER.*/SHAIRPORT_PARAMETER=-d default:CARD=ALSA/' /opt/max2play/audioplayer.conf.sav
	fi
	
	echo "Y" | apt-get install ntfs-3g lsb-release
	#Remove "-a 120::16:" from squeezelite_parameter due to fixed sample rate
	sed -i 's/\-a 120::16:/\-a 120:::/' /opt/max2play/audioplayer.conf
	#Copy Squeezeplug custom.css and Header Files if Existing
	if [ -e "/var/www/max2play/application/plugins/squeezeplug/view/header_custom.php" ]; then
	    # Update Plugin squeezeplug
		/opt/max2play/install_plugin.sh http://shop.max2play.com/media/downloadable/beta/squeezeplug.tar
	    echo "Copy custom header files"
	    cp -f /var/www/max2play/application/plugins/squeezeplug/view/header_custom.php /var/www/max2play/application/view/
	    cp -f /var/www/max2play/application/plugins/squeezeplug/scripts/custom.css /var/www/max2play/public/
	    mkdir /var/www/max2play/public/addons/squeezeplug
	    cp -f /var/www/max2play/application/plugins/squeezeplug/scripts/images/* /var/www/max2play/public/addons/squeezeplug/
	    
	    # FIX ERROR remove double Entry in Crontab
	    crontab -u pi -l | /usr/bin/uniq > /opt/max2play/cache/cronmax2play
		crontab -u pi /opt/max2play/cache/cronmax2play
	    rm /opt/max2play/cache/cronmax2play
	fi
	if [ -e "/var/www/max2play/application/plugins/hifiberry/view/header_custom.php" ]; then
	    # Update Plugin Header		
	    echo "Copy custom header files"
	    cp -f /var/www/max2play/application/plugins/hifiberry/view/header_custom.php /var/www/max2play/application/view/
	fi
	
	if [ "$(grep -i "start_audioplayer" /etc/rc.local | wc -l)" -lt "1" ]; then
		# Add Start Audioplayer to boot (not wait for crontab)
		sudo sed -i "s/^exit 0/#Max2Play Start Audioplayer\nsudo -u pi -H -s \/opt\/max2play\/start_audioplayer.sh > \/dev\/null 2>\&1 \&\n\nexit 0/" /etc/rc.local
	fi
fi

#htaccess Password Protection Overwrite Backup
if [ -e "/var/www/max2play/public/.htaccess_add" ]; then
	cat /var/www/max2play/public/.htaccess_add | cat - /var/www/max2play/public/.htaccess > /var/www/max2play/public/.htaccess.tmp && mv /var/www/max2play/public/.htaccess.tmp /var/www/max2play/public/.htaccess
fi

showHelpOnSidebar=$(grep -a "showHelpOnSidebar" /opt/max2play/options.conf | wc -l)
if [ "$showHelpOnSidebar" -lt "1" ]; then
    echo "showHelpOnSidebar=1" >> /opt/max2play/options.conf
    echo "Added Help on Sidebar"
fi
