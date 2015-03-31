#!/bin/bash
LOGFILE=/opt/max2play/cache/fritzbox.txt
LOGSTATE=/opt/max2play/cache/fritzbox_state.txt
rm $LOGSTATE

while true; do
	tail -30 $LOGFILE > $LOGFILE.old; mv $LOGFILE.old $LOGFILE
	CONFIG=$(grep "HM_FRITZ\|HM_HOMEMATIC" /opt/max2play/options.conf)
	IP=$(echo "$CONFIG" | grep "HM_FRITZ_IP" | sed 's/HM_FRITZ_IP=//')
	USER=$(echo "$CONFIG" | grep "HM_FRITZ_USER" | sed 's/HM_FRITZ_USER=//')
	SECRET=$(echo "$CONFIG" | grep "HM_FRITZ_SECRET" | sed 's/HM_FRITZ_SECRET=//')
	DEVICE_LIST=( $(echo "$CONFIG" | grep "HM_FRITZ_DEVICE_LIST" | sed 's/HM_FRITZ_DEVICE_LIST=//') )
	HMVAR=( $(echo "$CONFIG" | grep "HM_FRITZ_HMVAR" | sed 's/HM_FRITZ_HMVAR=//') )
	LOOPTIME=$(echo "$CONFIG" | grep "HM_FRITZ_LOOPTIME" | sed 's/HM_FRITZ_LOOPTIME=//')
	HMIP=$(echo "$CONFIG" | grep "HM_HOMEMATIC_IP" | sed 's/HM_HOMEMATIC_IP=//')
	
	if [ "$HMIP" == "" ]; then
		HMIP="http://homematic.fritz.box"
	fi
	
	# Challenge abholen
	CHALLENGE=`wget -T 10 -t 1 -O - "http://$IP/login_sid.lua" 2>/dev/null | sed 's/.*<Challenge>\(.*\)<\/Challenge>.*/\1/'`
	
	# login aufbauen und hashen
	CPSTR="$CHALLENGE-$SECRET"
	
	MD5=`echo -n $CPSTR | iconv -f ISO8859-1 -t UTF-16LE | md5sum -b | awk '{print substr($0,1,32)}'`
	RESPONSE="$CHALLENGE-$MD5"
	URL_PARAMS="username=$USER&response=$RESPONSE"
	
	# login senden und SID herausfischen
	SID=`wget -T 10 -t 1 -O - "http://$IP/login_sid.lua?$URL_PARAMS" 2>/dev/null | sed 's/.*<SID>\(.*\)<\/SID>.*/\1/'`
	if [ "$SID" == "" ];then
        echo "Fritzbox Anmeldungsfehler"
    fi
	
	
	DEVICES=`wget -T 10 -t 1 -O - "http://$IP/net/network_user_devices.lua?sid=$SID" 2>/dev/null | grep uiLanActive | sed 's/.*uiLanActive\(.*\)uiLanPassive.*/\1/'`
	
	for (( i = 0; i < ${#DEVICE_LIST[@]} ; i++ ))
	do
	    active=$(echo $DEVICES | grep "${DEVICE_LIST[$i]}" | wc -l)
	    
	    OLDSTATE=$(grep -i "${DEVICE_LIST[$i]}" $LOGSTATE)
	    if [ "$OLDSTATE" == "" ]; then
	    	echo ${DEVICE_LIST[$i]} >> $LOGSTATE
	    fi
	    
	    OLDSTATE_ACTIVE=$(echo $OLDSTATE | grep " 1" | wc -l)
	    if [ "$OLDSTATE_ACTIVE" == "$active" -a ! "$OLDSTATE" == "" ]; then
	       echo "Nothing to do"
	    else 	    
		    if [ $active -gt 0 ]; then
		      sed -i "s/${DEVICE_LIST[$i]}.*/${DEVICE_LIST[$i]} 1/" $LOGSTATE
		      wget -T 10 -t 1 -q -O /dev/null "$HMIP/config/xmlapi/statechange.cgi?ise_id=${HMVAR[$i]}&new_value=true"
		      echo $(date) ${DEVICE_LIST[$i]}, angemeldet >> $LOGFILE
		      echo "URL $HMIP/config/xmlapi/statechange.cgi?ise_id=${HMVAR[$i]}&new_value=true" >> $LOGFILE
		    else
		      sed -i "s/${DEVICE_LIST[$i]}.*/${DEVICE_LIST[$i]} 0/" $LOGSTATE
		      wget -T 10 -t 1 -q -O /dev/null "$HMIP/config/xmlapi/statechange.cgi?ise_id=${HMVAR[$i]}&new_value=false"
		      echo $(date) ${DEVICE_LIST[$i]}, abgemeldet >> $LOGFILE
		      echo "URL $HMIP/config/xmlapi/statechange.cgi?ise_id=${HMVAR[$i]}&new_value=false" >> $LOGFILE
		    fi
	    fi
	done
	
	#echo "sleep $LOOPTIME" >> $LOGFILE
	sleep $LOOPTIME
done
