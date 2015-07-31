#!/bin/bash
# Script can be run from Powerbutton
# insert your own script in max2play-webinterface
# e.g. /opt/max2play/burnsdcard.sh /home/odroid/Callblocker_Image_06012015.img 2>&1 > /opt/max2play/cache/burnsdcard.txt

# first argument is image file
echo "START burning $1" 
date1=$(date +"%s")

if [ "$2" == "sda" -o "$2" == "sdb" ]; then
	DEVICE=$2
elif [ "$2" == "" ]; then
	DEVICE='sda'
else
	echo "No valid Device choosen - must be sda or sdb"
	echo "FINISHED"
	exit 0 
fi

echo "Burning to Device $DEVICE"

#start flashing LED
echo timer > /sys/class/leds/led1/trigger;
echo 80 > /sys/class/leds/led1/delay_on;
echo 160 > /sys/class/leds/led1/delay_off;

dd bs=4M if=$1 of=/dev/$DEVICE

#stop flashing LED
echo heartbeat > /sys/class/leds/led1/trigger;
rm /sys/class/leds/led1/delay_on;
rm /sys/class/leds/led1/delay_off;

date2=$(date +"%s")
diff=$(($date2-$date1))
echo "Burning took $(($diff / 60)) minutes and $(($diff % 60)) seconds"

echo "finished"
