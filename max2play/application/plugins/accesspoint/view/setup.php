<?php
/**
	 Example Setup View File
	
	 @Copyright 2014 Stefan Rick
	 @author Stefan Rick
	 Mail: stefan@rick-software.de
 	 Web: http://www.netzberater.de
	
	 This program is free software; you can redistribute it and/or modify
	 it under the terms of the GNU General Public License as published by
	 the Free Software Foundation; either version 2 of the License, or
	 (at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details.
	
	 You should have received a copy of the GNU General Public License along
	 with this program; if not, write to the Free Software Foundation, Inc.,
	 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */ 
?>											


<h1 class="entry-header">
	<?php echo _t("Accesspoint Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($accesspoint->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $accesspoint->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<?php echo _t('You can easily turn your device into an accesspoint for other WiFi-devices. Once installed the new WiFi network created by this device enables other devices to access its features and services. It also shares the internet connection if available and forwards all traffic to the LAN-network.');?>
	
	<br />
	<?php echo _t('This happens on installation: 
			<ul class="description">
				<li>a DHCP-Server will be installed</li>
				<li>a static WiFi-entry will be added to your interfaces</li>
				<li>the WiFi of this device will be used to give access to other devices</li>
				<li>as long as the accesspoint is installed, you cannot connect to other WiFi-networks with this device</li>
			</ul>')?>
		
	<br /><br />		
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		<?php if(!$accesspoint->view->installed){ ?>
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Set up an accesspoint on this device") ?></b></p>
				<input type="button" id="install" name="<?php echo _("install") ?>" onclick="document.getElementById('action').value='install';submit();" value="<?php echo _("install") ?>" />		
		<?php } else { ?>
			<?php echo str_replace('$URL', 'http://'.$_SERVER['HTTP_HOST'], _t('Accesspoint is installed and active! On your WiFi-devices that are connected to this accesspoint open a browser with <a href="$URL">$URL</a> to get to this webinterface.')); ?>
			<br /><br />
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-cancel" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Remove accesspoint from this device") ?></b></p>
				<?php echo _t("The accesspoint feature is currently installed and active. You have to uninstall the accesspoint-modus to get back to the normal usage of your WiFi.") ?>
				<input type="button" id="uninstall" name="<?php echo _("uninstall") ?>" onclick="document.getElementById('action').value='uninstall';submit();" value="<?php echo _("uninstall") ?>" />			
			
			<br /><br />
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Edit basic accesspoint settings") ?></b></p>
			<div class="optiondetails"> 
				<table class="settings">
				  <tr>
					<td><?php echo _t("SSID") ?></td>
					<td>
						<input type="text" id="ssid" name="ssid" value="<?php echo $accesspoint->config->ssid ?>" />
					</td>
					<td><?php echo _t("The name of this WiFi network") ?></td> 
				  </tr>	 
				  <tr>
					<td><?php echo _t("Passphrase") ?></td>
					<td><input type="text" id="passphrase" name="passphrase" value="<?php echo $accesspoint->config->passphrase ?>" /></td>
					<td><?php echo _t("The WiFi passphrase for this network"); ?></td> 
				  </tr>
				  <tr>
					<td><?php echo _t("Standalone") ?></td>
					<td>
						<input type="checkbox" id="standalone" name="standalone" value="1" <?php if ($accesspoint->config->standalone) echo "checked"; ?> />
					</td>
					<td><?php echo _t("The accesspoint redirects all external requests to itself and sets up a default gateway to its IP-adress. Always activate this, if the device is not connected to the internet and if there is no other router connected by LAN-interface. Otherwise the audioplayers (e.g. squeezelite) will not work correctly.") ?></td> 
				  </tr>
				</table>
				<br />
				<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='saveSettings';submit();" value="<?php echo _("save") ?>" />
			</div>
		<?php } ?>
		
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($accesspoint->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	