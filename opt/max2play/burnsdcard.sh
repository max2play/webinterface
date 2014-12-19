#!/bin/bash
# Script can be run from Powerbutton
# insert your own script in max2play-webinterface
# e.g. /opt/max2play/burnsdcard.sh /home/odroid/Callblocker_Image_12112014.img 2>&1 > /opt/max2play/cache/burnsdcard.txt

# first argument is image file
echo "START burning $1" 

#start flashing LED
echo timer > /sys/class/leds/led1/trigger;
echo 80 > /sys/class/leds/led1/delay_on;
echo 160 > /sys/class/leds/led1/delay_off;

dd bs=4M if=$1 of=/dev/sda 2>>/opt/max2play/cache/burnsdcard.txt 

#stop flashing LED
echo heartbeat > /sys/class/leds/led1/trigger;
rm /sys/class/leds/led1/delay_on;
rm /sys/class/leds/led1/delay_off;

echo "FINISHED"
