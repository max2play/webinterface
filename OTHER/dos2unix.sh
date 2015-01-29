#!/bin/bash
if [ "$1" = "" ]; then
	echo "Usage dos2unix /path/to/file"
else	
	for file in $(find $1 -type f); do
   		tr -d '\r' <$file >temp.$$ && mv temp.$$ $file
   		chmod 777 $file
	done
fi
