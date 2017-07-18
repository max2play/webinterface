#!/bin/bash

#$1 is parameter for ssid
#$2 is parameter for passphrase

# Make sure previous script is loaded to prevent connection abort
sleep 5
eth_connected=$(LANG=C && /sbin/ifconfig eth0 | grep -o 'inet addr:[0-9.]\+' | grep -o '[0-9.]\+')
# TODO: Add Variable for Auto-Accesspoint if no eth0 is available
hostapd_running=$(ps -Al | grep hostapd | wc -l)
if [ "$hostapd_running" -gt "0" ]; then
	/etc/init.d/hostapd stop
	# Disable Accesspoint
	/var/www/max2play/application/plugins/accesspoint/scripts/uninstall_accesspoint.sh 1
	sleep 3
fi

ifdown wlan0
killall -q wpa_supplicant && sleep 3

wpa_supplicant -B w -D wext -i wlan0 -c /opt/max2play/wpa_supplicant.conf;
wpa_cli -iwlan0 add_network
wpa_cli -iwlan0 set_network 0 key_mgmt WPA-PSK
wpa_cli -iwlan0 set_network 0 mode 0
eval "wpa_cli -iwlan0 set_network 0 psk '\"$2\"'"
eval "wpa_cli -iwlan0 set_network 0 ssid '\"$1\"'"
wpa_cli -iwlan0 enable_network 0
wpa_cli -iwlan0 save_config
killall -q wpa_supplicant && sleep 3

# Enable Interface Wlan0
sed -i 's/#pre-up wpa_supplicant/pre-up wpa_supplicant/;s/#allow-hotplug wlan0/allow-hotplug wlan0/;s/#auto wlan0/auto wlan0/;s/#iface wlan0 inet dhcp/iface wlan0 inet dhcp/;s/#post-down killall/post-down killall/' /etc/network/interfaces

# Launch Wlan0
ifup wlan0
sleep 3

# Check Wlan connection
checkwifi=$(LANG=C && /sbin/ifconfig wlan0 | grep -o 'inet addr:[0-9.]\+' | grep -o '[0-9.]\+')
if [ "$checkwifi" == "" ]; then
	echo "No IP-Address could be received - WiFi NOT working correctly. Please check Network-ID and Passphrase!"
	sed -i 's/^pre-up wpa_supplicant/#pre-up wpa_supplicant/;s/^allow-hotplug wlan0/#allow-hotplug wlan0/;s/^auto wlan0/#auto wlan0/;s/^iface wlan0 inet dhcp/#iface wlan0 inet dhcp/;s/^post-down killall/#post-down killall/' /etc/network/interfaces
	ifdown wlan0
	killall wpa_supplicant && sleep 3
	wpa_supplicant -B w -D wext -i wlan0 -c /opt/max2play/wpa_supplicant.conf && sleep 1
	# Remove Network that does not work
	wpa_cli -iwlan0 remove_network 0
	wpa_cli -iwlan0 save_config
	killall wpa_supplicant && sleep 3
	wpa_supplicant -B w -D wext -i wlan0 -c /opt/max2play/wpa_supplicant.conf && sleep 1
	if [ "$hostapd_running" -gt "0" -o "$eth_connected" == "" ]; then
		/var/www/max2play/application/plugins/accesspoint/scripts/install_accesspoint.sh /var/www/max2play/application/plugins/accesspoint/scripts/ 1
	fi
else
    # enable Autoreconnect WiFi
    echo "Enable Autoreconnect WiFi..."    
    autoreconnect_wifi=$(cat /opt/max2play/options.conf | grep autoreconnect_wifi | wc -l)    
	if [ "1" -gt "$autoreconnect_wifi" ]; then
	    echo "autoreconnect_wifi=1" >> /opt/max2play/options.conf
	else
	    sed -i 's/autoreconnect_wifi.*/autoreconnect_wifi=1/' /opt/max2play/options.conf
	fi
fi


echo "Finished <b><a href='/plugins/max2play_settings/controller/Wlan.php'>Please Click here Reload Page!</a></b>"

exit 0