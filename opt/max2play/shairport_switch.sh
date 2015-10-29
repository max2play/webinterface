#!/bin/bash

### Squeezebox switch

LMSIP=$(grep -a "LMSIP" /opt/max2play/audioplayer.conf | sed -n -e 's/^[A-Z_]*\=//p')
LMSPort=9090

ETH0=$(cat /sys/class/net/eth0/operstate)
if [ "$ETH0" = "up" ]; then
    INTERFACE=eth0
else
    INTERFACE=wlan0
fi
if [ -e /sys/class/net/$INTERFACE/address ]; then
  SBC_MAC=$(cat /sys/class/net/$INTERFACE/address)
  echo "Use Interface $INTERFACE with $SBC_MAC"
else
  echo "No Mac-Address set"
  exit 1
fi


### INIT
shopt -s nocasematch
echo "$1"

if [ "$1" == "resume" ] ; then
  ### Resume
  SBC_PW=$(printf "${SBC_MAC} power 1\nexit\n" | nc $LMSIP $LMSPort 2>/dev/null | cut "-d " -f 3)
  SBC_LM=$(printf "${SBC_MAC} playerpref lastmode ?\nexit\n" | nc $LMSIP $LMSPort 2>/dev/null | cut "-d " -f 4)
  if [ "${SBC_LM}" == "play" ] ; then
    SBC_MO=$(printf "${SBC_MAC} mode play\nexit\n" | nc $LMSIP $LMSPort 2>/dev/null | cut "-d " -f 3)
  fi
else
  ### Shutup
  SBC_MO=$(printf "${SBC_MAC} mode ?\nexit\n" | nc $LMSIP $LMSPort 2>/dev/null | cut "-d " -f 3)
  SBC_LM=$(printf "${SBC_MAC} playerpref lastmode ${SBC_MO}\nexit\n" | nc $LMSIP $LMSPort 2>/dev/null | cut "-d " -f 4)
  SBC_PW=$(printf "${SBC_MAC} power 0\nexit\n" | nc $LMSIP $LMSPort 2>/dev/null | cut "-d " -f 3)
fi
