��          L      |       �   ,   �      �      �           *  �  @  �    �    �   �  �   y  �  n                                         Important Information Filesystem Description SQUEEZEPLAYER INFO DESCRIPTION SQUEEZESERVER INFO DESCRIPTION WLAN INFO DESCRIPTION XBMC INFO DESCRIPTION Project-Id-Version: Max2Play Übersetzungen
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2014-04-17 15:55+0100
PO-Revision-Date: 2014-04-17 17:38+0100
Last-Translator: 
Language-Team: 
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Language: en_GB
X-Poedit-KeywordsList: _;gettext;gettext_noop;translate
X-Poedit-Basepath: .
X-Poedit-SourceCharset: UTF-8
X-Generator: Poedit 1.5.4
X-Poedit-SearchPath-0: y:\projects\Max2Play-Git
 Network Shares like NFS (e.g. Synology Diskstation) are mounted following the example:<br />	<b>Mountpoint (IP or hostname and Path):</b> z.B. <i>//IP-ADDRESS/PATH</i> <br /><b>existing Path on Max2Play (for usage in Squeezeserver or XBMC):</b> <i>/mnt/mountdir/</i> <br />	<b>Type:</b> mostly <i>cifs</i> <br /><b>Options (user, password and other options for network share):</b> e.g. <i>user=name,password=pass,sec=ntlm,iocharset=utf8</i><br />important: always add to the options <i>sec=ntlm</i> ! Squeezelite is a software designed for the Squeezebox player, which works with the Logitech Media Server (Squeezebox Server) and is similar to a Squeezebox Receiver and how it is controllable via the server.<br />Shairport is a service of Apple Airplay. <br />Both services of ODROID will start with a slight delay to load (less than a minute) to avoid conflict with the sound drivers.   Squeezeserver (Logitech Media Server) is the server used for Squeezebox and is responsible for the control of the player. At least one such network server should be running. The server can be installed on the Start Setup on Max2Play. Info: Please restart the device after changing the network parameters! When operating several Max2Play devices, the MAC address for LAN must be different in each case on the devices. 	<br /> Keep in mind the network name upper and lower case.   XBMC is a Media-Center for videos, Music and more.<br /><br /><sup>#1</sup>Autostart XBMC: if activted, the device boots directly into XBMC. This option is suggested if you use the Player mostly for videos. If the device is not connected to a TV or the video playback is seldom used remove the XBMC from autostart. Therefore it uses less resources.<br /><br /><b>IMPORTANT:</b><br />If XBMC is running Squeezelite will be deactivated! This is neccessary to get Audio in XBMC working. XBMC uses pulseaudio and Squeezelite as well as Shairplay use Alsa to play sounds/music at the same time. When XBMC is closed, Squeezelite will start again if it is set to autostart. 