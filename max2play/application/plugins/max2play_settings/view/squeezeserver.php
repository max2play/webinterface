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
	<?php echo _('Squeezeserver - Status, Start & Stop') ?>
</h1>
	
<div class="entry-content">
	<?php if(isset($sp->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $sp->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
	<?php if($sp->view->installed == true) { ?>
		<?php if ($sp->view->pid) { ?>
			<b><?php echo _('Status')?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $sp->view->pid ?></b><br /><br />
			<a target="_blank" href="http://<?php echo preg_replace('=:.*=','', $_SERVER['HTTP_HOST']).':9000'; ?>" ><?php echo _('Start Squeezeserver Webadministration')?></a><br /><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
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
	<?php } ?>				
							
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?></p>
	<select name="lmsversion" onclick="copytodownloadurl(this);">
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
	<input id="downloadurl" type="text" value="" name="downloadurl" style="" /><br /><br /> 
	
	<input type="button" value="<?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?>" name="install" onclick="document.getElementById('action').value='install';submit();" />
	<br /><br /> <?php echo _('The installation takes about 5 to 10 minutes depending on your internet connection. At first it downloads the package from http://downloads.slimdevices.com/ and afterwards it installs the package. You may reload this page by clicking the button again to see the status of the install process.')?>			
		
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
