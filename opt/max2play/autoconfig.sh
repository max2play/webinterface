#!/bin/bash

# Auto Configure Settings (edit settings by file on first launch)
if [ -e /boot/max2play.conf ]; then
	echo "Boot Options found"	
	
	# Set Email Options
	EMAIL=$(grep -a "email" /boot/max2play.conf | sed -n -e 's/^[A-Z_a-z]*\=//p')
	if [ ! "$EMAIL" == "" ]; then
		if [ ! "$(grep -i '^email=' /opt/max2play/options.conf)" == "" ]; then
			sed -i "s/^email=.*/email=$EMAIL/" /opt/max2play/options.conf
		else
			echo "email=$EMAIL" >> /opt/max2play/options.conf
		fi
		if [ ! "$(grep -i '^license=' /opt/max2play/options.conf)" == "" ]; then
			sed -i "s/^license=.*/license=1/" /opt/max2play/options.conf
		else
			echo "license=1" >> /opt/max2play/options.conf
		fi
		echo "Email and License Set"
	fi
	
	# Set Options Audiocard
	DTOVERLAY=$(grep -a "dtoverlay" /boot/max2play.conf | sed -n -e 's/^[A-Z_a-z]*\=//p')
	if [ ! "$DTOVERLAY" == "" ]; then
		AUDIOCARD=$(grep -a "audiocard" /boot/max2play.conf | sed -n -e 's/^[A-Z_a-z]*\=//p')			
		if [ ! "$(grep -i '^dtoverlay=' /boot/config.txt)" == "" ]; then
			sed -i "s/^dtoverlay=.*/dtoverlay=$DTOVERLAY/" /boot/config.txt
		else
			echo "dtoverlay=$DTOVERLAY" >> /boot/config.txt
		fi
		
		if [ ! "$(grep -i '^audiocard=' /opt/max2play/options.conf)" == "" ]; then
			sed -i "s/^audiocard=.*/audiocard=$AUDIOCARD/" /opt/max2play/options.conf
		else
			echo "audiocard=$AUDIOCARD" >> /opt/max2play/options.conf
		fi
		
		# Squeezelite & Shairport
		SQUEEZELITE_PARAMETER=$(grep -a "SQUEEZELITE_PARAMETER" /boot/max2play.conf | sed -n -e 's/^[A-Z_a-z]*\=//p')
		if [ ! "$(grep -i '^SQUEEZELITE_PARAMETER=' /opt/max2play/audioplayer.conf)" == "" ]; then
			sed -i "s/^SQUEEZELITE_PARAMETER=.*/SQUEEZELITE_PARAMETER=$SQUEEZELITE_PARAMETER/" /opt/max2play/audioplayer.conf
		else
			echo "SQUEEZELITE_PARAMETER=$SQUEEZELITE_PARAMETER" >> /opt/max2play/audioplayer.conf
		fi
		
		SHAIRPORT_PARAMETER=$(grep -a "SHAIRPORT_PARAMETER" /boot/max2play.conf | sed -n -e 's/^[A-Z_a-z]*\=//p')
		if [ ! "$(grep -i '^SHAIRPORT_PARAMETER=' /opt/max2play/audioplayer.conf)" == "" ]; then
			sed -i "s/^SHAIRPORT_PARAMETER=.*/SHAIRPORT_PARAMETER=$SHAIRPORT_PARAMETER/" /opt/max2play/audioplayer.conf
		else
			echo "SHAIRPORT_PARAMETER=$SHAIRPORT_PARAMETER" >> /opt/max2play/audioplayer.conf
		fi
		
		echo "Audiocard set to $AUDIOCARD"
	fi
	
	if [ ! "$(grep -i '^resize=1' /boot/max2play.conf)" == "" ]; then
		/opt/max2play/expandfs.sh mmcblk0p2
	fi
	
	# Delete Config File
	rm /boot/max2play.conf
	
	reboot
fi