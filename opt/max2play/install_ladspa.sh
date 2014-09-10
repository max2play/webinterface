#!/bin/sh
installcheck=$(grep -a headphonesplug /etc/asound.conf | wc -l)

if [ "$installcheck" -lt 1 ]; then
	echo "Installation started"

	echo "Y" | apt-get install bs2b-ladspa

	echo "
pcm.headphonesplug { 
 type plug
 slave.pcm \"dmix:CARD=0,RATE=44100\"
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
	alsa force-reload
	echo "Install Finished - Use advanced Squeezelite-Config with \"-o headphones\" to use headphone-output with Squeezelite"
else
	echo "Already installed - Nothing to do"
fi


