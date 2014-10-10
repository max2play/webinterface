#!/bin/bash

#Howto
#http://xmodulo.com/raspberry-pi-wifi-access-point.html

apt-get install hostapd udhcpd
touch /var/lib/misc/udhcpd.leases
update-rc.d udhcpd enable

sed -i 's/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/' /etc/sysctl.conf

#Package for realtec network chipset
ln -sf /opt/max2play/hostapd-rtl /usr/sbin/hostapd 
chown root.root /usr/sbin/hostapd
chmod 755 /usr/sbin/hostapd

#Add to network interfaces
allow-hotplug wlan0
auto wlan0
iface wlan0 inet static
address 192.168.0.1
netmask 255.255.255.0
up iptables-restore < /etc/hostapd/iptables.ap
