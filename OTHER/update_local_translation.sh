#!/bin/bash

echo "Get current translationfiles from translate.max2play.com"
ssh -l root $SERVERIP /var/www/pootle/translations.sh exportmo
wget translate.max2play.com/assets/export.zip
echo "finished"
