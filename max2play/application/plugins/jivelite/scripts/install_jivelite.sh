#!/bin/bash

apt-get install git libsdl1.2-dev libsdl-ttf2.0-dev libsdl-image1.2-dev libsdl-gfx1.2-dev libexpat1-dev --yes --force-yes
pushd /opt
mkdir jivelite
pushd /opt/jivelite/
git clone https://code.google.com/p/jivelite/
git clone http://luajit.org/git/luajit-2.0.git
pushd /opt/jivelite/luajit-2.0/;make;make install;ldconfig
pushd /opt/jivelite/jivelite
make

echo "Add to autostart"
# Parse Autostart for existing Jivelite
existing=$(grep -a jivelite /opt/max2play/autostart.conf | wc -l)
if [ "$existing" -lt "1" ]; then
	echo "jivelite=0" >> /opt/max2play/autostart.conf
fi

echo "Configure Jivelite"
#sudo --user=odroid echo "settings = {skin="HDGridSkin-1080",}" > /home/odroid/.jivelite/userpath/settings/SelectSkin.lua
#sudo --user=odroid echo "settings = {setupDone=true,}" > /home/odroid/.jivelite/userpath/settings/SetupWelcome.lua
#sudo --user=odroid echo "settings = {_AUTOUP=false,_LASTVER="0.1.0",}" > /home/odroid/.jivelite/userpath/settings/SetupAppletInstaller.lua
#sudo --user=odroid echo "settings = {locale="DE",}" > /home/odroid/.jivelite/userpath/settings/SetupLanguage.lua
echo "finished";

