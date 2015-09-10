#!/bin/bash

# TODO: add to Basic funtions 

# VARIABLEN
BACKUP_PFAD="/pfad/zum_backup_order"
BACKUP_ANZAHL="5"
BACKUP_NAME="Max2Play_Backup"
DIENSTE_START_STOP="service mysql"
# ENDE VARIABLEN
 
# Stoppe Dienste vor Backup
${DIENSTE_START_STOP} stop
 
# create backup, zip it and save to path
dd if=/dev/mmcblk0 of=${BACKUP_PFAD}/${BACKUP_NAME}-$(date +%Y%m%d-%H%M%S).img bs=1MB
 
# restart Services
${DIENSTE_START_STOP} start
 
# Remove old backups
pushd ${BACKUP_PFAD}; ls -tr ${BACKUP_PFAD}/${BACKUP_NAME}* | head -n -${BACKUP_ANZAHL} | xargs rm; popd


echo "Finished"