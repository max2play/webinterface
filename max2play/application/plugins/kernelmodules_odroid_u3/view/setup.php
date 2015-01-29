<?php
/**
	 Kernel Setup View File
	
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
	<?php echo _("Enable Kernelmodules for Odroid U3") ?>
</h1>
<div class="entry-content">
	<?php if(isset($kernelmodules_odroid_u3->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $kernelmodules_odroid_u3->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("Hiface USB-DAC: Status=").$kernelmodules_odroid_u3->view->hiface_enabled ?></b></p>	
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />			
			
		<input type="button" id="enableHifaceUSB" name="<?php echo _("Load Module to etc/rc.local") ?>" onclick="document.getElementById('action').value='enableHifaceUSB';submit();" value="<?php echo _("Load Module to etc/rc.local") ?>" />
		&nbsp;&nbsp;&nbsp;
		<input type="button" id="disableHifaceUSB" name="<?php echo _("Unload Module from etc/rc.local") ?>" onclick="document.getElementById('action').value='disableHifaceUSB';submit();" value="<?php echo _("Unload Module from etc/rc.local") ?>" />
		<br /><br />
		<?php echo _("If normal activation of Pre-Build Module does not work (mostly because of different Kernel-Version), please use this option to build the module with the latest Kernel-Sources. Takes ~20-30 minutes time! Make sure to install the latest kernel by using /usr/local/bin/odroid-utility.sh") ?> 
		<input type="button" id="buildHifaceWithKernel" name="<?php echo _("Build HiFace-Module with latest Kernel") ?>" onclick="document.getElementById('action').value='buildHifaceWithKernel';submit();" value="<?php echo _("Build HiFace-Module with latest Kernel") ?>" />
		
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($kernelmodules_odroid_u3->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	