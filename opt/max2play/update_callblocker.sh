#!/bin/sh

### Directory exists?
if [ -e /opt/callblocker ]; then echo "Callblocker-Directory exists"
else
	echo "Create Callblocker-Directory"
	mkdir /opt/callblocker
	mkdir /opt/callblocker/cache
	chmod 777 /opt/callblocker/cache
fi	

### Update Scripts and Webinterface ###
echo "Get Data"
wget http://cdn.tellows.de/uploads/downloads/callblocker/currentversion/webinterface_max2play_plugin.zip -O /opt/callblocker/cache/webinterface_max2play_plugin.zip
wget http://cdn.tellows.de/uploads/downloads/callblocker/currentversion/scripts.zip -O /opt/callblocker/cache/scripts.zip

echo "Install Webinterface"
unzip -o /opt/callblocker/cache/webinterface_max2play_plugin.zip -d /var/www/max2play/application/plugins/

echo "Install Scripts"
unzip -o /opt/callblocker/cache/scripts.zip -d /opt

### Test for Running NCID and run installer if its not running
runningNCID=$(ps -Al | grep ncid | wc -l)

if [ "$runningNCID" -lt "1" ]; then
	echo "Install needed - no running NCID found"
	/opt/callblocker/install.sh
else
	echo "Running NCID detected - no new Install required"
fi

echo "Finished Update"