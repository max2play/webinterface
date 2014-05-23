<?php
/**
	 Advanced_Max2play_Setup View File
	
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
	<?php echo _("Advanced Max2Play Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($advanced_max2play_setup->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $advanced_max2play_setup->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>		
	
	<?php echo _("This is the Advanced Setup Page. You may configure additional settings on this page.") ?>
	<br /><br />
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
		
	<table class="settings">
	  <tr>
		<td><?php echo _("Disable LED-Blink") ?></td>
		<td><input type="button" id="disableLEDBlink" name="disableLEDBlink" onclick="document.getElementById('action').value='disableLEDBlink';submit();" value="<?php echo _("Disable LED-Blink") ?>" /></td>
		<td><?php echo _("Set LED Blink of ODROID to 10 secondes - Press Button again to set LED-Blink back to original settings.") ?></td> 
	  </tr>	 
	</table>
	<br />
	
	</form>
	
	<br /><br />
	<?php echo _("DEBUG Info") ?>:
	<textarea rows="5" cols="80" readonly></textarea>
</div>	