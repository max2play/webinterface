#!/bin/bash
if [ "$1" = "" ]; then
	echo "Usage dos2unix /path/to/file"
else	
	for file in $(find $1 -type f); do
		echo "$file"
		#ONLY FOR .sh, php or .conf files!!!   		
   		tr -d '\r' <$file >temp.$$ && mv temp.$$ $file   		   		
   		chmod 777 $file
	done
fi
