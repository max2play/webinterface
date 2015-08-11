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
					<?php echo implode('<br /><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>', $basic->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	
	<form id="settings" action="" method="get">
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
		<td><?php echo _("Your eMail-Address / activation code") ?></td> 
		<td style="width:25%;"><input style="width:95%;" type="text" id="email" name="email" value="<?php echo $basic->view->email; ?>" /></td>
		<td><?php echo _("To access all functions and plugins of Max2Play and constantly get new features, you need to be a customer of shop.max2play.com or have a code for activation. This is verified with your eMail-address or activation code. If you are no customer yet, you may buy the <a href='http://shop.max2play.com/en/max2play-full-license.html' target='_blank'>Max2Play-Full-Package here</a>.") ?></td>
	  </tr>
	  <tr>
		<td><?php echo _("Language") ?></td> 
		<td><select name="locale" style="width:95%;">
			<?php foreach($basic->view->locales as $key => $res) { ?><option value="<?php echo $res?>" <?php if($basic->view->currentLocale == $res) echo 'selected'; ?>><?php echo $key?></option><?php } ?>
			</select><br />
			<select name="timezone" style="width:95%;">
			<?php foreach($basic->view->timezones as $res) { ?><option value="<?php echo $res?>" <?php if($basic->view->currentTimezone == $res) echo 'selected'; ?>><?php echo $res?></option><?php } ?>
			</select>
		</td>
		<td><?php echo _("Change Keyboard Layout, Language and Timezone for Desktop (this web-interface detects browser language)") ?></td>
	  </tr>
	  <tr>
		<td><?php echo _("Network Lookup") ?></td>
		<td><input type="checkbox" id="Max2PlayNetworkLookup" name="Max2PlayNetworkLookup" <?php if($basic->view->Max2PlayNetworkLookup) echo 'checked'; ?> value="1" /></td>
		<td><?php echo _("Search local Network for other Max2Play-Devices running Squeezelite and display them in the header of this interface") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _("Donate Button") ?></td>
		<td><input type="checkbox" id="removedonate" name="removedonate" <?php if($basic->view->removedonate == 1) echo 'checked'; ?> value="1" /></td>
		<td><?php echo _("Remove the donate-button in header of Max2Play-Webinterface") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _("Help on Sidebar") ?></td>
		<td><input type="checkbox" id="showhelponsidebar" name="showhelponsidebar" <?php if($basic->view->showHelpOnSidebar == true) echo 'checked'; ?> value="1" /></td>
		<td><?php echo _("Show help on sidebar for current page/addon, if the page supports it.") ?></td> 
	  </tr>
	</table>
	<br />
	<input type="button" value="<?php echo _("Save Settings") ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	<br /><br />
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-refresh" style="float:left; margin:-2px 5px 0 0;"></span>
		<b><?php echo _("Reboot / Update / Filesystem Settings") ?></b></p>
	
	<script type="text/javascript">
		function doReboot(){			
			window.open('http://<?php echo (strpos($_SERVER['SERVER_ADDR'], ':') !== FALSE) ? '['.$_SERVER['SERVER_ADDR'].']' : $_SERVER['SERVER_ADDR']; ?>/plugins/max2play_settings/controller/Basic.php?action=reboot', '_self');
			return true;
		}
	</script>
	<input type="button" value="<?php echo _("Reboot") ?>" name="reboot" onclick="doReboot();document.getElementById('action').value='reboot';" />&nbsp;&nbsp;
	<input type="button" value="<?php echo _("Shutdown") ?>" name="shutdown" onclick="document.getElementById('action').value='shutdown';submit();" />&nbsp;&nbsp;
	<input type="button" value="<?php echo _("Expand Filesystem") ?>" name="expandfs" onclick="document.getElementById('action').value='expandfs';submit();" />&nbsp;&nbsp;	
	<input type="button" value="<?php echo _("Update Max2Play") ?>" name=checkMax2PlayUpdate onclick="document.getElementById('action').value='checkMax2PlayUpdate';submit();" />&nbsp;&nbsp;	
	<br /><br />
	
	<?php if($basic->view->betaEnabled) { ?>
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-refresh" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("Force Update to latest Beta for Max2Play (Development)") ?></b></p>
		<p><?php echo _("READ CAREFULLY: This function is for developers and users that want to test the latest available Max2Play before it is released. This might have some bugs and will not be supported in forums and questions by mails!") ?><br />
		<input type="button" value="<?php echo _("Update2Beta Max2Play") ?>" name=checkMax2PlayBetaUpdate onclick="document.getElementById('action').value='checkMax2PlayBetaUpdate';submit();" />&nbsp;&nbsp;
		<br /><br />
	<?php } ?>	
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-arrowreturnthick-1-n" style="float:left; margin:-2px 5px 0 0;"></span>
		<b><?php echo _("Reset configs to Max2Play delivery configuration") ?></b></p>
	<input type="button" value="<?php echo _("Reset configs - WARNING: deletes all changes") ?>" name="reset" onclick="document.getElementById('action').value='reset';submit();" /><br />
	<?php echo _("Settings for WiFi, autostart, playername, filesystem mounts will be overwritten.") ?>	
	
	<br /><br />
	<p id="pluginconfigblock" class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<b><?php echo _("Plugin Configuration - Activate / Deactivate") ?></b></p>		
	<?php echo _('Activate Plugins by selecting and moving them to the "Active Plugins". You may change the order of the active Plugins in the navigation bar by selecting them and move up or down. Plugins that are not active will not show up in the navigation bar!') ?>
	<br style="margin-bottom:10px;" />
	<div style="float:left;"><b><?php echo _("Available Plugins"); ?></b><br />
		<select name="plugins" id="plugins" multiple size="10" style="float:left;">
		<?php foreach($basic->view->pluginselect as $ps) {
			if(!isset($ps['active']) || $ps['active'] == false) { ?>
				<option value="<?php echo $ps['name'] ?>"><?php echo _($ps['name']); ?></option>
		<?php }}?>
		</select>
	</div>
	
	<div style="float:left;">
		<input id="pluginadd" type="button" value=">" style="width:40px;margin:10px;margin-top:50px;" /><br />	
		<input id="pluginremove" type="button" value="<" style="width:40px;margin:10px;" /><br />
	</div>
	
	<div style="float:left;"><b><?php echo _("Active Plugins"); ?></b><br />
		<select name="activeplugin[]" id="activeplugin" multiple size="10" style="float:left;">
		<?php foreach($basic->view->pluginselect as $ps) { 
			if(isset($ps['active']) && $ps['active'] == true) { ?>
				<option value="<?php echo $ps['name'] ?>"><?php echo _($ps['name']); ?></option>
		<?php }}?>
		</select>
	</div>
	<div style="float:left;">
		<input id="btn-up" type="button" value="<?php echo _("Move Up"); ?>" style="margin:10px;margin-top:50px;" /><br />
	    <input id="btn-down" type="button" value="<?php echo _("Move Down");?>" style="margin:10px;" />
	</div>
	
	<br style="clear:both;" />
	<br />
	
	<?php echo _('Default Plugin that should be opened on start of Max2Play-Webinterface') ?>
	<br />
	<select name="defaultplugin">
	<?php foreach($basic->view->pluginselect as $ps) { ?>		
		<option value="<?php echo $ps['name'] ?>" <?php if(isset($ps['default']) && $ps['default'] == 1) echo 'selected'; ?>><?php echo _($ps['name']); ?></option>
	<?php }?>
	</select>
	
	
	<script type="text/javascript">
	function moveSelectedItems(source, destination){
	    var selected = $(source+' option:selected').remove();
	    var sorted = $.makeArray($(destination+' option').add(selected));//.sort(function(a,b){return $(a).text() > $(b).text() ? 1:-1;})
	    $(destination).empty().append(sorted);	    
	}
	</script>
	
	<script type="text/javascript">
	$(document).ready(function(){
	    $('#pluginadd').click(function(){
	        moveSelectedItems('#plugins', '#activeplugin');return false;
	    });	    
	    $('#pluginremove').click(function(){
	        moveSelectedItems('#activeplugin', '#plugins');return false;
	    });

	    $('#btn-up').bind('click', function() {
	        $('#activeplugin option:selected').each( function() {
	            var newPos = $('#activeplugin option').index(this) - 1;
	            if (newPos > -1) {
	                $('#activeplugin option').eq(newPos).before("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
	                $(this).remove();
	            }
	        });return false;
	    });
	    $('#btn-down').bind('click', function() {
	        var countOptions = $('#activeplugin option').size();
	        $('#activeplugin option:selected').each( function() {
	            var newPos = $('#activeplugin option').index(this) + 1;
	            if (newPos < countOptions) {
	                $('#activeplugin option').eq(newPos).after("<option value='"+$(this).val()+"' selected='selected'>"+$(this).text()+"</option>");
	                $(this).remove();
	            }
	        });return false;
	    });  
	});
	</script>
	
	<br /><br />
	<input type="button" value="<?php echo _("Save Plugin Config - WARNING: removes Navigation for all NOT Selected") ?>" name="pluginconfig" id="pluginconfig" 
		   onclick="document.getElementById('action').value='pluginconfig';$('#activeplugin option').prop('selected', 'true');submit();" /><br />	
	<br /><br />
	<?php echo _('You may install new Plugins by entering the http-link to the Pluginfile (zip/tar/tar.gz).<br /><a class="download-button" href="http://www.max2play.com/features/plugins/" target="_blank"> A list of Max2Play-Extensions can be found here</a>') ?>
	<br />
	<?php echo _('Enter http-URL')?> <input type="text" id="installplugin" name="installplugin" value="" />	
	<input type="button" value="<?php echo _("Install new Plugin") ?>" name="installplugin" onclick="document.getElementById('action').value='installplugin';submit();" /><br />	
	<br /><br />
	</form>
	
	<?php echo _("DEBUG Info") ?>:
	<textarea rows="5" cols="80" readonly><?php foreach ($basic->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?></textarea>
</div>
															