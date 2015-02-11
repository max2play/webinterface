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
	<?php if(isset($wlan->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $wlan->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
		
	<b><?php echo _("Status") ?>:</b> 
		<?php echo str_replace('$STATUS', ($wlan->view->wlan_active) ? _('active') : _('not active'), _('WiFi USB connection status is <b>$STATUS</b>')) ?> 
		<?php echo _("(Only WPA/WPA2-Encryption possible)") ?>
		<br /><br />
	<table>
	<tr><td><?php echo _("Load WiFi Interface") ?>:</td> 
		<td><input type="checkbox" id="wlan_configured" name="wlan_configured" <?php if($wlan->view->wlan_configured == true) echo 'checked'; ?> value="1" /></td>
	</tr>
	<tr><td><?php echo _("Network SSID") ?>:</td> 
		<td><input type="text" id="ssid" name="ssid" value="<?php echo $wlan->view->ssid ?>" /></td>
	</tr>
	<tr>	
		<td><?php echo _("Password") ?>:</td>
		<td><input type="text" id="psk" name="psk" value="<?php echo $wlan->view->webpsk ?>" /></td>
	</tr>
	<?php /*<tr>
		<td><?php echo _("Group Cipher") ?>:</td>
		<td><input type="text" id="groupcipher" name="groupcipher" value="<?php echo $wlan->view->groupcipher ?>" /></td>
	</tr>*/	?>
	</table>

	<br /><br />
	<?php echo _("List of available networks") ?>: 
	
	<script type="text/javascript">
		var networks = new Array();
		
		<?php $i=0;
			if(isset($wlan->view->wlanNetworks)) 
				foreach($wlan->view->wlanNetworks as $network) {?>					
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
		<?php $i=0; 
			if(isset($wlan->view->wlanNetworks) && isset($wlan->view->wlanNetworks[0])) {
				?><option value=""><?php echo _('Choose network...'); ?></option><?php 
				foreach($wlan->view->wlanNetworks as $network) {?>
					<option value="<?php echo $i++ ?>"><?php echo $network['ESSID']; ?></option>
		<?php }
			} ?>
	</select>	
	
	<a href="?action=scanWlanNetworks"><?php echo _("start scan") ?></a> 
	<?php if(isset($wlan->view->wlanNetworks)) echo '<br /><i>'. _("Choose your network by clicking on it, enter your password and save the settings").'</i>'; ?>
	<br />
		
	<br /><br />
	<?php echo _("Configure LAN Mac-Address") ?>: <input type="text" id="lanmac" name="lanmac" value="<?php echo $wlan->view->lanmac ?>" />
	<br /><?php echo _("(Change only if there are more Max2Play-Devices in one network)") ?>	
	
	<br /><br />	
	
	<input type="button" value="<?php echo _("Save all settings") ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />		
	
	</form>
	
	<br /><br />
	<?php echo _("WLAN INFO DESCRIPTION") ?>
	 
	<br /><br />
	<?php echo _("DEBUG Info") ?>:
	<textarea rows="5" cols="80" readonly><?php echo $wlan->view->ifconfig_txt ?></textarea>
</div>	
															