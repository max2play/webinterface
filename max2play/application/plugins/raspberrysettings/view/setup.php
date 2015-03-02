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
			<b><?php echo _("Choose USB-Soundcard to load on boot (device tree overlay)") ?></b></p>
		<?php echo _("The following USB-DACs are supported. Just choose the one that should be enabled and click save.");?><br />
		<select name="dtoverlay">
			<option value=""><?php echo _("none") ?></option>
		<?php foreach ($rs->usbSoundCards as $card => $name) { ?>
			<option value="<?php echo $card ?>"><?php echo $name ?></option>
		<?php } ?>			
		</select><br />
		<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save_dtoverlay';submit();" value="<?php echo _("save") ?>" />
		
		<br /><br />
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("Overclocking and GPU-Memory") ?></b></p>
		<?php echo _("To get the maximum performance out of your Raspberry PI A/B/B+ you may change the frequency and the amount of memory used for GPU (graphics).");?><br />
		
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _("CPU Frequency") ?></td>
				<td>
					<select name="frequency">
						<option value="default">...</option>
						<option value="700">700 MHZ</option>
						<option value="800">800 MHZ</option>
					</select>
				</td>
				<td><?php echo _("More CPU-Power") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _("GPU-Memory") ?></td>
				<td><input type="text" id="gpumemory" name="gpumemory" value="<?php echo $rs->gpumemory ?>" /></td>
				<td><?php echo _("Default should be 128 MB. If you do not need any graphics, set it to 10 MB") ?></td> 
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