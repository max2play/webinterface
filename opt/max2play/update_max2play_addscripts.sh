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
	# Apply Patch for usbmount if not yet patched
	if [ "$(grep -i "max2play" /usr/share/usbmount/usbmount | wc -l)" -lt "1" ]; then 
		sed -i "s~mount \"-t\$fstype\" \"\${options:+-o\$options}\" \"\$DEVNAME\" \"\$mountpoint\"~echo mount \"-t\$fstype\" \"\${options:\+-o\$options}\" \"\$DEVNAME\" \"\$mountpoint\" >/tmp/usbmount_max2play.sh\n                at -f /tmp/usbmount_max2play.sh now~" /usr/share/usbmount/usbmount
	fi
	# Patch wrong patch...
	sed -i "s/echo echo/echo/;s/echo echo/echo/;s/echo echo/echo/;s/echo echo/echo/;s@at -f /tmp/usbmount_max2play.sh now >/tmp/usbmount_max2play.sh@@" /usr/share/usbmount/usbmount
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

HW_RASPBERRY=$(cat /proc/cpuinfo | grep Hardware | grep -i "BCM2708\|BCM2709\|BCM2837" | wc -l)
if [ "$HW_RASPBERRY" -gt "0" ]; then
	#Fix for wrong hostname in Image 2.31
	HOSTNAME=$(cat /etc/hostname)
	if [ ! "$HOSTNAME" = "" ]; then 		
		sudo sed -i "s/raspberrypi/$HOSTNAME/;s/max2play/$HOSTNAME/" /etc/hosts
	fi
	
	#Timeout fix on Start / Stop (90sec wait)
	sudo sed -i "s/#DefaultTimeoutStartSec=.*/DefaultTimeoutStartSec=10s/;s/#DefaultTimeoutStopSec=.*/DefaultTimeoutStopSec=10s/" /etc/systemd/system.conf
	
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
	
	EXPECTINSTALLED=$(dpkg -s expect | grep "Status: install ok" | wc -l)
	if [ "$EXPECTINSTALLED" -lt "1" ]; then
		apt-get update
		echo "Y" | apt-get install ntfs-3g lsb-release expect -y
	fi
	
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
	
	#get Version (beta/currentversion)
	ISBETA=$(grep -i "beta" /var/www/max2play/application/config/version.txt | wc -l)
	if [ "$ISBETA" -lt "1" ]; then
	    VERSION="currentversion"
	else
		VERSION="beta"
	fi
	
	if [ -e "/var/www/max2play/application/plugins/allo/view/header_custom.php" ]; then
	    # Update Plugin Header		
	    echo "Copy custom header files Allo"
	    cp -f /var/www/max2play/application/plugins/allo/view/header_custom.php /var/www/max2play/application/view/
	    cp -f /var/www/max2play/application/plugins/allo/scripts/custom.css /var/www/max2play/public/
	fi
	if [ -e "/var/www/max2play/application/plugins/hifiberry/view/header_custom.php" ]; then
	    # Workaround for OLDER hifiberry Images - get current Pluginfiles to overwrite CSS	    
	    /opt/max2play/install_plugin.sh https://shop.max2play.com/media/downloadable/$VERSION/hifiberry.tar
	    
	    # Update Plugin Header		
	    echo "Copy custom header files hifiberry"	    
	    cp -f /var/www/max2play/application/plugins/hifiberry/view/header_custom.php /var/www/max2play/application/view/
	    cp -f /var/www/max2play/application/plugins/hifiberry/scripts/custom.css /var/www/max2play/public/
	fi
	if [ -e "/var/www/max2play/application/plugins/iqaudio/view/header_custom.php" ]; then
	    # Workaround for OLDER hifiberry Images - get current Pluginfiles to overwrite CSS	    
	    /opt/max2play/install_plugin.sh https://shop.max2play.com/media/downloadable/$VERSION/iqaudio.tar
	    
	    # Update Plugin Header		
	    echo "Copy custom header files iqaudio"
	    cp -f /var/www/max2play/application/plugins/iqaudio/view/header_custom.php /var/www/max2play/application/view/
	    cp -f /var/www/max2play/application/plugins/iqaudio/scripts/custom.css /var/www/max2play/public/
	fi
	if [ -e "/var/www/max2play/application/plugins/audiophonics/view/header_custom.php" ]; then
	    # Update Plugin Header		
	    echo "Copy custom header files audiophonics"
	    cp -f /var/www/max2play/application/plugins/audiophonics/view/header_custom.php /var/www/max2play/application/view/
	    cp -f /var/www/max2play/application/plugins/audiophonics/scripts/custom.css /var/www/max2play/public/
	fi	
	if [ -e "/var/www/max2play/application/plugins/justboom/view/header_custom.php" ]; then
	    # Update Plugin Header		
	    echo "Copy custom header files justboom"
	    cp -f /var/www/max2play/application/plugins/justboom/view/header_custom.php /var/www/max2play/application/view/
	    cp -f /var/www/max2play/application/plugins/justboom/scripts/custom.css /var/www/max2play/public/
	fi	
	
	if [ "$(grep -i "start_audioplayer" /etc/rc.local | wc -l)" -lt "1" ]; then
		# Add Start Audioplayer to boot (not wait for crontab)
		sudo sed -i "s/^exit 0/#Max2Play Start Audioplayer\nsudo -u pi -H -s \/opt\/max2play\/start_audioplayer.sh > \/dev\/null 2>\&1 \&\n\nexit 0/" /etc/rc.local
	fi
	
	# USBMOUNT Charset Umlaute Fix Fat32
	if [ "$(grep -i "iocharset=iso8859-1" /etc/usbmount/usbmount.conf | wc -l)" -lt "1" ]; then
		sudo sed -i "s/fstype=vfat,gid=users,uid=pi/fstype=vfat,gid=users,uid=pi,iocharset=iso8859-1/" /etc/usbmount/usbmount.conf
	fi
	
	# Fix wrong YMPD Parameter webport
	sed -i 's/YMPD_PARAMETER=8081/YMPD_PARAMETER=--webport 8081/' /opt/max2play/audioplayer.conf
	
	# Jessie Fix USBMOUNT NTFS ONLY ON JESSIE!
	ISJESSIE=$(lsb_release -r | grep '8.0' | wc -l)	
	if [ "$ISJESSIE" -gt "0" -a -e /etc/systemd/system ]; then
		if [ ! -e /etc/systemd/system/usbmount@.service ]; then
			echo "Fix USB-Mount on Debian Jessie"
			echo "[Unit]\nBindTo=%i.device\nAfter=%i.device\n\n[Service]\nType=oneshot\nTimeoutStartSec=0\nEnvironment=DEVNAME=%I\nExecStart=/usr/share/usbmount/usbmount add\nRemainAfterExit=yes" > /etc/systemd/system/usbmount@.service
			echo "# Rules for USBmount -*- conf -*-\nKERNEL==\"sd*\", DRIVERS==\"sbp2\",         ACTION==\"add\",  PROGRAM=\"/bin/systemd-escape -p --template=usbmount@.service \$env{DEVNAME}\", ENV{SYSTEMD_WANTS}+=\"%c\"\nKERNEL==\"sd*\", SUBSYSTEMS==\"usb\",       ACTION==\"add\",  PROGRAM=\"/bin/systemd-escape -p --template=usbmount@.service \$env{DEVNAME}\", ENV{SYSTEMD_WANTS}+=\"%c\"\nKERNEL==\"ub*\", SUBSYSTEMS==\"usb\",       ACTION==\"add\",  PROGRAM=\"/bin/systemd-escape -p --template=usbmount@.service \$env{DEVNAME}\", ENV{SYSTEMD_WANTS}+=\"%c\"\nKERNEL==\"sd*\",                          ACTION==\"remove\",       RUN+=\"/usr/share/usbmount/usbmount remove\"\nKERNEL==\"ub*\",                          ACTION==\"remove\",       RUN+=\"/usr/share/usbmount/usbmount remove\"" > /etc/udev/rules.d/usbmount.rules
			rm /lib/udev/rules.d/usbmount.rules
		fi		
		# ifplugd fix for missing eth0
		sudo sed -i 's/^INTERFACES=""/INTERFACES="eth0"/' /etc/default/ifplugd
		
		# Update Kodi Settings for Webserver on Port 80 to remote Control
		if [ -e /home/pi/.kodi/userdata/guisettings.xml ]; then
		   echo "Update Kodi: Activate Webserver for Remote Control"
		   sed -i 's@<webserver default="true">false</webserver>@<webserver>true</webserver>@' /home/pi/.kodi/userdata/guisettings.xml
		fi		
	fi
	# Fix for NOT JESSIE and deleted usbmount rules
	if [ "$ISJESSIE" -lt "1" -a ! -e /lib/udev/rules.d/usbmount.rules ]; then
		echo "Remove Fix for USB-Mount on NON-Jessie"
		echo "KERNEL==\"sd*\", DRIVERS==\"sbp2\",		ACTION==\"add\",	RUN+=\"/usr/share/usbmount/usbmount add\"\nKERNEL==\"sd*\", SUBSYSTEMS==\"usb\",	ACTION==\"add\",	RUN+=\"/usr/share/usbmount/usbmount add\"\nKERNEL==\"ub*\", SUBSYSTEMS==\"usb\",	ACTION==\"add\",	RUN+=\"/usr/share/usbmount/usbmount add\"\nKERNEL==\"sd*\",				ACTION==\"remove\",	RUN+=\"/usr/share/usbmount/usbmount remove\"\nKERNEL==\"ub*\",				ACTION==\"remove\",	RUN+=\"/usr/share/usbmount/usbmount remove\"" > /lib/udev/rules.d/usbmount.rules
		rm /etc/systemd/system/usbmount@.service
		rm /etc/udev/rules.d/usbmount.rules		
	fi
	
	EXFATINSTALLED=$(dpkg -s exfat-fuse | grep "Status: install ok" | wc -l)	
	if [ "$EXFATINSTALLED" -lt "1" ]; then
		apt-get update
		echo "Y" | apt-get install exfat-fuse exfat-utils --yes
		# fix USB-Mount filesystem Options
		EXFATUSBMOUNT=$(grep -i "exfat" /etc/usbmount/usbmount.conf | wc -l)
		if [ "$EXFATUSBMOUNT" -lt "1" ]; then
			sed -i 's/hfsplus/hfsplus exfat/' /etc/usbmount/usbmount.conf
		fi
	fi
	
	#Add Further Languages here
	if [ -e /etc/locale.gen ]; then
		if [ "5" -gt "$(grep -e "^de_DE\|^fr_FR\|^it_IT\|^en_GB\|^ru_RU" /etc/locale.gen | wc -l)" ]; then
			sudo sed -i 's/# de_DE.UTF-8 UTF-8/de_DE.UTF-8 UTF-8/;s/# it_IT.UTF-8 UTF-8/it_IT.UTF-8 UTF-8/;s/# fr_FR.UTF-8 UTF-8/fr_FR.UTF-8 UTF-8/;s/# ru_RU.UTF-8 UTF-8/ru_RU.UTF-8 UTF-8/;s/# en_GB.UTF-8 UTF-8/en_GB.UTF-8 UTF-8/' /etc/locale.gen
  			sudo locale-gen
  			echo "Updated Locales"
  		fi
  	fi
  	
  	if [ "$(grep -i '^default-fragments = 5' /etc/pulse/daemon.conf | wc -l)" -lt "1" ]; then
       echo "default-fragments = 5\ndefault-fragment-size-msec = 2\n" >> /etc/pulse/daemon.conf
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

# Delete News Sidebar -> force Reload News
if [ -e /tmp/0.html ]; then
	rm /tmp/0.html
fi

# Custom Allo Sparky usbmount for user "vana"
USERNAME=$(grep -aP "^[ \t]*SYSTEM_USER" /opt/max2play/audioplayer.conf | sed -n -e "s/^[ \t]*[A-Za-z_0-9\.]*=//p")
if [ "$USERNAME" = "vana" ]; then
	sudo sed -i "s/odroid/vana/" /etc/usbmount/usbmount.conf
fi

if [ ! -e /opt/max2play/custom_autostart.sh ]; then
	echo '#!/bin/bash\n#Custom Autostart File\n' > /opt/max2play/custom_autostart.sh
	chmod 777 /opt/max2play/custom_autostart.sh
fi