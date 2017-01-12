#!/bin/sh

# if no Parameter is given, this takes in consideration /dev/mmcblk0p2 as the rootfs! 

if [ -z "$1" ] ; then
	PARTITION="mmcblk0p2"
else
	PARTITION=$1
fi
echo $PARTITION

p2_start=`fdisk -l /dev/mmcblk0 | grep $PARTITION | awk '{print $2}'`
# Leave 100 MB for Writable Partition
# get Maximium Size
maximum=`fdisk -l /dev/mmcblk0 | grep -o -e "[0-9]\{7,\} sectors" | awk '{print $1}'`
p2_end_current=`fdisk -l /dev/mmcblk0 | grep $PARTITION | awk '{print $3}'`
p2_end=$(($maximum-400000))
p3_start=$(($p2_end+1))

# echo "p2start: $p2_start , maximium: $maximum , p2_end_current: $p2_end_current , p2_end: $p2_end , p3_start: $p3_start"

# Shrinking is not allowed!
if [ -z "$p2_start" -o ! "$p2_end_current" -lt "$p2_end" ]; then
	echo "Resize not successful! Maybe already expanded? Make sure no USB-drives are attached when trying to expand."
	exit 0;
fi 

fdisk /dev/mmcblk0 <<EOF &>> $rsflog
p
d
2
n
p
2
$p2_start
$p2_end
p
n
p
3
$p3_start

p
w
EOF

cat <<\EOF > /etc/init.d/resize2fs_once
#!/bin/sh
### BEGIN INIT INFO
# Provides:          resize2fs_once
# Required-Start:
# Required-Stop: 
# Default-Start: 2 3 4 5 S
# Default-Stop:
# Short-Description: Resize the root filesystem to fill partition
# Description:
### END INIT INFO

case "$1" in
  start)
    echo "Starting resize2fs_once" 
    resize2fs /dev/$PARTITION    
    update-rc.d resize2fs_once remove
    rm /etc/init.d/resize2fs_once
    mkfs -t ext4 /dev/mmcblk0p3
    echo "Finished"
    ;;
  *)  
    echo "Usage: $0 start" >&2
    exit 3
    ;;
esac  
EOF

sed -i "s/\$PARTITION/$PARTITION/" /etc/init.d/resize2fs_once

chmod +x /etc/init.d/resize2fs_once
update-rc.d resize2fs_once defaults

# On Raspbian Jessie just start script...
if [ "$(lsb_release -r | grep '8.0' | wc -l)" -gt "0" ]; then
	/etc/init.d/resize2fs_once start
	sed -i "s@^exit 0@resize2fs /dev/$PARTITION;sed -i \"s=resize.*==\" /etc/rc.local\nexit 0@" /etc/rc.local
fi

# Autoreboot if REBOOT is set
if [ "$2" = "REBOOT" ]; then
	sleep 2;
	reboot
fi

echo "Filesystem Extended. <b><a href='/plugins/max2play_settings/controller/Basic.php?action=reboot'>Please reboot to take effect</a></b>"
return 0


#############  Shrink Filesystem   ################


cat <<\EOF > /etc/init.d/resize2fs_once
#!/bin/sh
### BEGIN INIT INFO
# Provides:          resize2fs_once
# Required-Start:
# Required-Stop: 
# Default-Start: 2 3 4 5 S
# Default-Stop:
# Short-Description: Resize the root filesystem to fill partition
# Description:
### END INIT INFO

. /lib/lsb/init-functions

case "$1" in
  start)
    log_daemon_msg "Starting resize2fs_once" &&    
    resize2fs /dev/mmcblk0p2    
    rm /etc/init.d/resize2fs_once &&
    update-rc.d resize2fs_once remove &&
    log_end_msg $?
    ;;
  *)  
    echo "Usage: $0 start" >&2
    exit 3
    ;;
esac  
EOF

chmod +x /etc/init.d/resize2fs_once
update-rc.d resize2fs_once defaults
  
echo "Filesystem Extended. Please reboot to take effect"
return 0
