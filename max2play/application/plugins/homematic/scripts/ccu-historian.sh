#!/bin/bash
### BEGIN INIT INFO
# Provides:          ccu-historian
# Required-Start:    $network $local_fs $remote_fs
# Required-Stop::    $network $local_fs $remote_fs
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts ccu-historian
# Description:       starts ccu-historian
### END INIT INFO
(( EUID )) && echo .You need to have root priviliges.. && exit 1
PIDF=/opt/ccu-historian/ccu-historian.pid
CONFF=/opt/ccu-historian/ccu-historian.config
JAVACMD=/usr/bin/java
CCUCMD=/opt/ccu-historian/ccu-historian.jar
RETVAL=0

start() {
            echo -n "Starting ccu-historian"
            start-stop-daemon --start --pidfile $PIDF --make-pidfile --background  --exec $JAVACMD -- -jar $CCUCMD -config $CONFF
            RETVAL=$?
}

stop() {
            echo -n "Stopping ccu-historian"
            start-stop-daemon --quiet --stop --pidfile $PIDF
            RETVAL=$?
}
case "$1" in
    start)
      start
  ;;
    stop)
      stop
  ;;
    restart)
      stop
      start
  ;;
    *)
      echo "Usage: ccu-historian {start|stop|restart}"
      exit 1
  ;;
esac
exit $RETVAL