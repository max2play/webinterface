#!/bin/sh

# this takes in consideration /dev/mmcblk0p2 as the rootfs! 

p2_start=`fdisk -l /dev/mmcblk0 | grep mmcblk0p2 | awk '{print $2}'`

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
    resize2fs /dev/mmcblk0p2 &&
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
  
echo "Rootfs Extended. Please reboot to take effect"
return 0

