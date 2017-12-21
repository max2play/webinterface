<?php
/**
 Wlan / Lan Settings View File
 
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
	<?php echo _("WiFi & Network configuration") ?>
</h1>

<div class="entry-content">
	<?php

$message = $wlan->view->message;
$error = $wlan->view->error;
include (APPLICATION_PATH . '/view/messages.php');
?>	
	
	<form action="" method="get" id="form1">
		<input type="hidden" id="action" name="action" value="" /> <b><?php echo _("Status") ?>:</b> 
		<?php echo str_replace(array('$STATUS', '$CONNECTION'), array(($wlan->view->wlan_active) ? _('active') : _('not active'), ($wlan->view->wlan_configured) ? _('active').' (IP: '.$wlan->view->wlan_ip.')' : _('not active')), _('WiFi USB adapter status is <b>$STATUS</b>. WiFi connection status is <b>$CONNECTION</b> ')) ?> 
		<br /><?php echo _("(Only WPA/WPA2-Encryption possible)") ?>
		<br />
		<br />
		<table>
			<tr>
				<td><?php echo _("Load WiFi Interface") ?>:</td>
				<td><input type="checkbox" id="wlan_configured"
					name="wlan_configured"
					<?php if($wlan->view->wlan_configured == true) echo 'checked'; ?>
					value="1" /></td>
			</tr>
			<tr>
				<td><?php echo _("Network SSID") ?>:</td>
				<td><input type="text" id="ssid" style="width: 150px;" name="ssid"
					value="<?php echo $wlan->view->ssid ?>" /></td>
			</tr>
			<tr>
				<td><?php echo _("Password") ?>:</td>
				<td><input type="text" style="width: 150px;" id="psk" name="psk"
					value="<?php echo $wlan->view->webpsk ?>" /></td>
			</tr>
		</table>

		<br />
		<br />
	<?php echo _("List of available networks") ?>: 
	
	<script type="text/javascript">
		var networks = new Array();
		
		<?php

$i = 0;
if (isset($wlan->view->wlanNetworks))
    foreach ($wlan->view->wlanNetworks as $network) {
        ?>					
					networks[<?php echo $i;?>] = new Array();
					networks[<?php echo $i;?>]["ESSID"] = "<?php echo $network['ESSID']; ?>";
					networks[<?php echo $i++;?>]["GCIPHER"] = "<?php echo $network['GCIPHER']; ?>";
		<?php } ?>

		function setNetwork(){
			var e = document.getElementById('choose_ssid');
			document.getElementById('ssid').value=networks[e.options[e.selectedIndex].value]['ESSID'];
			document.getElementById('groupcipher').value=networks[e.options[e.selectedIndex].value]['GCIPHER'];
		}
	</script>

		<select name="choose_ssid" id="choose_ssid" onchange="setNetwork();">
		<?php

$i = 0;
if (isset($wlan->view->wlanNetworks) && isset($wlan->view->wlanNetworks[0])) {
    ?><option value=""><?php echo _('Choose network...'); ?></option><?php
    foreach ($wlan->view->wlanNetworks as $network) {
        ?>
					<option value="<?php echo $i++ ?>"><?php echo $network['ESSID']; ?></option>
		<?php
    
}
}
?>
	</select> <a href="?action=scanWlanNetworks"><?php echo _("start scan") ?></a> 
	<?php if(isset($wlan->view->wlanNetworks)) echo '<br /><i>'. _("Choose your network by clicking on it, enter your password and save the settings").'</i>'; ?>
	<br /> <input type="button"
			value="<?php echo _("Save all settings") ?>" name="save"
			onclick="document.getElementById('action').value='save';submit();" />

		<br />
		<br />
		<div id="wifiadvanced" style="max-height: 700px;">
			<h3><?php echo _('Automatic WiFi Setup with WPS') ?></h3>
			<div>
				<p>
		    	<?php echo _("Activate WPS on Boot") ?>: <input type="checkbox"
						id="wpsenabled" name="wpsenabled" value="1"
						<?php if($wlan->view->wpsenabled) echo 'checked'; ?> /> <br />
				<?php echo _("Wifi Protected Setup: Enable WPS in your Router and reboot Max2Play to automatically set up WiFi. Starts only, if no LAN-cable connected!") ?>
		    	<br /> <input type="button"
						value="<?php echo _("Save all settings") ?>" name="save"
						onclick="document.getElementById('action').value='save';submit();" />
					<br />
					<br />
		    	<?php echo _("Start WPS Setup now (requires activated WPS on your Router): ") ?>
		    	<br />
					<br /> <a class="button-small clickloading" href="#"
						onclick="document.getElementById('action').value='start_wps';$('#form1').submit();"><?php echo _("Start Automatic WiFi Setup") ?></a>
				</p>
			</div>
			<h3><?php echo _('Fixed LAN-IP Setup') ?></h3>
			<div>
				<p>
		    <?php echo _("Set fixed LAN IP-Address") ?> (<?php echo $wlan->view->fixedinterface ?>): <input
						type="checkbox" id="lanipfix" name="lanipfix" value="1"
						<?php if($wlan->view->fixedip) echo 'checked'; ?> /> <?php echo _("Current IP-Address") ?>: <input
						type="text" id="lanip" name="lanip"
						value="<?php echo $wlan->view->lanip ?>" /> <br />
					<br /><?php echo _("(Useful, if your router sometimes changes the IP-Address of you Max2Play-device and you need a fixed IP-Address.)") ?>
		    <br />
					<br /> <input type="button"
						value="<?php echo _("Save all settings") ?>" name="save"
						onclick="document.getElementById('action').value='save';submit();" />

				</p>
			</div>
			<h3><?php echo _('Accesspoint Mode on missing network connection') ?></h3>
			<div>
				<p>
		    <?php echo _("Activate to automatically start Accesspoint Mode during boot up, if no WiFi or LAN connection is available") ?> : <input
						type="checkbox" id="auto_accesspoint_mode"
						name="auto_accesspoint_mode" value="1"
						<?php if($wlan->view->auto_accesspoint_mode) echo 'checked'; ?> />
					<br />
					<br /><?php echo _("This enables you to configure WiFi from your Smartphone - no need for any LAN-Connection. Useful, if you don't want to connect the Max2Play-device with a network cable for initial setup of WiFi.") ?>
		    <br />
					<br /><?php echo _("<b>HowTo</b> (assuming no user changes on Accesspoint Plugin page): Plug-In Power cable -> wait for 2 minutes -> choose <i>max2play</i> as WiFi network on your smartphone -> enter passphrase <i>mypasscode</i> -> point your browser to http://max2play and configure WiFi as normally"); ?>
		    <br />
					<br /> <input type="button"
						value="<?php echo _("Save all settings") ?>" name="save"
						onclick="document.getElementById('action').value='save';submit();" />
				</p>
			</div>
			<h3><?php echo _('Configure LAN Mac-Address') ?></h3>
			<div>
				<p>
		    <?php echo _("This may be neccessary if you have a Minicomputer without a real hardware MAC-Address (some ODROIDs). This function is not available for Raspberry Pis.") ?>
		    <br />
		    <?php echo _("Configure LAN Mac-Address") ?>: <input type="text"
						id="lanmac" name="lanmac"
						value="<?php echo $wlan->view->lanmac ?>" /> <br />
					<br /><?php echo _("(Change only if there are more Max2Play-Devices in one network)") ?>
			<br />
					<br /> <input type="button"
						value="<?php echo _("Save all settings") ?>" name="save"
						onclick="document.getElementById('action').value='save';submit();" />
				</p>
			</div>
		</div>

		<script>
	  $(function() {
	    $( "#wifiadvanced" ).accordion({
	      heightStyle: "content",	     
	      collapsible: true 
	    });   
	  });  
 	 </script>

	</form>

	<br />
	<br />
	<?php echo _("WLAN INFO DESCRIPTION") ?>
	 
	<br />
	<br />
	<?php echo _("DEBUG Info") ?>:<br />
	<textarea rows="5" cols="80" readonly><?php echo $wlan->view->ifconfig_txt."\n\n### WLAN ###\n".$wlan->view->wlan_debug ?></textarea>
</div>
