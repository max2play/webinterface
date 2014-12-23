<?php
/**
	 Callbot View File
	 Start Calls from Max2Play via SIP (e.g. as fritzbox IP-Phone) to custom numbers with predefined MP3 or custom MP3
	 Good for real-time information on detected alarms
	 Start calls via http-Get Request
	
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
	<?php echo _("Callbot Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($callbot->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $callbot->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("Callbot - automatically submit voice messages to your mobile phone") ?></b></p>
	This plugin features an easy to use interface to set up and automatically start voice calls to your mobile phone. This enables you to get real-time alerts on alarm-detection and similar usage.
	<br />
	How this plugin works - Prerequisites:
	<ul>
		<li>Install and configure the callblocker plugin to get the SIP-functionality and the connection to your SIP-Provider</li>
		<li>Set up the messages that should be send to your mobile phone</li>
		<li>Start the calls by using the http-Request from your local network (e.g. homematic-CCU or home automatisation tools)</li>		
	</ul> 
	<br /> 
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />				
		
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _("Aktiviert") ?></td>
				<td><input type="checkbox" name="config[enabled]" value="1" <?php if($callbot->config->enabled > 0) echo "checked" ?> /></td>
				<td><?php echo _("Ausgehende Anrufe sind möglich über einfache URL-Aufrufe") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _("Telefonnummer") ?></td>
				<td><input type="text" name="config[phonenumber]" value="<?php echo $callbot->config->phonenumber ?>" /></td>
				<td><?php echo _("Welche Telefonnummer soll angerufen werden?") ?></td> 
			  </tr>
			  <tr>
				<td><?php echo _("Sprachansage aufnehmen / ändern") ?></td>
				<td><select name="wavfile-record">
						<option value="1">Ansage 1</option>
						<option value="2">Ansage 2</option>
					</select>
					<input type="button" id="record" name="<?php echo _("record") ?>" onclick="document.getElementById('action').value='record';submit();" value="<?php echo _("Starte Aufnahme") ?>" />
				</td>
				<td><?php echo _("Bei Übergabe des Parameters 'wavfile=1' wird die entsprechende Nachricht (in dem Fall die erste) abgespielt. Die Sprachnachricht wird als WAV-Datei gespeichert und hat eine maximale Länge von 10 Sekunden.") ?></td> 
			  </tr>	 
			 
			</table>			
			<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save';submit();" value="<?php echo _("save") ?>" />		
		</div>	
    <br /><br />
    Hier kannst du einen Testanruf an die oben gespeicherte Nummer starten. <br />
	<select name="wavfile">
		<option value="1">Ansage 1</option>
		<option value="2">Ansage 2</option>
	</select>
	<input type="button" id="startcall" name="<?php echo _("startcall") ?>" onclick="document.getElementById('action').value='startcall';submit();" value="<?php echo _("Starte Testanruf") ?>" />	
	<br /><br />
	Die URL zum Aufruf aus anderen Scripten und von anderen Computern im lokalen Netz lautet: http://<?php echo $_SERVER['HTTP_HOST']?>/plugins/callbot/controller/Setup.php?action=startcall&wavfile=1
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($callbot->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	