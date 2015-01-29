#!/bin/bash
echo "Get Sources and Headers and Build latest odroid-3.8.y Kernel"
pushd /opt/
git clone --depth 1 https://github.com/hardkernel/linux.git -b odroid-3.8.y odroid-3.8.y
pushd /opt/odroid-3.8.y/
make odroidu_defconfig
make -j5 zImage modules
pushd /opt/
git clone git://github.com/panicking/snd-usb-asyncaudio.git
pushd /opt/snd-usb-asyncaudio
make -C /opt/odroid-3.8.y/ SUBDIRS=/opt/snd-usb-asyncaudio modules
cp -f snd-usb-hiface.ko /var/www/max2play/application/plugins/kernelmodules_odroid_u3/scripts/
echo "Finished"