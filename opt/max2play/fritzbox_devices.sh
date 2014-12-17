/bin/bash
LOGFILE=/opt/max2play/cache/fritzbox.txt

while true; do
	rm $LOGFILE
	
	CONFIG=$(grep "HM_FRITZ" /opt/max2play/options.conf)
	IP=$(echo "$CONFIG" | grep "HM_FRITZ_IP" | sed 's/HM_FRITZ_IP=//')
	USER=$(echo "$CONFIG" | grep "HM_FRITZ_USER" | sed 's/HM_FRITZ_USER=//')
	SECRET=$(echo "$CONFIG" | grep "HM_FRITZ_SECRET" | sed 's/HM_FRITZ_SECRET=//')
	DEVICE_LIST=( $(echo "$CONFIG" | grep "HM_FRITZ_DEVICE_LIST" | sed 's/HM_FRITZ_DEVICE_LIST=//') )
	HMVAR=( $(echo "$CONFIG" | grep "HM_FRITZ_HMVAR" | sed 's/HM_FRITZ_HMVAR=//') )
	LOOPTIME=$(echo "$CONFIG" | grep "HM_FRITZ_LOOPTIME" | sed 's/HM_FRITZ_LOOPTIME=//')
	
	# Challenge abholen
	CHALLENGE=`wget -O - "http://$IP/login_sid.lua" 2>/dev/null | sed 's/.*<Challenge>\(.*\)<\/Challenge>.*/\1/'`
	
	# login aufbauen und hashen
	CPSTR="$CHALLENGE-$SECRET"
	
	MD5=`echo -n $CPSTR | iconv -f ISO8859-1 -t UTF-16LE | md5sum -b | awk '{print substr($0,1,32)}'`
	RESPONSE="$CHALLENGE-$MD5"
	URL_PARAMS="username=$USER&response=$RESPONSE"
	
	# login senden und SID herausfischen
	SID=`wget -O - "http://$IP/login_sid.lua?$URL_PARAMS" 2>/dev/null | sed 's/.*<SID>\(.*\)<\/SID>.*/\1/'`
	#echo $SID
	
	DEVICES=`wget -O - "http://$IP/net/network_user_devices.lua?sid=$SID" 2>/dev/null | grep uiLanActive | sed 's/.*uiLanActive\(.*\)uiLanPassive.*/\1/'`
	
	for (( i = 0; i < ${#DEVICE_LIST[@]} ; i++ ))
	do
	    active=$(echo $DEVICES | grep "${DEVICE_LIST[$i]}" | wc -l)
	    if [ $active -gt 0 ] ; then
	      wget -q -O /dev/null "http://homematic.fritz.box/config/xmlapi/statechange.cgi?ise_id=${HMVAR[$i]}&new_value=true" | echo $(date) ${DEVICE_LIST[$i]}, angemeldet >> $LOGFILE
	    else
	      wget -q -O /dev/null "http://homematic.fritz.box/config/xmlapi/statechange.cgi?ise_id=${HMVAR[$i]}&new_value=false" | echo $(date) ${DEVICE_LIST[$i]}, abgemeldet >> $LOGFILE
	    fi
	done
	
	echo sleep $LOOPTIME >> $LOGFILE
	sleep $LOOPTIME
done
