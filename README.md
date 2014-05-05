Max2Play - Webinterface
============

Browser Administration for Linux-Based Audio/Video-Player like ODROID or Raspberry Pi.

More Information on <a href="http://shop.max2play.com/">http://shop.max2play.com</a>

FEATURES
 - browser interface for configuring WiFi, filesystem mountpoints, autostarts, display resolution, init-scripts, etc.
 - no need to ssh to your device any more
 - easy to use even for non-programmers
 - adds plug & play to existing audio/video-player releases

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

HOW TO UPDATE
 - On Basic-Settings in Webinterface click "check Max2play Update"
 - If a new version is available, the files in webserver-folder and /opt/max2play will be overwritten, just keeping wifi, samba and playername config-files
 
Webinterface is under /max2play

Config-Files for /etc/ under /CONFIG_SYSTEM

Config-Files for /home/USER under /CONFIG_USER

Packages with Scripts for /opt/ under /opt/


This file is work in progress...
