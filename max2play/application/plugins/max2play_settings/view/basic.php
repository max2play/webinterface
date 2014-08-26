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
	
	<?php if(isset($basic->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $basic->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
		
	<table class="settings">
	  <tr>
		<td><?php echo _("Playername") ?></td>
		<td style="width:25%;"><input style="width:95%;" type="text" id="playername" name="playername" value="<?php echo $basic->view->playername; ?>" /></td>
		<td><?php echo _("URL for this Web-administration / name of Squeezelite player") ?></td> 
	  </tr>
	  <?php if (isset($basic->view->displayResolutions)) {?>
		  <tr>
			<td><?php echo _("Display Resolution") ?></td> 
			<td><select name="displayResolution">
				<?php foreach($basic->view->displayResolutions as $res) { ?><option value="<?php echo $res?>" <?php if($basic->view->currentResolution == $res) echo 'selected'; ?>><?php echo $res?></option><?php } ?>
				</select>
			</td>
			<td><?php echo _("With this resolution runs desktop and XBMC") ?></td>
		  </tr>
	  <?php } ?>
	  <tr>
		<td><?php echo _("Language") ?></td> 
		<td><select name="locale" style="width:95%;">
			<?php foreach($basic->view->locales as $key => $res) { ?><option value="<?php echo $key?>" <?php if($basic->view->currentLocale == $key) echo 'selected'; ?>><?php echo $key.' - '.$res?></option><?php } ?>
			</select>
		</td>
		<td><?php echo _("Change Keyboard Layout, Language and Timezone for Desktop (this web-interface detects browser language)") ?></td>
	  </tr>
	  <tr>
		<td><?php echo _("Network Lookup") ?></td>
		<td><input type="checkbox" id="Max2PlayNetworkLookup" name="Max2PlayNetworkLookup" <?php if($basic->view->Max2PlayNetworkLookup) echo 'checked'; ?> value="1" /></td>
		<td><?php echo _("Search local Network for other Max2Play-Devices running Squeezelite and display them in the header of this interface") ?></td> 
	  </tr>
	</table>
	<br />
	<input type="button" value="<?php echo _("Save Settings") ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	<br /><br />
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-refresh" style="float:left; margin:-2px 5px 0 0;"></span>
		<b><?php echo _("Reboot / Update / Filesystem Settings") ?></b></p>
	<input type="button" value="Reboot" name="reboot" onclick="document.getElementById('action').value='reboot';submit();" />&nbsp;&nbsp;
	<input type="button" value="<?php echo _("Expand Filesystem") ?>" name="expandfs" onclick="document.getElementById('action').value='expandfs';submit();" />&nbsp;&nbsp;
	<input type="button" value="<?php echo _("Fix USB-Mount") ?>" name="fixusbmount" onclick="document.getElementById('action').value='fixusbmount';submit();" />&nbsp;&nbsp;
	<input type="button" value="<?php echo _("Update Max2Play") ?>" name=checkMax2PlayUpdate onclick="document.getElementById('action').value='checkMax2PlayUpdate';submit();" />&nbsp;&nbsp;	
	
	<br /><br />
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-arrowreturnthick-1-n" style="float:left; margin:-2px 5px 0 0;"></span>
		<b><?php echo _("Reset configs to Max2Play delivery configuration") ?></b></p>
	<input type="button" value="<?php echo _("Reset configs - WARNING: deletes all changes") ?>" name="reset" onclick="document.getElementById('action').value='reset';submit();" /><br />
	<?php echo _("Settings for WiFi, autostart, playername, filesystem mounts will be overwritten.") ?>	
	
	<br /><br />
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<b><?php echo _("Plugin Configuration - Multiselect") ?></b></p>		
	<?php echo _('Activate Plugins by selecting them. Do multiple Selects by holding &quot;Strg&quot; or &quot;Shift&quot; and Mouseclick') ?>
	<br />
	<select name="plugins[]" multiple size="10">
	<?php foreach($basic->view->pluginselect as $ps) {?>
		<option value="<?php echo $ps['name'] ?>" <?php if($ps['active']) echo 'selected'; ?>><?php echo _($ps['name']); ?></option>
	<?php }?>
	</select>
	<br /><br />
	
	<?php echo _('Default Plugin that should be the first in the navigation') ?>
	<br />
	<select name="defaultplugin">
	<?php foreach($basic->view->pluginselect as $ps) { ?>		
		<option value="<?php echo $ps['name'] ?>" <?php if(isset($ps['default']) && $ps['default'] == 1) echo 'selected'; ?>><?php echo _($ps['name']); ?></option>
	<?php }?>
	</select>
	<br /><br />
	<input type="button" value="<?php echo _("Save Plugin Config - WARNING: removes Navigation for all NOT Selected") ?>" name="pluginconfig" onclick="document.getElementById('action').value='pluginconfig';submit();" /><br />	
	<br /><br />
	
	</form>
	
	<?php echo _("DEBUG Info") ?>:
	<textarea rows="5" cols="80" readonly><?php foreach ($basic->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?></textarea>
</div>	
															