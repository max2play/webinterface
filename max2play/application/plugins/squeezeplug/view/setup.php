<?php
/**
	 Squeezeplug Setup View File
	
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
	<?php echo _("Squeezeplug Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($squeezeplug->view->message[0])) echo implode('<br />', $squeezeplug->view->message).'<br /><br />'; ?>
	
	<?php echo _("This is the Squeezeplug Setup Page. You may install services and configure your squeezeplug device on this page.") ?>
	<br /><br />
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
		
	<table class="settings">
	  <tr>
		<td><?php echo _("Install MiniDLNA") ?></td>
		<td><input type="button" id="installMiniDLNA" name="installMiniDLNA" onclick="document.getElementById('action').value='installMiniDLNA';submit();" value="<?php echo _("Install MiniDLNA") ?>" /></td>
		<td><?php echo _("MiniDLNA is a Service for...") ?></td> 
	  </tr>	 
	</table>
	<br />
	
	</form>
	
	<br /><br />
	<?php echo _("DEBUG Info") ?>:
	<textarea rows="5" cols="80" readonly></textarea>
</div>	