<?php
/**
	 Squeezeserver View File
	
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
	Squeezeserver - Status, Start & Stop
</h1>
	
<div class="entry-content">
	<?php if(isset($sp->view->message[0])) echo implode('<br />', $sp->view->message).'<br /><br />'; ?>
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
	<?php if ($sp->view->pid) { ?>
		<b>Status:</b> Squeezeserver (Logitech Media Server) läuft aktuell unter der ProzessID <b><?php echo $sp->view->pid ?></b><br />
		<a target="_blank" href="http://<?php echo preg_replace('=:.*=','', $_SERVER['HTTP_HOST']).':9000'; ?>" >Squeezeserver Webadministration starten</a><br /><br />
		<input type="button" value="Squeezeserver stoppen" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
		<input type="button" value="Squeezeserver killen" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
	<?php }else { ?>
		<b>Status:</b> Squeezeserver läuft nicht<br /><br />
		<input type="button" value="Squeezeserver starten" name="start" onclick="document.getElementById('action').value='start';submit();" />
		
	<?php } ?>
	
		<br /><br />
		<b>Autostart Squeezebox Server:</b><br />
		<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostart" />
		<input type="button" value="speichern" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	</form>
	
	<br /><br />
	Squeezeserver (Logitech Media Server) ist der Server für die Squeezebox und ist für die Steuerung der Player zuständig. Im Netzwerk muss mindestens ein solcher Server laufen.  
</div>	
															