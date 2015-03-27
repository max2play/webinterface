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
	<?php echo _t("CCU-Historian für Homematic") ?>
</h1>
<div class="entry-content">
	<?php if(isset($ccuhistorian->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $ccuhistorian->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		
	<?php if(!$ccuhistorian->view->installed){ ?>
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Installiere CCU-Historian auf diesem Gerät") ?></b></p>
		<?php echo _t('Download-URL für Zipfile') ?>: 
		<input id="downloadurl" type="text" value="http://www.ccu-historian.de/uploads/CCU-Historian/ccu-historian-0.7.6hf1-bin.zip" name="downloadurl" /><br /><br /> 
		<input type="button" id="install" name="<?php echo _("install") ?>" onclick="document.getElementById('action').value='install';submit();" value="<?php echo _("install") ?>" />		
	<?php } else { ?>
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("CCU-Historian ist installiert") ?></b></p>
		
		<?php if ($ccuhistorian->view->pid) { ?>
			<b><?php echo _('Status')?>:</b> <?php echo str_replace('$SERVICENAME', 'CCU-Historian', _('$SERVICENAME is running with processID')) ?> <b><?php echo $ccuhistorian->view->pid ?></b><br /><br />
			<?php echo str_replace('$URL', 'http://'.$_SERVER['HTTP_HOST'].':'.$ccuhistorian->config->webserver_port, _t('Zugriff auf Weboberfläche von CCU-Historian über <a target="_blank" href="$URL">$URL</a>. Hinweis: Nach dem Start des Dienstes kann es bis zu 2 Minuten dauern bis die URL erreichbar ist.')); ?><br />
			<br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', 'CCU-Historian', _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />			
			<input type="button" value="<?php echo str_replace('$SERVICENAME', 'CCU-Historian', _('kill $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
		<?php }else { ?>		
			<b><?php echo _('Status')?>:</b> <?php echo str_replace('$SERVICENAME', 'CCU-Historian', _('$SERVICENAME not running')) ?><br /><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', 'CCU-Historian', _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />			
		<?php } ?>			
		
		<br /><br />
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Grundlegende Einstellungen für CCU-Historian") ?></b></p>
		Alle weitergehenden Einstellungen können über das Konfigurationsfile unter <i><?php echo $ccuhistorian->configfile ?></i> vorgenommen werden. Für den einfachen Zugriff auf das Konfigurationfile kann z.B. eine Freigabe mit Samba (Reiter Dateisystem Mount) erstellt werden.
		<br />
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _t("Autostart") ?></td>
				<td><input type="checkbox" name="config[autostart]" value="1" <?php if($ccuhistorian->view->autostart > 0) echo "checked" ?> /></td>
				<td><?php echo _t("Diesen Dienst beim Bootvorgang automatisch starten") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _t("Port für Webserver von CCU-Historian") ?></td>
				<td><input type="text" name="config[webserver_port]" value="<?php echo $ccuhistorian->config->webserver_port ?>" /></td>
				<td><?php echo _t("Auf diesem Port läuft der Dienst. Da Port 80 schon vo Max2Play belegt ist, sollte eine höhere Nummer verwendet werden.") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _t("Device Type") ?></td>
				<td><input type="text" name="config[devices_device1_type]" value="<?php echo $ccuhistorian->config->devices_device1_type ?>" /></td>
				<td><?php echo _t("CCU1 oder CCU2") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _t("Device Adresse") ?></td>
				<td><input type="text" name="config[devices_device1_address]" value="<?php echo $ccuhistorian->config->devices_device1_address ?>" /></td>
				<td><?php echo _t("IP Adresse oder Netzwerknamen der Homematic Zentrale. ACHTUNG die Single-Quotes (') müssen erhalten bleiben!") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _t("Plugin Type") ?></td>
				<td><input type="text" name="config[devices_device1_plugin1_type]" value="<?php echo $ccuhistorian->config->devices_device1_plugin1_type ?>" /></td>
				<td><?php echo _t("CUXD oder HMWLGW (bei LAN-Adapter)") ?></td> 
			  </tr>	
			</table>
			
			<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save';submit();" value="<?php echo _("save") ?>" />	
		</div>
		
	</form>

	<?php }?>
	
	<br /><br />
	Info zum Projekt CCU-Historian: <a href="http://www.ccu-historian.de">http://www.ccu-historian.de</a>
	<br /><br />
	
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($ccuhistorian->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>