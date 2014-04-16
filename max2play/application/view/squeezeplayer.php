<?php
/**
	 Audioplayer View File
	
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


<?php //zusätzlich die Shairport konfig laden
	  include_once('../application/controller/Shairport.php') ?>		

<h1 class="entry-header">
	<?php echo _("Mediaplayer Squeezelite & Airplay - Status, Start / Stop") ?>
</h1>
	
<div class="entry-content">
	<?php if(isset($sp->view->message[0])) echo implode('<br />', $sp->view->message).'<br /><br />'; ?>
	<?php if(isset($ap->view->message[0])) echo implode('<br />', $ap->view->message).'<br /><br />'; ?>
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		
		<?php if ($sp->view->pid) { ?>
			<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $sp->view->pid ?></b><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('kill $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
		<?php }else { ?>
			<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME not running')) ?><br /><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />	
		<?php } ?>
		<br /><br /><b><?php echo str_replace('$SERVICENAME', $sp->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
		<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostartsqueeze" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
		
		<br /><br /><br />
		
		<?php if ($ap->view->pid) { ?>
			<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $ap->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $ap->view->pid ?></b><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $ap->viewname, _('stop $SERVICENAME')) ?>" name="stopap" onclick="document.getElementById('action').value='stopap';submit();" />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $ap->viewname, _('kill $SERVICENAME')) ?>" name="killap" onclick="document.getElementById('action').value='killap';submit();" />
		<?php }else { ?>
			<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $ap->viewname, _('$SERVICENAME not running')) ?><br /><br />
			<input type="button" value="<?php echo str_replace('$SERVICENAME', $ap->viewname, _('start $SERVICENAME')) ?>" name="startap" onclick="document.getElementById('action').value='startap';submit();" />
			
		<?php } ?>
		<br /><br /><b><?php echo str_replace('$SERVICENAME', $ap->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
		<input type="checkbox" <?php if($ap->view->autostart) echo "checked"; ?> value="1" name="autostartap" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
	
	</form>
	
	<br /><br /><br />
	<?php echo _('SQUEEZEPLAYER INFO DESCRIPTION') ?>
</div>	
															