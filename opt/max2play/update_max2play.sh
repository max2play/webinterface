#!/bin/sh
#Update Max2Play Scripts and Webinterface
if [ "$1" = "beta" ];then
   VERSION=beta
else
   VERSION=currentversion
fi
   
echo "Get Data"
wget "https://shop.max2play.com/media/downloadable/$VERSION/webinterface.zip" -O /opt/max2play/cache/webinterface.zip
wget "https://shop.max2play.com/media/downloadable/$VERSION/scripts.zip" -O /opt/max2play/cache/scripts.zip

echo "Install Webinterface"
if [ -e /var/www/max2play/application/config/plugins.xml ]; then 
	unzip -o /opt/max2play/cache/webinterface.zip -d /var/www -x \*plugins.xml
else
	unzip -o /opt/max2play/cache/webinterface.zip -d /var/www
fi

echo "Install Scripts"
unzip -o /opt/max2play/cache/scripts.zip -d /

# Fix für usbmount Geschwindigkeit
sed -i 's/^MOUNTOPTIONS="sync,noexec,nodev,noatime,nodiratime"/MOUNTOPTIONS="noexec,nodev,noatime,nodiratime"/' /etc/usbmount/usbmount.conf

/opt/max2play/update_max2play_addscripts.sh 2>&1
