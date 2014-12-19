<?php
/**
	 Homematic Anwesenheit über Fritzbox View File
	
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
	<?php echo _("Homematic und Hausautomatisierung Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($homematic->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $homematic->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("Anwesenheitsüberwachung mittels FritzBox und Homematic Variablen") ?></b></p>
	Über die Fritzbox lässt sich der Status einzelner Geräte im WLAN ermittlen. So kann bei Anwesenheit (Smartphone angemeldet) eine Variable im Homematic-System gesetzt werden.
	Dieses Script simuliert eine Nutzeranmeldung an der Fritzbox, liest den Status der angegebenen Geräte aus und setzt die korrespondierenden Variablen in Homematic. Voraussetzung ist ein Installierter Cuxd-Dienst in der Homematic-Zentrale.
	<br /> 
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />				
		
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _("Autostart") ?></td>
				<td><input type="checkbox" name="config[autostart]" value="1" <?php if($homematic->view->autostart > 0) echo "checked" ?> /></td>
				<td><?php echo _("Diesen Dienst immer starten") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _("FritzBox IP / Name") ?></td>
				<td><input type="text" name="config[ip]" value="<?php echo $homematic->config->ip ?>" /></td>
				<td><?php echo _("Meistens ist das fritz.box") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _("FritzBox Benutzer") ?></td>
				<td><input type="text" name="config[user]" value="<?php echo $homematic->config->user ?>" /></td>
				<td><?php echo _("Falls Benutzer in der Fritzbox eingerichtet sind, muss hier der Benutzername eingetragen werden") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _("FritzBox Passwort") ?></td>
				<td><input type="text" name="config[secret]" value="<?php echo $homematic->config->secret ?>" /></td>
				<td><?php echo _("Das Benutzerpasswort für den FritzBox-Nutzer oder das allgemeine Passwort für das Weblogin") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _("FritzBox Geräte") ?></td>
				<td><input type="text" name="config[device_list]" value="<?php echo $homematic->config->device_list ?>" /></td>
				<td><?php echo _("Die Geräte, die überwacht werden sollen - getrennt durch Leerzeichen") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _("Homematic Variables") ?></td>
				<td><input type="text" name="config[hmvar]" value="<?php echo $homematic->config->hmvar ?>" /></td>
				<td><?php echo _("Die korrespondierenden Homematic Variablen für die angegebenen Fritzbox Geräte") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _("FritzBox Looptime") ?></td>
				<td><input type="text" name="config[looptime]" value="<?php echo $homematic->config->looptime ?>" /></td>
				<td><?php echo _("Intervall für Statusupdates") ?></td> 
			  </tr>	 
			</table>
			
			<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save';submit();" value="<?php echo _("save") ?>" /></td>		
		</div>
		
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($homematic->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	