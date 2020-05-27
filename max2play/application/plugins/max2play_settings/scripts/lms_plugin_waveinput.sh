#!/bin/bash
echo "Install Waveinput Plugin"
wget http://cdn.max2play.com/WaveInputLinux-v104_7.6.ZIP -O /opt/max2play/cache/waveinput.zip

cp /opt/max2play/cache/waveinput.zip /var/lib/squeezeboxserver/cache/DownloadedPlugins/WaveInput.zip
chown squeezeboxserver /var/lib/squeezeboxserver/cache/DownloadedPlugins/WaveInput.zip	
echo "WaveInput: needs-install" >> /var/lib/squeezeboxserver/prefs/plugin/state.prefs
	
foundWaveInput=$(grep -i "WaveInput" /var/lib/squeezeboxserver/prefs/plugin/extensions.prefs | wc -l)
if [ "$foundWaveInput" -lt "1" ]; then
	sed -i 's/plugin:$/plugin:\n  WaveInput: 1/' /var/lib/squeezeboxserver/prefs/plugin/extensions.prefs
	sed -i 's/plugin: {}/plugin:\n  WaveInput: 1/' /var/lib/squeezeboxserver/prefs/plugin/extensions.prefs
fi
echo "WaveInput installed"	

/etc/init.d/logitechmediaserver restart

echo "Finished"