#!/bin/sh
wget --no-check-certificate -O /opt/max2play/cache/fhem.deb https://debian.fhem.de/fhem-5.6.deb
dpkg -i /opt/max2play/cache/fhem.deb
apt-get update
echo "Y" | apt-get install -f
echo "Finished"
