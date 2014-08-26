#!/bin/bash
# Sync-Script for development and testing
# Sync webinterface and /opt to device with name max2play
 
LOGFILE='rsync.log'
DESTUSER='root'
SOURCEPATH='/home/webuser/projects/max2play/max2play/'
DESTPATH='/var/www/max2play'
SOURCEPATHOPT='/home/webuser/projects/max2play/opt/max2play/'
DESTPATHOPT='/opt/max2play'

HOSTS=( "anrufblocker")

for DESTHOST in "${HOSTS[@]}"
do
	echo $'\n'
	echo $DESTHOST
	rsync -av -rsh=ssh --exclude="*svn*" --exclude="cache" $SOURCEPATH $DESTUSER@$DESTHOST:$DESTPATH
	rsync -av -rsh=ssh --exclude="*svn*" --exclude="cache" --exclude="wpa_supplicant.conf" $SOURCEPATHOPT $DESTUSER@$DESTHOST:$DESTPATHOPT	
	# Delete /etc/smsc95xxx_mac_addr !
	echo "Completed" 
done
