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
	<?php if(isset($pp->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br /><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>', $pp->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Set up or remove password protection for Max2Play Browserinterface") ?></b></p>
	<?php echo _t("Once installed, this addon asks users to enter username and password to open the Max2Play-Browserinterface. Make sure not to forget your user credentials after setting up the password protection. Otherwise you will not be able to change any settings!<br>You can easily remove the password protection again by clicking unistall.") ?>	
	<br />
	<br />
	<b><?php echo _t("Protection is currently: ") ?><?php echo ($pp->view->installed === true) ? _('installed') : _('NOT installed')?></b>
	
	<form action="" method="post">
		<input type="hidden" id="action" name="action" value="" />			
		
		<?php if ($pp->view->installed !== true){ ?>
			<?php echo _t('Username') ?>: <input type="text" id="user" name="user" value="" /><br />
			<?php echo _t('Password') ?>: <input type="text" id="password" name="password" value="" /><br />
			<br />
			<input type="button" id="save" name="<?php echo _("install") ?>" onclick="document.getElementById('action').value='install';submit();" value="<?php echo _("install") ?>" />
			
		<?php }else { ?>		
			<input type="button" id="save" name="<?php echo _("uninstall") ?>" onclick="document.getElementById('action').value='uninstall';submit();" value="<?php echo _("uninstall") ?>" />					
		<?php }?>
		
		<br /><br />
		TODO: root-Password, user-Password, disable SSH, ipTables Rules?
			
		
	</form>
	
</div>	