#!/bin/bash
#Howto
#http://xmodulo.com/raspberry-pi-wifi-access-point.html

# $1 Parameter is scriptpath
# probably /var/max2play/application/plugins/accesspoint/scripts/

# Check for WiFi Adapter!
WIFI_AVAILABLE=$(sudo ifconfig | grep wlan0 | wc -l)
if [ "$WIFI_AVAILABLE" -lt "1" ]; then
	echo "No WiFi detected - make sure a WiFi stick is attached to your device!"
	return 1
fi
	
# Install everything
echo "Y" | apt-get install hostapd dnsmasq

# TODO: Check for Realtek Chipset (Edimax) and switch hostapd binary
# sudo cp /usr/sbin/hostapd /usr/sbin/hostapd-old;sudo cp -f /opt/max2play/hostapd-rtl /usr/sbin/hostapd

# Copy config file for DHCP Server
cp -f $1dnsmasq.conf /etc/dnsmasq.conf

# Insert hostname to /etc/hosts
HOSTNAME=$(cat /etc/hostname)
echo "192.168.189.1     $HOSTNAME" >> /etc/hosts
sed -i "s/HOSTNAME/$HOSTNAME" /etc/dnsmasq.conf

# Enable in Config
sed -i 's/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/' /etc/sysctl.conf

# Edit Interfaces
echo "#Accesspoint start
iface wlan0 inet static
#gateway 192.168.189.1
address 192.168.189.1
netmask 255.255.255.0
up iptables-restore < /etc/hostapd/iptables.ap
#Accesspoint end" >> /etc/network/interfaces

cp -f $1hostapd.conf /etc/hostapd/hostapd.conf

sed -i 's/DAEMON_CONF.*/DAEMON_CONF="\/etc\/hostapd\/hostapd.conf"/' /etc/default/hostapd

update-rc.d hostapd enable

sudo ifup wlan0

iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
iptables -A FORWARD -i eth0 -o wlan0 -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i wlan0 -o eth0 -j ACCEPT

sudo sh -c "iptables-save > /etc/hostapd/iptables.ap"
