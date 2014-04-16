<?php
/**
	 XBMC Settings View File
	
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
	XBMC - Status, Start & Stop
</h1>
	
<div class="entry-content">
	<?php if($sp->view->message[0]) echo implode('<br />', $sp->view->message).'<br /><br />'; ?>	
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
	<?php if ($sp->view->pid) { ?>
		<b>Status:</b> XBMC läuft aktuell unter der ProzessID <b><?php echo $sp->view->pid ?></b><br />
		<input type="button" value="XBMC stoppen" name="stop" onclick="document.getElementById('action').value='stop';submit();" />		
	<?php }else { ?>
		<b>Status:</b> XBMC läuft nicht<br /><br />
		<input type="button" value="XBMC starten" name="start" onclick="document.getElementById('action').value='start';submit();" />
	
	<?php } ?>
		<br /><br />
		<b>Autostart XBMC:</b><sup>#1</sup><br />
		<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostart" />
		<input type="button" value="speichern" name="save" onclick="document.getElementById('action').value='save';submit();" />
	</form>
	
	<br /><br />
	XBMC ist ein Media-Center für Videos, Musik und mehr.<br /><br />
	<sup>#1</sup>Autostart XBMC: wenn aktiviert, wird XBMC automatisch beim Start von Max2Play gestartet. Dies empfiehlt sich bei der Nutzung als Videoplayer. 
	Wenn Max2Play nur für Musik genutzt wird kann auf den Start von XBMC verzichtet werden. Dadurch verbraucht Max2Play weniger Resourcen und startet schneller.
	<br /><br />
	<b>Hinweise:</b><br />
	Wenn XBMC läuft wird Squeezelite automatisch deaktiviert! Anders ist es derzeit nicht möglich Sound in XBMC abzuspielen, da XBMC Pulseaudio nutzt und Squeezelite 
	sowie Shairplay Alsa nutzen um gleichzeitig auf das Soundinterface zuzugreifen.
	Sobald XBMC beendet wird, startet Squeezelite automatisch wieder.  
</div>	
															