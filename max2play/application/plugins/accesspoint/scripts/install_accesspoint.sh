#!/bin/bash
#Howto
#http://xmodulo.com/raspberry-pi-wifi-access-point.html

# $1 Parameter is scriptpath
# probably /var/max2play/application/plugins/accesspoint/scripts/
# Parameter $2 for Accesspoint Setup First Start (fast switch between AP and WiFi Mode)

# Check for WiFi Adapter!
WIFI_AVAILABLE=$(sudo /sbin/ifconfig | grep wlan0 | wc -l)
if [ "$WIFI_AVAILABLE" -lt "1" ]; then
	wpa_supplicant -B w -D wext -i wlan0 -c /opt/max2play/wpa_supplicant.conf
	WIFI_AVAILABLE=$(sudo /sbin/ifconfig | grep wlan0 | wc -l)
	if [ "$WIFI_AVAILABLE" -lt "1" ]; then
		echo "No WiFi detected - make sure a WiFi stick is attached to your device!"	
		exit 1
	fi
else
	echo "WiFi detected"
fi
	
# Install everything and Check if hostapd and dnsmasq are existing
if [ ! "$2" == "1" ]; then
	apt-get update
	echo "Y" | apt-get install hostapd dnsmasq


	# Check for Realtek Chipset (Edimax) and switch hostapd binary
	# ONLY for Debian Wheezy...
	IS_EDIMAX=$(sudo lsusb | grep "Wireless\|Edimax" | grep "RTL8188CUS\|Edimax" | wc -l)
	RELEASE=$(lsb_release -a 2>/dev/null | grep Codename | sed "s/Codename:\t//")	
	if [ "$IS_EDIMAX" -gt "0" -a "$(lsb_release -r | grep '8.0' | wc -l)" -lt "1" -a "$RELEASE" != "stretch" ]; then 
	    echo "Change hostapd-Binary to Edimax RTL8188CUS Chipset for Debian Wheezy"
	    sudo cp /usr/sbin/hostapd /usr/sbin/hostapd-old;sudo cp -f /opt/max2play/hostapd-rtl /usr/sbin/hostapd
	elif [ -e /usr/sbin/hostapd-old ]; then
		sudo cp /usr/sbin/hostapd-old /usr/sbin/hostapd
	fi

	# Copy config file for DHCP Server
	cp -f $1dnsmasq.conf /etc/dnsmasq.conf
	update-rc.d hostapd defaults
	update-rc.d dnsmasq defaults
fi
	
if [ "$2" == "1" ]; then
	if [ "$(dpkg -s hostapd | grep "install ok" | wc -l)" -lt "1" -o "$(dpkg -s dnsmasq | grep "install ok" | wc -l)" -lt "1" ]; then
		# Check for Internet connection...
		if [ "$(LANG=C && /sbin/ip addr show eth0 | grep 'inet ' | wc -l)" -lt "1" -a "$(LANG=C && /sbin/ip addr show wlan0 | grep 'ether' | wc -l)" -gt "0" -a "$(LANG=C && /sbin/ip addr show wlan0 | grep 'inet ' | grep -v '169.254' | wc -l)" -lt "1" ]; then
			echo "Big Problem: no Internet connection to install hostapd and dnsmasq - you should do this earlier..."
			exit 1
		else
			# Fallback to install required packages
			apt-get update
			echo "Y" | apt-get install hostapd dnsmasq
			cp -f $1dnsmasq.conf /etc/dnsmasq.conf
			## Script start_accesspoint_onboot.sh will do the startup if not network connection available
			update-rc.d hostapd remove
			update-rc.d -f dnsmasq disable
		fi
	fi 
	
	if [ "$3" == "onlyinstall" ]; then
		exit 0
	else
		#Save Variable to know device is in Automatic Accesspoint Mode - Check in Service Class to allow/disallow functions
		echo "1" > /tmp/automatic_accesspoint_mode
	fi
fi

# Insert hostname to /etc/hosts
HOSTNAME=$(cat /etc/hostname)
echo "192.168.189.1     $HOSTNAME" >> /etc/hosts
sed -i "s/HOSTNAME/$HOSTNAME/" /etc/dnsmasq.conf

# Enable in Config
sed -i 's/#net.ipv4.ip_forward=1/net.ipv4.ip_forward=1/' /etc/sysctl.conf

# Separate Jessie and Wheezy!
RELEASE=$(lsb_release -a 2>/dev/null | grep Codename | sed "s/Codename:\t//")
if [ "$RELEASE" == "jessie" -o "$RELEASE" == "stretch" ]; then 
echo "#Accesspoint start
up iptables-restore < /etc/hostapd/iptables.ap
#Accesspoint end" >> /etc/network/interfaces
echo "#Accesspoint start
interface wlan0
static ip_address=192.168.189.1/24
#Accesspoint end" >> /etc/dhcpcd.conf
else
# Edit Interfaces on Debian Wheezy
echo "#Accesspoint start
iface wlan0 inet static
address 192.168.189.1
#network 192.168.189.0
netmask 255.255.255.0
#gateway 192.168.189.1
up iptables-restore < /etc/hostapd/iptables.ap
#Accesspoint end" >> /etc/network/interfaces
fi

cp -f $1hostapd.conf /etc/hostapd/hostapd.conf

sed -i 's/.*DAEMON_CONF.*/DAEMON_CONF="\/etc\/hostapd\/hostapd.conf"/' /etc/default/hostapd

# Set up hostapd init-script to always use fixed WiFi IP - otherwise WLAN0 sometimes wont have a IP
cp -f $1hostapd /etc/init.d/

iptables -t nat -A POSTROUTING -o eth0 -j MASQUERADE
iptables -A FORWARD -i eth0 -o wlan0 -m state --state RELATED,ESTABLISHED -j ACCEPT
iptables -A FORWARD -i wlan0 -o eth0 -j ACCEPT
#iptables -A INPUT -m pkttype --pkt-type multicast -j ACCEPT

sudo sh -c "iptables-save > /etc/hostapd/iptables.ap"

sudo /etc/init.d/hostapd restart
sudo /etc/init.d/dnsmasq restart

exit 0