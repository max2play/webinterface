#!/bin/bash

echo "This script creates a new Plugin within the Max2Play application Folder."
echo "Enter the name of the new Plugin. Please choose a lowercase name like 'example' or 'myplugin': "
read name

pluginname="/var/www/max2play/application/plugins/$name"

uppercase="${name^}"

echo "Creating $uppercase in $pluginname"

if [ -e $pluginname ]; then
        echo "Plugin already existing! Not created."
elif [ -z "$name" ]; then
        echo "No valid Name! Not created."
else
        cp -R /var/www/max2play/application/plugins/example/ $pluginname        
        sed -i "s/Exampleclass/$uppercase/g" $pluginname/controller/Setup.php
        sed -i "s/exampleclass/$name/g" $pluginname/controller/Setup.php
        sed -i "s/exampleclass/$name/g" $pluginname/view/setup.php
        echo "Plugin $name Created"
fi
