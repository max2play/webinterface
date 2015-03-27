#!/bin/bash
#Installer Script for CCU-Historian
# takes First Agrument for Download URL and second for Scriptpath
pushd /opt
mkdir ccu-historian
cd ccu-historian
wget -O /opt/ccu-historian/install.zip "$1" 
unzip install.zip

cp ccu-historian-sample.config ccu-historian.config
cp $2ccu-historian.sh /etc/init.d/ccu-historian.sh

#Enable some Configs
sed -i 's/\/\/ webServer\.port.*/webServer.port=8060/' /opt/ccu-historian/ccu-historian.config
sed -i "s/\/\/ webServer\.dir.*/webServer.dir='\/opt\/ccu-historian\/webapp'/" /opt/ccu-historian/ccu-historian.config
sed -i 's/\/\/ database\.webAllowOthers.*/database.webAllowOthers=true/' /opt/ccu-historian/ccu-historian.config
sed -i 's/^devices\.device1\.type.*/devices.device1.type=CCU1/' /opt/ccu-historian/ccu-historian.config
sed -i "s/^devices\.device1\.address.*/devices.device1.address='homematic.fritz.box'/" /opt/ccu-historian/ccu-historian.config
echo "devices.device1.plugin1.type=CUXD" >> /opt/ccu-historian/ccu-historian.config

#sudo java -jar /opt/ccu-historian/ccu-historian.jar

echo "Finished"