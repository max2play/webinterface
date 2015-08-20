#!/bin/sh
installcheck=$(grep -a headphonesplug /etc/asound.conf | wc -l)
echo "Hardware is $1"

if [ "$1" = "Raspberry PI" ]; then
    OUTPUT="hw:0,0"
else
	OUTPUT="dmix:CARD=0,RATE=44100"
fi    

if [ "$installcheck" -lt 1 ]; then
	echo "Installation started"
	apt-get update
	echo "Y" | apt-get install bs2b-ladspa

	echo "
pcm.headphonesplug { 
 type plug
 slave.pcm \"$OUTPUT\"
}

pcm.bs2b {
 type ladspa
 slave.pcm \"headphonesplug\"
 path \"/usr/lib/ladspa\"
 plugins [
  {
   label bs2b
   input {
    controls [ 500 4.5 ]
   }
  }
 ]
}

pcm.headphones {
 type plug
 slave {
  pcm \"bs2b\"
  rate 44100
 }
}
" >> /etc/asound.conf	
	HW_RASPBERRY=$(cat /proc/cpuinfo | grep Hardware | grep -i BCM2708 | wc -l)
	if [ "$HW_RASPBERRY" -gt "0" ]; then
  		echo "Hardware is Raspberry - Replace Options in asound.conf"
  		sed -i 's/slave.pcm "dmix:CARD=0,RATE=44100"/slave { pcm "hw:0,0";rate 44100;}/' /etc/asound.conf
	fi	
	alsa force-reload
	echo "Install Finished - Use advanced Squeezelite-Config with \"-o headphones\" to use headphone-output with Squeezelite"
else
	echo "Already installed - Nothing to do"
fi


