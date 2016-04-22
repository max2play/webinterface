#!/bin/bash

# Check for WiFi Adapter!
echo "Y" | apt-get remove hostapd dnsmasq
echo "Y" | apt-get purge dnsmasq

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

echo "finished"
exit 0