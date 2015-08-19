Max2Play - Webinterface
=======================

Browser Administration for Linux-Based Audio/Video-Player like ODROID or Raspberry Pi.

More Information on <a href="http://www.max2play.com/">http://www.max2play.com</a> and <a href="http://shop.max2play.com/">http://shop.max2play.com</a>

Find a live Demo of Max2Play Web interface at <a href="http://demo.max2play.com:81/ ">http://demo.max2play.com:81/</a>. 

FEATURES
 - browser interface for configuring WiFi, filesystem mountpoints, autostarts, display resolution, init-scripts, etc.
 - no need to ssh to your device any more
 - Plugin configuration to add new features and customize navigation
 - custom CSS and logo possible 
 - easy to use even for non-programmers (configure mount-points, samba-shares, start/stop programs)
 - responsive layout - optimized for mobile devices
 - adds plug & play to existing audio/video-player releases
 - multilanguage support for basic settings and new plugins (german and english are already available with PO-files)

HOW TO INSTALL
 - install & configure apache webserver with php5 support: "apt-get install apache2 php5 php5-json"
 - Enable mod_rewrite for Apache "a2enmod rewrite"
 - Add "/etc/apache2/sites_enabled/max2play.conf" from repository "CONFIG_SYSTEM/apache2/" to point to the max2play directory and remove default from "/etc/apache2/sites_enabled/"
 - drop the files from the max2play-Folder of this repository to "var/www/max2play/"
 - reload webserver "/etc/init.d/apache2 reload"

HOW TO CONFIGURE
 - every task for the browserinterface needs little setup to work properly
 - the browser interface itsself uses script-files and configuration-files located in "/opt/max2play"
 - some file-permissions and sudoer-configs need to be set in order to get wifi and other things working in the max2play-interface
 - activate or deactivate Plugins on "Reboot / Reset"-Tab of webinterface

HOW TO UPDATE
 - On Basic-Settings in Webinterface click "check Max2play Update"
 - If a new version is available, the files in webserver-folder and /opt/max2play will be overwritten, just keeping wifi, samba and playername config-files
 
FILESTRUCTURE OF MAX2PLAY PROJECT
 - Webinterface is under /max2play
 - Config-Files for /etc/ under /CONFIG_SYSTEM
 - Config-Files for /home/USER under /CONFIG_USER
 - Packages with Scripts for /opt/ under /opt/

HOW TO CREATE NEW PLUGINS
- copy an existing Plugin (e.g. a small one that exists):
    <i>cp -r /var/www/max2play/application/plugins/squeezeplug /var/www/max2play/application/plugins/myplugin</i>
- change the Pluginname /var/www/max2play/application/plugins/myplugin/controller/Setup.php
- there is always a backend- (folder "controller") and a frontend-file (folder "view"). Both have the same name and go together (see MVC-Schema).
- if you want to call shell scripts best use the function $this->writeDynamicScript(array("SCRIPT GOES HERE")); 
- you can work with the return value of your script and pass it to the view 
- the view is used to place buttons and output data as well as take input data from forms
- to activate your new plugin go to "Reset/Reboot" and mark your new Plugin -> save and reload page -> now your Plugin should be visible in the navigation

SCREENS OF CURRENT USAGE - THIS IS HOW IT LOOKS LIKE

<img src="https://raw.githubusercontent.com/max2play/webinterface/master/OTHER/screens/webinterface_audioplayer.gif" />
<img src="https://raw.githubusercontent.com/max2play/webinterface/master/OTHER/screens/webinterface_reset.gif" />
<img src="https://raw.githubusercontent.com/max2play/webinterface/master/OTHER/screens/webinterface_filesystem.gif" />
<img src="https://raw.githubusercontent.com/max2play/webinterface/master/OTHER/screens/webinterface_callblocker.gif" />


This file is work in progress...
