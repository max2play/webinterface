#!/bin/bash

# Max2Play WPS (Wifi Protected Setup) Auto Connection on Startup
# Just make sure to activate WPS on Router before booting up your device
# Automatically sets up and saves WiFi Connection

# only run if there's no current wifi connection and WiFi is enabled
if [ "$(LANG=C && /sbin/ifconfig wlan0 | grep 'HWaddr' | wc -l)" -gt "0" -a "$(LANG=C && /sbin/ifconfig wlan0 | grep 'inet addr:' | wc -l)" -lt "1" ]; then
    killall -q wpa_supplicant
    # Check if "update_config=1" needed in /opt/max2play/wpa_supplicant.conf for Autoconfig
    if [ "$(grep -i "update_config=1" /opt/max2play/wpa_supplicant.conf | wc -l)" -lt "1" ]; then
    	echo "update_config=1" >> /opt/max2play/wpa_supplicant.conf
    fi
    
    # Make sure WPA-Supplicant is running with config
    wpa_supplicant -B w -D wext -i wlan0 -c /opt/max2play/wpa_supplicant.conf
    sleep 1        
    
    # get Routers supporting WPS, sorted by signal strength        
    SSID=$(/sbin/wpa_cli scan_results | grep "WPS" | sort -r -k3 | awk 'END{print $NF}')
    echo "Using $SSID for WPS"
    SUCCESS=$(wpa_cli wps_pbc $SSID)
    sleep 10
    
    #Check for Entry in wpa_supplicant.conf
    VALIDENTRY=$(grep -i "^network=" /opt/max2play/wpa_supplicant.conf | wc -l)
    if [ "$(echo "$SUCCESS" | grep "OK" | wc -l)" -gt "0" -a "$VALIDENTRY" -gt "0" ]; then
    	# Now Config File should be written    	
    	
    	# Stop existing WPA_Supplicant Process with Old Config
    	killall -q wpa_supplicant
    	
    	# Enable wlan0 in /etc/network/interfaces
    	if [ ! $(grep -i "^auto wlan0" /etc/network/interfaces) ]; then
    		sed -i "s/#allow-hotplug wlan0/allow-hotplug wlan0/;s/#iface wlan0 inet dhcp/iface wlan0 inet dhcp/;s/#auto wlan0/auto wlan0/;s/#pre-up wpa_supplicant/pre-up wpa_supplicant/;s/#post-down killall -q wpa_supplicant/post-down killall -q wpa_supplicant/" /etc/network/interfaces  	
		fi
		    	
    	# Startup wlan0 with new Config
    	/sbin/ifup wlan0
    else
    	echo "ERROR creating connection"
    	echo $SUCCESS
    fi
fi