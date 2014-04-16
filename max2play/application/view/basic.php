<?php
/**
	 Basic Settings View File
	
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
	<?php echo _("Basic settings for Max2Play") ?>
</h1>
	
<div class="entry-content">
	<?php if(isset($basic->view->message[0])) echo implode('<br />', $basic->view->message).'<br /><br />'; ?>
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
		
	<table class="settings">
	  <tr>
		<td><?php echo _("Playername") ?></td>
		<td><input type="text" id="playername" name="playername" value="<?php echo $basic->view->playername; ?>" /></td>
		<td><?php echo _("URL for this Web-administration / name of Squeezelite player") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _("Display Resolution") ?></td> 
		<td><select name="displayResolution">
			<?php foreach($basic->view->displayResolutions as $res) { ?><option value="<?php echo $res?>" <?php if($basic->view->currentResolution == $res) echo 'selected'; ?>><?php echo $res?></option><?php } ?>
			</select>
		</td>
		<td><?php echo _("With this resolution runs desktop and XBMC") ?></td>
	</table>
	<br />
	<input type="button" value="<?php echo _("Save Settings") ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	<br /><br />
	<b><?php echo _("Reboot Max2Play") ?></b><br />
	<input type="button" value="Reboot" name="reboot" onclick="document.getElementById('action').value='reboot';submit();" />
	
	<br /><br />
	<b><?php echo _("Reset configs to Max2Play delivery configuration") ?></b><br />
	<input type="button" value="<?php echo _("Reset configs - WARNING: deletes all changes") ?>" name="reset" onclick="document.getElementById('action').value='reset';submit();" /><br />
	<?php echo _("Settings for WiFi, autostart, playername, filesystem mounts will be overwritten.") ?>
	</form>
	
	<br /><br />
	<?php echo _("DEBUG Info") ?>:
	<textarea rows="5" cols="80" readonly></textarea>
</div>	
															