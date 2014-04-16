<?php
/**
	 Filesystem Settings View File
	
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
	<?php echo _("Filesystem - mount network paths (e.g. NFS) ") ?>
</h1>
	
<div class="entry-content">
	<?php if(isset($fs->view->message[0])) foreach($fs->view->message as $message){ echo $message; echo '<br /><br />';} ?>	
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />	
		
		<table>
			<tr>
				<td><?php echo _('Mountpoint') ?></td>
				<td><?php echo _('Path') ?></td>
				<td><?php echo _('Type') ?></td>
				<td><?php echo _('Options') ?></td>
				<td><?php echo _('Action') ?></td>
			</tr>
		<?php $i=0;
			 foreach ($fs->view->mounts as $mount){ ?>
			<tr>
				<td><input type="text" name="" value="<?php echo $mount->getMountpoint(); ?>" /></td>
				<td><input type="text" name="" value="<?php echo $mount->getPath(); ?>" /></td>
				<td><input type="text" name="" value="<?php echo $mount->getType(); ?>" /></td>
				<td><input type="text" name="" value="<?php echo $mount->getOptions(); ?>" /></td>
				<td><input type="button" value="Löschen" name="delete" onclick="document.getElementById('action').value='delete_<?php echo $i; ?>';submit();" /></td>
			</tr>
		<?php $i++; } ?>
		<tr>
			<td colspan = 5><br /><hr><br />
				<?php echo _('Add new mountpoint') ?>:
			</td>
		</tr>
		<tr>
			<td><input type="text" name="mountpoint" value="" /></td>
			<td><input type="text" name="path" value="" /></td>
			<td><input type="text" name="type" value="" /></td>
			<td><input type="text" name="options" value="" /></td>
			<td><input type="button" value="Speichern" name="add" onclick="document.getElementById('action').value='add';submit();" /></td>
		</tr>
		</table>	
	</form>
	<br /><br />
	<b><?php echo _('Important Information') ?>:</b><br />
	<?php echo _('Important Information Filesystem Description') ?>	
	
	<br /><br /><br /><br />
	DEBUGINFO
	<textarea rows="5" cols="80" readonly><?php echo $fs->view->mount_txt ?></textarea>
	
</div>	
															