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
 - install & configure apache webserver with php5 support: "apt-get install apache2 php5"
 - edit "/etc/apache2/sites_enabled" to point to the max2play directory
 - drop the files of this repository to "var/www/max2play/"
 - reload webserver "/etc/init.d/apache2 reload"

HOW TO CONFIGURE
 - every task for the browserinterface needs little setup to work properly
 - the browser interface itsself uses script-files and configuration-files located in "/opt/max2play"
 - some file-permissions and sudoer-configs need to be set in order to get wifi and other things working in the max2play-interface


Webinterface is under /max2play

Config-Files for /etc/ under /CONFIG_SYSTEM

Config-Files for /home/USER under /CONFIG_USER

Packages with Scripts for /opt/ under /opt/


This file is work in progress...

