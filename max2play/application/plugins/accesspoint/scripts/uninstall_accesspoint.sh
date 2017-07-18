#!/bin/bash

# Parameter $1 for Accesspoint Setup First Start (fast switch between AP and WiFi Mode)
# Check for WiFi Adapter!
/etc/init.d/hostapd stop
/etc/init.d/dnsmasq stop

if [ ! "$1" == "1" ]; then
	echo "Y" | apt-get remove hostapd dnsmasq
	echo "Y" | apt-get purge dnsmasq
else
	update-rc.d hostapd remove
	update-rc.d dnsmasq remove
fi

# Disable Forwarding in Config
sed -i 's/^net.ipv4.ip_forward=1/#net.ipv4.ip_forward=1/' /etc/sysctl.conf

# Remove Hostname from Hosts
sed -i 's/192\.168\.189\.1.*//' /etc/hosts

# Remove static WLAN from interface Config and DHCPD Conf
awk -v NEWTEXT="" 'BEGIN{n=0} /#Accesspoint start/ {n=1} {if (n==0) {print $0}} /#Accesspoint end/ {print NEWTEXT; n=0}' < /etc/network/interfaces > /etc/network/interfaces.new
cp /etc/network/interfaces.new /etc/network/interfaces
rm /etc/network/interfaces.new

awk -v NEWTEXT="" 'BEGIN{n=0} /#Accesspoint start/ {n=1} {if (n==0) {print $0}} /#Accesspoint end/ {print NEWTEXT; n=0}' < /etc/dhcpcd.conf > /etc/dhcpcd.conf.new
cp /etc/dhcpcd.conf.new /etc/dhcpcd.conf
rm /etc/dhcpcd.conf.new

if [ ! "$1" == "1" ]; then
	echo "finished"
fi
exit 0