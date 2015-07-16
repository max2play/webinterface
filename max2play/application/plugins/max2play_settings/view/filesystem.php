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
	<?php if(isset($fs->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $fs->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />	
		
		<table>
			<tr>
				<td><?php echo _('Path') ?></td>
				<td><?php echo _('Mountpoint') ?></td>
				<td><?php echo _('Type') ?></td>
				<td><?php echo _('Options') ?></td>
				<td><?php echo _('Action') ?></td>
			</tr>
		<?php $i=0;
			 foreach ($fs->view->mounts as $mount){ ?>
			<tr>
				<td><input style="width:95%;" type="text" name="" size="20" value="<?php echo $mount->getMountpoint(false); ?>" /></td>
				<td><input style="width:95%;" type="text" name="" size="8" value="<?php echo $mount->getPath(); ?>" /></td>
				<td><input style="width:95%;" type="text" name="" size="4" value="<?php echo $mount->getType(); ?>" /></td>
				<td><input style="width:95%;" type="text" name="" size="40" value="<?php echo $mount->getOptions(); ?>" /></td>
				<td><input style="width:95%;" type="button" value="<?php echo _('Delete'); ?>" name="delete" onclick="document.getElementById('action').value='delete_<?php echo $i; ?>';submit();" /></td>
			</tr>
		<?php $i++; } ?>
		<tr>
			<td colspan = 5><br /><hr><br />
				<?php echo _('Add new mountpoint') ?>:
			</td>
		</tr>
		<tr>
			<td><input style="width:95%;" type="text" id="mountpoint" name="mountpoint" value="" size="20" /><br /><span class="small">//192.168.1.100/share</span></td>
			<td><input style="width:95%;" type="text" name="path" value="" size="8" /><br /><span class="small">/mnt/share</span></td>
			<td><input style="width:95%;" type="text" name="type" value="" size="4" /><br /><span class="small">cifs</span></td>
			<td><input style="width:95%;" type="text" name="options" value="" size="40" /><br /><span class="small">user=name,password=pass,sec=ntlm,iocharset=utf8</span></td>
			<td valign=top><input type="button" value="<?php echo _('Save'); ?>" name="add" onclick="document.getElementById('action').value='add';submit();" /></td>
		</tr>
		</table>	
	
	<br /><br />
	<script>	
		function loadajax(){
			ajaxload("networkdevices","/plugins/max2play_settings/controller/Filesystem.php?shownetworkshares=1&ajax=1");
			document.getElementById('networkdevices').style.display = '';
		}
	</script>
	
	<a href="#javascript" onclick="document.getElementById('helpmount').style.display = '';" class="download-button" style="width:40%; float:left;"><?php echo _("Show Help"); ?></a>
	<a href="#javascript" onclick="loadajax();" class="download-button" style="width:40%; float:right;"><?php echo _("Show Network Devices"); ?></a>
	<br style="clear:both;" />
	<div id="networkdevices" style="display:none;" >
		
	</div>
	
	<div id="helpmount" style="display:none;" >		
		<br />
		<b><?php echo _('Important Information') ?>:</b><br />
		<?php echo _('Important Information Filesystem Description') ?>	
	</div>
	
	<br /><br />	
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Info") ?></a>
	<textarea id="debug" rows="5" cols="80" style="display:none;"><?php echo $fs->view->mount_txt ?></textarea>	
	
	<br /><br /><br />
	<h1 class="entry-header" id="usbdrivesblock">
		<?php echo _("External Drives on Max2Play - mountpoints for USB-Storage") ?>
	</h1>
	<p><?php echo _("This list shows all external storages and their mountpoints. You may access the devices by their path in Squeezeboxserver or XBMC/Kodi.") ?></p>
	<?php if ($fs->view->mountpointsSDA){?>
		<ul class="description">
		<?php foreach ($fs->view->mountpointsSDA as $mnt){ ?>
	 		<li><b><?php echo $mnt['label'] .' in '. $mnt['path'].'</b>  (Device ' .$mnt['device']. ' with UUID '.$mnt['uuid'].')'; ?>
	 		<br /><?php echo _('Set fixed Mountpoint to prevent directory switching on reboot: ')?>
	 		<input type="checkbox" name="fixmount[<?php echo $mnt['device'] ?>]" <?php if($mnt['fixmounted']) echo 'checked' ?> />
	 		<input type="button" value="<?php echo _('Save'); ?>" name="add" onclick="document.getElementById('action').value='setfixmount';submit();" />
	 		</li>
		<?php } ?>
		</ul>
	<?php }else echo "<b>"._("No external devices found.")."</b>" ?>
	</form>
	
	<br /><br />
	<h1 class="entry-header" id="sambashares">
		<?php echo _("Sambashares on Max2Play - setup network share paths") ?>
	</h1>
	<form action="" method="get">
		<input type="hidden" id="sambaaction" name="sambaaction" value="" />	
		
		<table>
			<tr>
				<td><?php echo _('Name') ?></td>
				<td><?php echo _('Path') ?></td>
				<td><?php echo _('Comment') ?></td>
				<td><?php echo _('Writeable') ?></td>
				<td><?php echo _('Create Mode') ?></td>
				<td><?php echo _('Action') ?></td>
			</tr>
		<?php $i=0;
			foreach ($fs->view->sambashares as $samba){ ?>
			<tr>
				<td><input type="text" style="width:95%;" name="" value="<?php echo $samba->getName(); ?>" /></td>
				
				<td><input type="text" style="width:95%;" name="" value="<?php echo $samba->getOptions('path'); ?>" /></td>
				<td><input type="text" style="width:95%;" name="" value="<?php echo $samba->getOptions('comment'); ?>" /></td>
				<td><input type="text" style="width:95%;" name="" size=4 value="<?php echo $samba->getOptions('writeable'); ?>" /></td>
				<td><input type="text" style="width:95%;" name="" size=4 value="<?php echo $samba->getOptions('create mode'); ?>" /></td>

				<td><input type="button" value="<?php echo _('Delete'); ?>" name="delete" onclick="document.getElementById('sambaaction').value='delete_<?php echo $i; ?>';submit();" /></td>
			</tr>
		<?php $i++; } ?>
		<tr>
			<td colspan = 6><br /><hr><br />
				<?php echo _('Add new Sambashare') ?>:
			</td>
		</tr>
		<tr>
			<td><input type="text" style="width:95%;" name="name" value="" /><br /><span class="small"><?php echo _('usbshare'); ?></span></td>
			<td><input type="text" style="width:95%;" name="path" value="" /><br /><span class="small">/media/usb</span></td>
			<td><input type="text" style="width:95%;" name="comment" value="" /><br /><span class="small">Max2Play USB</span></td>
			<td><input type="text" style="width:95%;" size=4 name="writeable" value="" /><br /><span class="small">yes</span></td>
			<td><input type="text" style="width:95%;" size=4 name="create mode" value="" /><br /><span class="small">664</span></td>
			<td valign="top"><input type="button" value="<?php echo _('Save'); ?>" name="add" onclick="document.getElementById('sambaaction').value='add';submit();" /></td>
		</tr>
		</table>
		<br /><br />
		<b><?php echo _('Sambashare Access') ?></b><br />
		
		<table>
			<tr><td><?php echo _('User for Samba-Access') ?>:</td> 
			<td><input type="text" readonly name="sambauser" value="root" /></td>
			</tr>
			<tr><td><?php echo _('Password for Samba-Access') ?>:</td> 
			<td><input type="text" name="sambpass" value="<?php echo $fs->view->sambapass ?>" /></td>
			</tr>
			</table>
		<input type="button" value="<?php echo _('Save'); ?>" name="savepassword" onclick="document.getElementById('sambaaction').value='savepassword';submit();" />
		<br />
	</form>
	<br /><br />
	<b><?php echo _('Samba Important Information') ?>:</b><br />
	<?php echo _('Important Information Samba Description') ?>	
	
</div>	
															