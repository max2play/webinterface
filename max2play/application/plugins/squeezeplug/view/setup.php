<h1 class="entry-header">
	<?php echo _t("SqueezePlug & Max2Play - Info") ?>
</h1>
	
<div class="entry-content">
    <?php if(isset($squeezeplug->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $squeezeplug->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	<h2><?php echo _t("Welcome to SqueezePlug & Max2Play - Your easy to use Multiroom Audio Solution!")?></h2>	
	<br />
	<?php echo _t("SqueezePlug & Max2Play is the new combination of <b>SqueezePlug</b>, the famous Multiroom Audio Solution and <b>Max2Play</b>, the web based framework for controlling Linux based Mini-Computer like e.g. Raspberry Pi, Odroid and others by an simple webinterface without any Linux Know-How.<br />
	<br />
	<b>SqueezePlug</b> is now a plug-in in <b>Max2Play</b> to make the configuration as easy as possible. There is no need for connecting a monitor, a keyboard or a mouse on the device itself. It all runs headless and no special tools like Putty are needed. The configuration is as simple as configuring a router from an webinterface!"); ?><br />  
<br />
<h1 class="entry-header">
	<?php echo _t("Requirements") ?>
</h1>
	<h2><?php echo _t("Hardware")?></h2>
	<?php echo _t("<ul class='description'><li>Raspberry Pi (all types, best: Raspberry Pi 2 Model B), or Odroid U3, or Odroid C1</li>
	<li>other devices will be supported in future</li>
	<li>SD-Card minimum 4GB (Class 10 is recommended)</li>
	<li>optional: Raspberry Pi I2S Soundcard</li>
	<li>optional: USB Soundcard</li>
	<li>optional: WiFi-Adaptor, Keyboard, Mouse, and other peripheral devices</li></ul>"); ?>
	<b><?php echo _t("Please always use an appropriate power supply to power your Mini-Computer! Insufficient power supplies can be the reason for many problems!") ?></b> <br />
	<br />
	<h2><?php echo _t("Software")?></h2>
	<?php echo _t("<ul class='description'><li>Tool for writing SD-Cards</li>
	<li>Webbrowser</li>
	<li>No other software is needed!!!</li></ul>"); ?>
	<b><?php echo _t("Please remember, that you will need a working Internet-Connection for your Mini-Computer!") ?></b><br />
	<br />
<h1 class="entry-header">
	<?php echo _t("How to get it working") ?></h1>

	<?php echo _t("There are only a few steps necessary to get your Mini-Computer working as an easy to use Multiroom Audio Solution. Follow the next steps and you will be ready to go in a few minutes.") ?>
	
	<ol class="boldorderedlist">
	<li><span>
	<?php echo _t('<b>Download the latest SqueezePlug & Max2Play SD-Card Image</b><br /> 
    Go to <a href="http://www.squeezeplug.de/?page_id=52" target="_blank">SqueezePlug Downloads</a> or <a href="http://www.max2play.com/max2play-image/" target="_blank">Max2Play Downloads</a> to get the latest version of SqueezePlug & Max2Play. Download and save it to your PC.') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Burn the image to the SD-Card</b><br /> 
	Use a tool like <a href="http://www.alexpage.de/usb-image-tool/download/" target="_blank">USB Image Tool</a> to burn the downloaded image to the SD-Card. It is also possible to get a ready to start image from our shop: shop.max2play.com.') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Start your Mini-Computer</b><br /> 
	Put in the SD-Card (or eMMC on Odroid),connect the LAN-Cable and the power supply to your Mini-Computer and boot it up.') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Connect to your Mini-Computer by an webbrowser</b><br /> 
	Type "max2play" in the address bar of your webbrowser. If the SqueezePlug & Max2Play site does not start up, try to connect via the IP-Address. For that you will need to know this address. Look it up in your router or use a tool like <a href="http://www.advanced-ip-scanner.com" target="_blank">Advanced IP Scanner</a>. If you are not able to connect either ways, connect a monitor and a keyboard to the Mini-Computer, log in and type "ifconfig". The login is different for the different Mini-Computers (e.g. use user "pi" and password "raspberry" on the Raspberry Pi, user "odroid" and password "odroid" on the Odroid devices.)') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Expand the SD-Card</b><br /> 
	To get use of the full space of the SD-Card you have to expand it. Remember that the minimum size is 4 GB. Even if your card is a 4GB card, please expand the card, before doing something else. Otherwise you will run out of space immediately. To expand the card choose the "SETTINGS / REBOOT" menu, go to the section "Reboot / Update / Filesystem Settings" and click on "Expand Filesystem". Afterwards please reboot to take effect! ') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Do some basic configuration</b><br /> 
	After rebooting has finished, go back to the "SETTINGS / REBOOT" menu. From here you should be able to configure the basic settings. Everything you need is explained here. It is always a good idea to update the system from here. The necessary plug-ins for SqueezePlug are already installed, choose others if you like.') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Configure the network settings</b><br /> 
	Choose the "WiFi / LAN" menu to configure your network settings. SqueezePlug & Max2Play uses the wired connection with DHCP by default. This means that you will get an IP-Address from your router while booting, if DHCP on the router is enabled. A LAN-Connection is always necessary on the first boot. If you like, you can configure a additional WLAN(WiFi)-Connection. If the WLAN-Connection is working, you can simply cut the LAN-Connection. Please remember, the IP-Address will change if you connect by WiFi instead of LAN.') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Configure the path to your files</b><br /> 
	Choose the "Filesystem Mount" menu to configure the path to your Media-Files like music, movies, pictures etc. Your Media-Files can be saved at different locations: On the SD-Card of the Mini-Computer itself, on an attached USB-drive, or on an Samba or NFS Network-Share. If the Media-Files are stored on the SD-Card you booted from, there is no need to do anything here. If it is stored on a USB-drive this will be mounted automatically to /media/usb and /media/usb0. If you attach more than one USB-drive, they will be mounted to /media/usb1 to /media/usb7. If your Media-Files are stored on an network location you have to configure it from this site. Everything you will need to do is explained here. If you like to enable the Mini-Computer to work as a Samba-Server, go to the section "Sambashares on Max2Play - setup network share paths". If configured correctly, you are now able to see your Mini-Computer as a network-share form the PCs in your home LAN. You can now easily transfer files to your Mini-Computer from other computers in your Home-LAN.') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Install SqueezeBoxServer</b><br /> 
	If you like, you can install SqueezeBoxServer (Logitech Mediaserver). SqueezeBoxServer is one of the best Media-Servers you can get. Choose the Menu " Squeezeserver
	" and you will find all you need to get SqueezeBoxServer installed. Please remember, that it will take a while, till the SqueezeBoxServer webinterface is reachable after the installation. After the installation has finished, you can access the SqueezeBoxServer webinterface by choosing "Start Squeezeserver Webadministration". One of the first things you have to configure here is the path to your media files. Please choose the path depending on the settings in the earlier steps.') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Configure AudioPlayer</b><br /> 
	To configure you Mini-Computer as an AudioPlayer choose the menu "Audioplayer". From here you can configure three different players. For the SqueezeBox environment you need the SqueezeLite player. To play from your Apple-Devices you need ShairPort. The gmediarender is a DLNA-Client for audio running with all DLNA compatible media-servers. All the players can run simultaneously. Please configure the appropriate soundcard for your system. ') ?>
	</span></li>
	<li><span>
	<?php echo _t('<b>Additional Settings</b><br /> 
	There are a lot of other setting or tweaks you can choose from the menus. E.g. if you like install Kodi (f.k.a. XBMC). It can easily coexist with SqueezeBoxServer and all other components of SqueezePlug & Max2Play.') ?>
	</span></li>
	</ol>	
	
	

<h1 class="entry-header">
	<?php echo _t("Video Tutorial") ?>
</h1>
	
<br />
	
	
	
	
	<iframe width="560" height="315" src="<?php echo _("//www.youtube.com/embed/GjYnHZF2Oek")?>" frameborder="0" allowfullscreen></iframe>
	<br /><br />
	<?php echo _t('Questions concerning this project? Please refer to <a href="http://shop.max2play.com" target="_blank">Shop.Max2Play</a>') ?><br />
	<br /><br />
	<b><?php echo _t("READ CAREFULLY: THIS INTERFACE IS STILL A BETA AND MIGHT HAVE BUGS OR UNEXPECTED BEHAVIOR!") ?></b>
</div>	