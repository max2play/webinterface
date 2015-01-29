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
	<?php echo _("Exampleclass Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($fhem->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $fhem->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("This is the Example Setup Page. Configure and Test your own Plugin :)") ?></b></p>
		
	<br />
	<br />
	
	<?php echo _("Example Variable: ") ?><?php echo $fhem->view->example ?>
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		
		<?php /*
			Put everything that should be sent inside this form - you may have a look at the other files of max2play and copy / paste their content		
			*/?>
		
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _("Example Button") ?></td>
				<td><input type="button" id="doSomething" name="doSomething" onclick="document.getElementById('action').value='doSomething';submit();" value="<?php echo _("Do Something") ?>" /></td>
				<td><?php echo _("Do something to get things done ...") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _("Example Input") ?></td>
				<td><input type="text" id="inputtext" name="inputtext" value="<?php echo $fhem->inputtext ?>" /></td>
				<td><?php echo _("Well - explain what this should do...") ?></td> 
			  </tr>	 
			</table>
			
			<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save';submit();" value="<?php echo _("save") ?>" /></td>		
		</div>
		
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($fhem->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	