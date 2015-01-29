#!/bin/bash

#Create Pluginfiles from your Workspace with "tar -cf example.tar -C /var/www/max2play/application/plugins/ example"
echo "Get Plugin from $1" 

if [ -e /opt/max2play/cache/newplugin ]; then
	rm -R /opt/max2play/cache/newplugin
fi
mkdir /opt/max2play/cache/newplugin

ISZIP=$(echo $1 | grep -i ".zip" | wc -l)
ISTARGZ=$(echo $1 | grep -i ".tar.gz" | wc -l)
ISTAR=$(echo $1 | grep -i ".tar" | wc -l)

if [ "$ISZIP" -gt "0" ]; then
	echo "Load Plugin from zip" 
	wget -O /opt/max2play/cache/plugin.zip $1
	unzip -o /opt/max2play/cache/plugin.zip -d /opt/max2play/cache/newplugin
elif [ "$ISTARGZ" -gt "0" ]; then
    echo "Load Plugin from tar.gz"
    wget -O /opt/max2play/cache/plugin.tar.gz $1
    tar -xfz /opt/max2play/cache/plugin.tar.gz -C /opt/max2play/cache/newplugin
elif [ "$ISTAR" -gt "0" ]; then
    echo "Load Plugin from tar"
    wget -O /opt/max2play/cache/plugin.tar $1
    tar -xf /opt/max2play/cache/plugin.tar -C /opt/max2play/cache/newplugin   
else
	echo "Wrong file type for Plugin! Must be one of these: .zip/.tar.gz/.tar/.rar"
	exit 0
fi

#Check for correct structure
PLUGINNAME=$(ls /opt/max2play/cache/newplugin/ | head -1)
echo "Installing Plugin $PLUGINNAME" 
if [ ! -e "/opt/max2play/cache/newplugin/$PLUGINNAME/controller" ] || [ ! -e "/opt/max2play/cache/newplugin/$PLUGINNAME/view" ]; then
	echo "Controller OR View Files are missing in the Plugin - Install canceled"
	exit 0
fi

#Install
cp -R /opt/max2play/cache/newplugin/* /var/www/max2play/application/plugins/

echo "Install successful - you need to enable the plugin to use it";

