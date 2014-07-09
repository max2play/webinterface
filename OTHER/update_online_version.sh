#!/bin/bash
# Update Version Script
# Zip Files for new Version - Update Version and send Files to Server
 
LOGFILE='rsync.log'
SOURCEPATH='/home/webuser/projects/max2play'
DESTPATH='/var/www/shop.max2play.com/magento/media/downloadable/currentversion'
SOURCEPATHOPT='/home/webuser/projects/max2play/opt/max2play/'

HOSTS=( "176.9.62.131")

for DESTHOST in "${HOSTS[@]}"
do
	echo $'\n'
	echo $DESTHOST
	pushd $SOURCEPATH
	zip -r webinterface.zip ./max2play -x \*custom\*
	zip -r scripts.zip ./opt/max2play -x  /opt/max2play/playername.txt /opt/max2play/samba.conf /opt/max2play/wpa_supplicant.conf
	zip -r max2play_complete.zip . -x  *svn* *custom*
	scp $SOURCEPATH/webinterface.zip root@$DESTHOST:$DESTPATH
	scp $SOURCEPATH/scripts.zip root@$DESTHOST:$DESTPATH
	scp $SOURCEPATH/max2play_complete.zip root@$DESTHOST:$DESTPATH
	scp $SOURCEPATH/max2play/application/config/version.txt root@$DESTHOST:$DESTPATH
	rm webinterface.zip
	rm scripts.zip
	rm max2play_complete.zip
	echo "Completed Max2Play" 
done

#################################### Callblocker #######################################
# Update Version Script
 
LOGFILE='rsync.log'
SOURCEPATH='/home/webuser/projects/max2play/max2play/application/plugins'
DESTPATH='/var/www/cdn.tellows/wordpress/uploads/downloads/callblocker/currentversion'
SOURCEPATHOPT='/home/webuser/projects/callblocker'

HOSTS=( "176.9.62.132" )

for DESTHOST in "${HOSTS[@]}"
do
	echo $'\n'
	echo $DESTHOST
	#### Pack and Copy Webinterface and Version ####
	pushd $SOURCEPATH
	zip -r webinterface_max2play_plugin.zip ./callblocker -x  *svn* *custom*
	scp $SOURCEPATH/webinterface_max2play_plugin.zip root@$DESTHOST:$DESTPATH	
	rm webinterface_max2play_plugin.zip
	
	#### Pack and Copy Scripts ####
	pushd $SOURCEPATHOPT
	zip -r scripts.zip ./callblocker -x /callblocker/tellows.conf /callblocker/linphone.conf /callblocker/button.c /callblocker/cache	
	scp $SOURCEPATHOPT/scripts.zip root@$DESTHOST:$DESTPATH
	scp $SOURCEPATHOPT/callblocker/version.txt root@$DESTHOST:$DESTPATH
	
	rm scripts.zip
	echo "Completed Callblocker" 
done