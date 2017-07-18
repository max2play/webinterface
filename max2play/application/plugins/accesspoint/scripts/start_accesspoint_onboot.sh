#!/bin/bash
# start_accesspoint_onboot.sh

# Check for existing connection
if [ "$(LANG=C && /sbin/ifconfig eth0 | grep 'inet addr:' | wc -l)" -lt "1" -a "$(LANG=C && /sbin/ifconfig wlan0 | grep 'HWaddr' | wc -l)" -gt "0" -a "$(LANG=C && /sbin/ifconfig wlan0 | grep 'inet addr:' | grep -v '169.254' | wc -l)" -lt "1" ]; then
	echo "No network connection..." 
else
	exit 1
fi

# Check for Parameter auto_accesspoint_mode in options
if [ "$(grep 'auto_accesspoint_mode=1' /opt/max2play/options.conf | wc -l)" -lt "1" ]; then
	echo "No Auto-Accesspoint-Mode activated"
	exit 1
fi

echo "Starting Accesspoint Mode..."
/var/www/max2play/application/plugins/accesspoint/scripts/install_accesspoint.sh /var/www/max2play/application/plugins/accesspoint/scripts/ 1

exit 0