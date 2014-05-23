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
	
	<?php if ($sp->view->pid) { ?>
		<b><?php echo _('Status')?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $sp->view->pid ?></b><br /><br />
		<a target="_blank" href="http://<?php echo preg_replace('=:.*=','', $_SERVER['HTTP_HOST']).':9000'; ?>" ><?php echo _('Start Squeezeserver Webadministration')?></a><br /><br />
		<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
		<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('kill $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
	<?php }elseif($sp->view->installed == true) { ?>
		<b><?php echo _('Status')?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME not running')) ?><br /><br />
		<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />
		
	<?php }else { ?>				
			<b><?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?>:</b> 
			<input type="button" value="<?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?>" name="install" onclick="document.getElementById('action').value='install';submit();" />
			<br /><?php echo _('The installation takes about 5 to 10 minutes depending on your internet connection. At first it downloads the package from http://downloads.slimdevices.com/ and afterwards it installs the package. You may reload this page by clicking the button again to see the status of the install process.')?>	
	<?php } ?>
	
		<br /><br />
		<b><?php echo str_replace('$SERVICENAME', $sp->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
		<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostart" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	</form>
	
	<br /><br />
	<?php echo _('SQUEEZESERVER INFO DESCRIPTION') ?>
	  
</div>	
