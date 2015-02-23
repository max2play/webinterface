#!/bin/sh

# if no Parameter is given, this takes in consideration /dev/mmcblk0p2 as the rootfs! 

if [ -z "$1" ] ; then
	PARTITION="mmcblk0p2"
else
	PARTITION=$1
fi
echo $PARTITION

p2_start=`fdisk -l /dev/mmcblk0 | grep $PARTITION | awk '{print $2}'`

if [ -z "$p2_start" ]; then
	echo "Resize not successful"
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

. /lib/lsb/init-functions

case "$1" in
  start)
    log_daemon_msg "Starting resize2fs_once" &&
    resize2fs /dev/$PARTITION &&
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

sed -i "s/\$PARTITION/$PARTITION/" /etc/init.d/resize2fs_once

chmod +x /etc/init.d/resize2fs_once
update-rc.d resize2fs_once defaults
  
echo "Rootfs Extended. Please reboot to take effect"
return 0

