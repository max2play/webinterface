<?php
/**
	 Advanced_Max2play_Setup View File
	
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
	<?php echo _("Advanced Max2Play Setup for Odroid U3") ?>
</h1>
<div class="entry-content">
	<?php if(isset($as->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $as->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>		
	
	<?php echo _("This is the Advanced Setup Page. You may configure additional settings like the blinking LED, the behavior of the Power Button and more on this page.") ?>
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
	
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-top:3em;">
				<span class="ui-icon ui-icon-lightbulb" style="float:left; margin:-2px 5px 0 0;"></span>
				<?php echo _('Disable LED-Blink') ?>
		</p>
		<input type="button" id="disableLEDBlink" name="disableLEDBlink" onclick="document.getElementById('action').value='disableLEDBlink';submit();" value="<?php echo _("Disable LED-Blink") ?>" />
		<br /><?php echo _("Set LED Blink of ODROID to 10 secondes - Press Button again to set LED-Blink back to original settings.") ?>	
				
		<br />
		
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-top:3em;">
				<span class="ui-icon ui-icon-power" style="float:left; margin:-2px 5px 0 0;"></span>
				<?php echo _('Power Button Settings') ?>
		</p>
		<input type="submit" onclick="document.getElementById('powerbutton').style.display='';return false;" value="<?php echo _('Edit Power Button Options') ?>" />
			<div id="powerbutton" class="optiondetails" style="display:none;">
				<?php echo _('You can adopt the event that should happen when the Power Button is pressed (separating short and long presses)') ?>
				<table>
					<tr>
						<td><?php echo _('Short Press (less than 1 second)') ?></td>					
						<td style="width: 25%;"><select name="powerbuttonshort" style="width: 90%;">
							<option <?php if($as->powerbutton['shortpress'] == 'shutdown') echo 'selected'; ?> value='shutdown'><?php echo _('Poweroff Max2Play') ?></option>
							<option <?php if($as->powerbutton['shortpress'] == 'xbmc') echo 'selected'; ?> value='xbmc'><?php echo _('Toggle XBMC / Squeezelite') ?></option>
							<option <?php if($as->powerbutton['shortpress'] == 'myscript') echo 'selected'; ?> value='myscript'><?php echo _('Start my own Script') ?></option>
							</select>
						</td>
						<td><?php echo _('Default: Shutdown the device. If you use XBMC and Squeezelite you can use the Power Button to stop XBMC and start Squeezelite and vice versa. You may even set your own script that should start on pressed Power Button.') ?></td>
					</tr>
					<tr>
						<td><?php echo _('Shortpress Script') ?></td>					
						<td><input style="width: 90%;" type="text" name="powerbuttonshort_script" value="<?php echo htmlspecialchars($as->powerbutton['short_script']) ?>" />
						</td>
						<td><?php echo _('Set up the script that should be started. For Short Press the value must be Start my own Script.') ?></td>
					</tr>
					<tr>
						<td><?php echo _('Long Press (between 1 and 9 seconds)') ?></td>					
						<td style="width: 25%;"><select name="powerbuttonlong" style="width: 90%;">
							<option <?php if($as->powerbutton['longpress'] == 'myscript') echo 'selected'; ?> value='myscript'><?php echo _('Start my own Script') ?></option>
							</select>
						</td>
						<td><?php echo _('Default: No Action. You may set your own script that should start on pressed Power Button.') ?></td>
					</tr>
					<tr>
						<td><?php echo _('Longpress Script') ?></td>					
						<td><input style="width: 90%;" type="text" name="powerbuttonlong_script" value="<?php echo htmlspecialchars($as->powerbutton['long_script']) ?>" />
						</td>
						<td><?php echo _('Set up the script that should be started. For Long Press the value must be Start my own Script.') ?></td>
					</tr>
				</table>
				<input type="button" name="save" value="<?php echo _('save') ?>" onclick="document.getElementById('action').value='configurePowerButton';submit();" />
			</div>
	
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-top:3em;">
				<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
				<?php echo _("Update APT-GET Sources and APT-GET UPGRADE") ?>
		</p>		
		<input type="button" id="checkUpgrade" name="checkUpgrade" onclick="document.getElementById('action').value='checkUpgrade';submit();" value="<?php echo _("Check Available Upgrades") ?>" /><br />
		<input type="button" id="upgradeSystem" name="upgradeSystem" onclick="document.getElementById('action').value='upgradeSystem';submit();" value="<?php echo _("Upgrade Packages") ?>" /><br />		
		<?php echo _("Fetch all current Updates and upgrade packages. ONLY DO THIS IF YOU ALREADY HAVE THE LATEST MAX2PLAY-UBUNTU-IMAGE 14!") ?>
		
		
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-top:3em;">
				<span class="ui-icon ui-icon-video" style="float:left; margin:-2px 5px 0 0;"></span>
				<?php echo _("Install Flash for Desktop Chromium Browser - Enable Youtube/Vimeo Flashplayer") ?>
		</p>
		<input type="button" id="installFlash" name="installFlash" onclick="document.getElementById('action').value='installFlash';submit();" value="<?php echo _("Install Pepperflash") ?>" /><br />
		<?php echo _('After Installation you must open the Chromium URL "chrome:plugins" and set check [Always allowed] for Adobe Flash player. Then Restart Max2Play.') ?>
	</form>
	
	<br /><br />	
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Info") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($as->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	