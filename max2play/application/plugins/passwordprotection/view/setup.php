<?php
/**
	 Password Protection Setup View File
	
	 @Copyright 2015 Stefan Rick
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
	<?php echo _("Password Protection Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($pp->view->message[0]) || isset($pp->view->message[1])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br /><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>', $pp->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<form action="" method="post">
		<input type="hidden" id="action" name="action" value="" />
	
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _("Set up or remove password protection for Max2Play Browserinterface") ?></b></p>
		<?php echo _("Once installed, this addon asks users to enter username and password to open the Max2Play-Browserinterface. Make sure not to forget your user credentials after setting up the password protection. Otherwise you will not be able to change any settings!<br>You can easily remove the password protection again by clicking unistall.") ?>	
		<br />
		<br />
		<b><?php echo _("Protection is currently: ") ?><?php echo ($pp->view->installed === true) ? _('installed') : _('NOT installed')?></b>
		<br /><br />			
		
		<?php if ($pp->view->installed !== true){ ?>
			<table class="settings">
			  <tr>
				<td><?php echo _('Username') ?></td>
				<td>
					<input type="text" id="user" name="user" value="" />
				</td>				
			  </tr>
			   <tr>
				<td><?php echo _('Password for webinterface') ?></td>
				<td>
					<input type="text" id="password" name="password" value="" />
				</td>				
			  </tr>
			</table>
			<br />
			<input type="button" id="save" name="<?php echo _("install") ?>" onclick="document.getElementById('action').value='install';submit();" value="<?php echo _("install") ?>" />
			
		<?php }else { ?>		
			<input type="button" id="save" name="<?php echo _("uninstall") ?>" onclick="document.getElementById('action').value='uninstall';submit();" value="<?php echo _("uninstall") ?>" />					
		<?php }?>
		
		<br /><br />
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("Change root and user password for SSH-Login") ?></b></p>
		<?php echo _('Change Root Password') ?>: <input type="text" id="rootpassword" name="rootpassword" value="" /><br />
		<input type="button" id="savepasswordroot" name="<?php echo _("savepasswordroot") ?>" onclick="document.getElementById('action').value='savepasswordroot';submit();" value="<?php echo _("save password") ?>" />
		<br /><br />
		<?php echo _('Change User Password for user').' '.$pp->getSystemUser(); ?>: <input type="text" id="userpassword" name="userpassword" value="" /><br />
		<input type="button" id="savepassworduser" name="<?php echo _("savepassworduser") ?>" onclick="document.getElementById('action').value='savepassworduser';submit();" value="<?php echo _("save password") ?>" />
		
	<br /><br />
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("Further security settings") ?></b></p>
	<div class="optiondetails"> 
		<table class="settings">
		  <tr>
			<td><?php echo _("SSH Server") ?></td>
			<td>
				<?php if ($pp->ssh['pid']) { ?>
					<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', 'SSH', _('$SERVICENAME is running with processID')) ?> <b><?php echo $pp->ssh['pid'] ?></b>
					<input type="button" value="<?php echo str_replace('$SERVICENAME', 'SSH', _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stopSSH';submit();" />
					<input type="button" value="<?php echo str_replace('$SERVICENAME', 'SSH', _('kill $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='killSSH';submit();" />
				<?php }else { ?>
					<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', 'SSH', _('$SERVICENAME not running')) ?>
					<input type="button" value="<?php echo str_replace('$SERVICENAME', 'SSH', _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='startSSH';submit();" />	
				<?php } ?>
				<br /><br />
				<b><?php echo str_replace('$SERVICENAME', 'SSH', _('Autostart $SERVICENAME')) ?>:</b><br />
				<input type="checkbox" <?php if($pp->ssh['autostart']) echo "checked"; ?> value="1" name="autostartssh" />
				<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='saveSSH';submit();" />
			</td>
			<td><?php echo _("Enable / Disable SSH in Autostart. If you dont't need SSH (console login) you may just disable the service or only start it when needed.") ?></td> 
		  </tr>
	    </table>
	</div>
		
	</form>
	<br /><br />
	<?php echo _("Setting up password protection for webinterface and changing default login for SSH is a MUST HAVE, when your device can be reached from the internet. Anyway it is not recommended to make your device visible from the internet (e.g. portforwarding from router).") ?>
</div>	