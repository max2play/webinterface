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

<?php include_once('../application/controller/Wlan.php') ?>

<?php include_once('header.php') ?>	

<h1 class="entry-header">
	WLAN & LAN Konfiguration
</h1>
	
<div class="entry-content">
	<?php if($wlan->view->message[0]) echo implode('<br />', $wlan->view->message).'<br /><br />'; ?>
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
		
	<b>Status:</b> WLAN USB-Stick ist <b><?php if($wlan->view->wlan_active) echo "aktiv"; else echo "nicht aktiv";?></b> (Nur WPA/WPA2-Verschlüsselung möglich)<br /><br />
	<table>
	<tr><td>WLAN Interface laden:</td> 
		<td><input type="checkbox" id="wlan_configured" name="wlan_configured" <?php if($wlan->view->wlan_configured == true) echo 'checked'; ?> value="1" /></td>
	</tr>
	<tr><td>Netzwerkname:</td> 
		<td><input type="text" id="ssid" name="ssid" value="<?php echo $wlan->view->ssid ?>" /></td>
	</tr>
	<tr>	
		<td>Netzwerkschlüssel:</td>
		<td><input type="text" id="psk" name="psk" value="<?php echo $wlan->view->webpsk ?>" /></td>
	</tr>
	<tr>
		<td>Group Cipher:</td>
		<td><input type="text" id="groupcipher" name="groupcipher" value="<?php echo $wlan->view->groupcipher ?>" /></td>
	</tr>	
	</table>

	<br /><br />
	Netzwerkliste: 
	
	<script type="text/javascript">
		var networks = new Array();
		
		<?php $i=0;
			if($wlan->view->wlanNetworks) 
				foreach($wlan->view->wlanNetworks as $network) {?>					
					networks[<?php echo $i;?>] = new Array();
					networks[<?php echo $i;?>]["ESSID"] = "<?php echo $network['ESSID']; ?>";
					networks[<?php echo $i++;?>]["GCIPHER"] = "<?php echo $network['GCIPHER']; ?>";
		<?php } ?>
	</script>	
	
	<select name="choose_ssid">
		<?php $i=0; 
			if($wlan->view->wlanNetworks) 
				foreach($wlan->view->wlanNetworks as $network) {?>
					<option value="<?php echo $i++ ?>" onclick="document.getElementById('ssid').value=networks[this.value]['ESSID'];document.getElementById('groupcipher').value=networks[this.value]['GCIPHER'];"><?php echo $network['ESSID']; ?></option>
		<?php } ?>
	</select>	
	
	<a href="?action=scanWlanNetworks">Scan starten</a> 
	<?php if($wlan->view->wlanNetworks) echo '<br /><i>Gewünschtes Netzwerk anklicken, Passwort eingeben und Einstellungen speichern.</i>'; ?>
	<br />
		
	<br /><br />
	LAN-Konfiguration MAC-Adresse: <input type="text" id="lanmac" name="lanmac" value="<?php echo $wlan->view->lanmac ?>" />
	<br />(Nur ändern bei mehreren Playern im Netzwerk)	
	
	<br /><br />	
	
	<input type="button" value="Alle Einstellung speichern" name="save" onclick="document.getElementById('action').value='save';submit();" />		
	
	</form>
	
	<br /><br />
	Info: bei Änderung der Netzwerkparameter bitte das Gerät Neustarten! Bei Betrieb mehrerer Max2Play-Geräte muss die MAC-Adresse für das LAN auf den Geräten jeweils unterschiedlich sein.
	<br />Bei dem Netzwerknamen Groß/Kleinschreibung beachten.	
	 
	<br /><br />
	DEBUG Info:
	<textarea rows="5" cols="80" readonly><?php echo $wlan->view->ifconfig_txt ?></textarea>
</div>	
															
<?php include_once('footer.php') ?>	