<?php
/**
	 Audioplayer View File
	
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

<?php include_once('../application/controller/Squeezeplayer.php') ?>
<?php include_once('../application/controller/Shairport.php') ?>		

<?php include_once('header.php') ?>		

<h1 class="entry-header">
	Mediaplayer Squeezelite & Airplay - Status, Start / Stop
</h1>
	
<div class="entry-content">
	<?php if($sp->view->message[0]) echo implode('<br />', $sp->view->message).'<br /><br />'; ?>
	<?php if($ap->view->message[0]) echo implode('<br />', $ap->view->message).'<br /><br />'; ?>
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
	<?php if ($sp->view->pid) { ?>
		<b>Status:</b> Squeezelite läuft aktuell unter der ProzessID <b><?php echo $sp->view->pid ?></b><br />
		<input type="button" value="Squeezelite stoppen" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
		<input type="button" value="Squeezelite killen" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
	<?php }else { ?>
		<b>Status:</b> Squeezelite läuft nicht<br /><br />
		<input type="button" value="Squeezelite starten" name="start" onclick="document.getElementById('action').value='start';submit();" />	
	<?php } ?>
	<br /><br /><b>Autostart Squeezelite:</b><br />
	<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostartsqueeze" />
	<input type="button" value="speichern" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	<br /><br /><br />
	
	<?php if ($ap->view->pid) { ?>
		<b>Status:</b> Shairport (Airplay) läuft aktuell unter der ProzessID <b><?php echo $ap->view->pid ?></b><br />
		<input type="button" value="Shairport stoppen" name="stopap" onclick="document.getElementById('action').value='stopap';submit();" />
		<input type="button" value="Shairport killen" name="killap" onclick="document.getElementById('action').value='killap';submit();" />
	<?php }else { ?>
		<b>Status:</b> Shairport läuft nicht<br /><br />
		<input type="button" value="Shairport starten" name="startap" onclick="document.getElementById('action').value='startap';submit();" />
		
	<?php } ?>
	<br /><br /><b>Autostart Shairport:</b><br />
	<input type="checkbox" <?php if($ap->view->autostart) echo "checked"; ?> value="1" name="autostartap" />
	<input type="button" value="speichern" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	</form>
	
	<br /><br /><br />
	Squeezelite ist ein Software Player für Squeezebox, der mit dem Logitech Media Server (Squeezebox Server) arbeitet und ähnlich wie ein Squeezebox Receiver 
	über den Server steuerbar ist.<br />
	Shairport ist ein Dienst der Apple Airplay ermöglicht.<br />
	Beide Dienste werden beim Starten des ODROID etwas verzögert geladen (max. 1 Minute), um Konflikte mit den Soundtreibern zu vermeiden.
</div>	
															
<?php include_once('footer.php') ?>	