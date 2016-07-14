#!/bin/bash
if [ ! -e /usr/lib/arm-linux-gnueabihf/libkodiplatform.so.15.0 ]; then
	sudo apt-get update
	sudo apt-get install libkodiplatform -y
	ln /usr/lib/arm-linux-gnueabihf/libkodiplatform.so.16.0 /usr/lib/arm-linux-gnueabihf/libkodiplatform.so.15.0
	ldconfig
	echo "Install Finished"
else
	echo "Already installed"
fi
