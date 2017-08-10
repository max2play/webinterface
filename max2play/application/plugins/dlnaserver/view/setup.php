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
	<?php echo _("DLNA-Server") ?>
</h1>
<div class="entry-content">
	<?php if(isset($dlnaserver->view->message[0])){ ?>
		<div class="ui-widget">
		<div class="ui-state-highlight ui-corner-all"
			style="margin-bottom: 10px; padding: 0.4em .7em;">
			<p>
				<span class="ui-icon ui-icon-info"
					style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $dlnaserver->view->message); ?>					
				</p>
		</div>
	</div>
	<?php } ?>		
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		<p class="ui-state-default ui-corner-all" style="padding: 4px;">
			<span class="ui-icon ui-icon-video"
				style="float: left; margin: -2px 5px 0 0;"></span>
				<?php echo _("MiniDLNA Service (easy media access for other network devices)") ?>
		</p>
		<br />
		<?php echo _("MiniDLNA is a Service for sharing media-data from Max2Play with other devices in your network.") ?><br />
		<br />
		<?php if ($dlnaserver->minidlna['installed'] == false) { ?>
			<input type="button" id="installMiniDLNA" name="installMiniDLNA"
			onclick="document.getElementById('action').value='installMiniDLNA';submit();"
			value="<?php echo _("Install MiniDLNA") ?>" /><br />
		<?php } else { ?>
			<input type="button" id="uninstallMiniDLNA" name="uninstallMiniDLNA"
			onclick="document.getElementById('action').value='uninstallMiniDLNA';submit();"
			value="<?php echo _("Uninstall MiniDLNA") ?>" /><br />
		<br />
			<?php echo _("Media Path") ?> <input style="width: 90%;" type="text"
			name="minidlnaMediapath"
			value="<?php echo $dlnaserver->minidlna['mediapath'] ?>" /><br /> <input
			type="button" id="saveMiniDLNA" name="saveMiniDLNA"
			onclick="document.getElementById('action').value='saveMiniDLNA';submit();"
			value="<?php echo _("Save") ?>" />&nbsp;&nbsp;&nbsp;&nbsp; <input
			type="button" id="reloadMiniDLNA" name="reloadMiniDLNA"
			onclick="document.getElementById('action').value='reloadMiniDLNA';submit();"
			value="<?php echo _("Force Reload (reindex Folder)") ?>" /> <br />
		<?php } ?>
		
	</form>

	<br />
	<br /> <a href="#javascript"
		onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display: none;"><?php

foreach ($dlnaserver->view->debug as $key => $debug) {
    echo "#### " . $key . " ####\n";
    echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>
