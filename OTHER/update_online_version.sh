#!/bin/bash
# Update Version Script
# Zip Files for new Version - Update Version and send Files to Server
 
LOGFILE='rsync.log'
SOURCEPATH='/home/webuser/projects/Max2Play-Git'
DESTPATH='/var/www/shop.max2play.com/magento/media/downloadable/currentversion'
SOURCEPATHOPT='/home/webuser/projects/Max2Play-Git/opt/max2play/'

HOSTS=( "176.9.62.131")

for DESTHOST in "${HOSTS[@]}"
do
	echo $'\n'
	echo $DESTHOST
	pushd $SOURCEPATH
	zip -r webinterface.zip ./max2play
	zip -r scripts.zip ./opt/max2play -x  /opt/max2play/playername.txt /opt/max2play/samba.conf /opt/max2play/wpa_supplicant.conf	
	scp $SOURCEPATH/webinterface.zip root@$DESTHOST:$DESTPATH
	scp $SOURCEPATH/scripts.zip root@$DESTHOST:$DESTPATH
	scp $SOURCEPATH/max2play/application/config/version.txt root@$DESTHOST:$DESTPATH
	rm webinterface.zip
	rm scripts.zip
	echo "Completed" 
done
