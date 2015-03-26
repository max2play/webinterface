<?php
/**
	 Example Setup View File
	
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
	<?php echo _("Raspberry Settings") ?>
</h1>
<div class="entry-content">
	<?php if(isset($rs->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $rs->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />			
		
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Choose USB-Soundcard to load on boot (device tree overlay)") ?></b></p>
		<?php echo _t("Remember to change the audiodevice in your desired player (e.g. squeezelite / shairport) in tab audioplayer after enabling the card and rebooting.");?><br />
		<?php echo _t("Some audiocards like HiFi-Berry might not support simultanous usage of more than one audioplayer! Make sure to only assign one audioplayer at a time to that card.");?><br /><br />
		<?php echo _t("The following USB-DACs are supported. Just choose the one that should be enabled and click save.");?><br />		
		<select name="dtoverlay">			
		<?php foreach ($rs->usbSoundCards as $card => $name) { ?>
			<option value="<?php echo $card ?>" <?php if ($rs->view->dtoverlay == $card) echo 'selected'; ?>><?php echo $name ?></option>
		<?php } ?>			
		</select><br />
		<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save_dtoverlay';submit();" value="<?php echo _("save") ?>" />
		
		<br /><br />
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Overclocking and GPU-Memory") ?></b></p>
		<?php echo _t("To get the maximum performance out of your Raspberry PI A/B/B+ you may change the frequency and the amount of memory used for GPU (graphics).");?><br />
		
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _t("CPU Frequency") ?></td>
				<td>
					<select name="arm_freq">
					<?php foreach ($rs->armFrequency[$rs->info->chipset] as $value) { ?>
						<option value="<?php echo $value ?>" <?php if ($rs->view->arm_freq == $value) echo 'selected'; ?>><?php echo $value ?></option>
					<?php } ?>	
					</select>
				</td>
				<td><?php echo _t("More CPU-Power results in more heat. <a href='http://www.raspberrypi.org/documentation/configuration/config-txt.md' target='_blank'>More Information</a>") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _t("GPU-Memory") ?></td>
				<td><input type="text" id="gpu_mem" name="gpu_mem" value="<?php echo $rs->view->gpu_mem ?>" /></td>
				<td><?php echo _t("Default for Kodi/XBMC should be 128 MB. If you do not need any graphics, set it to 16 MB, which is the minimum. <a href='http://www.raspberrypi.org/documentation/configuration/config-txt.md' target='_blank'>More Information</a>"); ?></td> 
			  </tr>	 
			</table>
			<br />
			<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save_performance';submit();" value="<?php echo _("save") ?>" />
		</div>
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($rs->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	