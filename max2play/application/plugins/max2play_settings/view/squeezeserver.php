<?php
/**
	 Squeezebox Server View File
	
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
	<?php echo $sp->viewname.' - '._('Status, Start & Stop') ?>
</h1>
	
<div class="entry-content">
	<?php $message=$sp->view->message;
    	  $error=$sp->view->error; 
    	  include(APPLICATION_PATH.'/view/messages.php');?>
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
	<?php if($sp->view->installed == true) { ?>
		<?php if ($sp->view->pid) { ?>
			<b><?php echo _('Status')?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $sp->view->pid ?></b><br /><br />
			<?php //Build correct URL to Squeezeboxserver (no ipv6 possible) ?>
			<a target="_blank" href="//<?php echo $sp->getServerUrl($ipv4=true).':9000'; ?>" class="download-button" style="width:70%;"><?php echo _('Open Squeezebox Server Webadministration')?></a><br /><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('restart $SERVICENAME')) ?>" name="restart" onclick="document.getElementById('action').value='restart';submit();" />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('kill $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
		<?php }else { ?>		
			<b><?php echo _('Status')?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME not running')) ?><br /><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />			
		<?php } ?>
		<br /><br />
		<b><?php echo str_replace('$SERVICENAME', $sp->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
		<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostart" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
		<br /><br />		
		<b><?php echo _('Run Squeezebox Server without www.mysqueezebox.com') ?>:</b><br />
		<input type="checkbox" <?php if($sp->view->nomysqueezebox) echo "checked"; ?> value="1" name="nomysqueezebox" /> <?php echo _('Disable services from www.mysqueezebox.com and render thumbnail images locally.') ?><br />
		<br /><br /><b><?php echo _('Delay Startup of Squeezebox Server on Boot') ?>:</b><br />
		<input type="text" size="4" <?php if($sp->view->delayedStartup) echo 'value="'.$sp->view->delayedStartup.'"'; ?> name="delayedstartup" /> <?php echo _('Delay time in seconds. If Radiolists are not working your Squeezebox Server might have discovered no internet connection on boot. Delay the start by 30 seconds to solve this problem.') ?><br />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save_nomysqueezebox';submit();" />
		<br /><br />
	<?php } ?>				
							
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?></p>
	<select id="lmsversion" name="lmsversion" onclick="copytodownloadurl(this);">
	<?php foreach($sp->lmsversions as $key => $value) { ?>
		<option value="<?php echo $value ?>"><?php echo $key ?></option>
	<?php } ?>
	</select>
	<script type="text/javascript">
		function copytodownloadurl(item){
			document.getElementById("downloadurl").value = item.options[item.selectedIndex].value;
			document.getElementById("downloadurl").style.width = "500px";
		}			
	</script>
	<input type="button" value="<?php echo _('Show available Versions') ?>" name="fetchLMS" onclick="document.getElementById('action').value='showavailablelms';submit();" /><br /><br />
	 
	<?php echo _('Alternative other source from Slimdevices (package must be .deb)') ?>: 
	<input id="downloadurl" type="text" value="<?php if($lmslink = reset($sp->lmsversions)){ echo $lmslink; } ?>" name="downloadurl" style="" /><br /><br /> 		
	
	<?php if ($sp->view->perlversion > "5.18" ) echo '<br /><span style="color:red;">'._('IMPORTANT: You must choose 7.9 Nightly as 7.8 is currently not supported for this version of Perl: '.$sp->view->perlversion).'</span><br />'; ?>
	
	<input type="button" value="<?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?>" name="install" onclick="document.getElementById('action').value='install';submit();" />
	<br /><br /> <?php echo _('The installation takes about 5 to 10 minutes depending on your internet connection. At first it downloads the package from http://downloads.slimdevices.com/ and afterwards it installs the package. You may reload this page by clicking the button again to see the status of the install process.')?>			
	
	<br /><br />
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<?php echo _('Install special Plugins for Squeezebox Server') ?></p>
	<select name="lmsplugin" style="width: 90%;">	
		<option value="shairtunes2"><?php echo _('Install Shairtunes2 (NEW) Plugin (Optimized version for ODROID and Raspberry by disaster123 - All connected Squeezeplayers can be used as Airplay Devices)') ?></option>
		<option value="shairtunes"><?php echo _('Install Shairtunes Plugin (All connected Squeezeplayers can be used as Airplay Devices)') ?></option>
		<option value="googlemusic"><?php echo _('Install GoogleMusic Plugin (Use your GoogleMusic Account and connect it with your Squeezebox Server)') ?></option>				
	</select>
	<input type="button" value="<?php echo _('Install choosen Plugin (takes some time)') ?>" name="plugininstall" onclick="document.getElementById('action').value='plugininstall';submit();" />
	
	</form>
	
	<br /><br />
	<?php echo _('SQUEEZESERVER INFO DESCRIPTION') ?>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;">DEBUG Informationen</a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($sp->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	
