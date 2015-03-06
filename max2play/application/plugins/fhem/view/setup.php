<?php
/**
	 FHEM Setup View File
	
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
	<?php echo _t("FHEM Setup Page") ?>
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
	
	<?php echo _t("Fhem is a GPL'd perl server for house automation. It is used to automate some common tasks in the household like switching lamps / shutters / heating / etc. and to log events like temperature / humidity / power consumption. More information on <a href=http://fhem.de target='_blank'>http://fhem.de</a>");?><br />
	<br />
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />		
		
	<?php if(!$fhem->view->fhemversion) { ?>
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Install FHEM Server on this device") ?></b></p>
		<input type="button" id="install" name="install" onclick="document.getElementById('action').value='install';submit();" value="<?php echo _t("install FHEM") ?>" />
		<br /><br />				
	<?php }else{ ?>
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("FHEM is installed").': '.$fhem->view->fhemversion.' - '; ?><a href="http://<?php echo preg_replace('=:.*=','', $_SERVER['HTTP_HOST']).':'.$fhem->config->port;?>" target="_blank" style="text-decoration:underline;"><?php echo _t('Open FHEM Webinterface')?></a></b></p>
		<input type="button" id="uninstall" name="uninstall" onclick="document.getElementById('action').value='uninstall';submit();" value="<?php echo _t("uninstall FHEM") ?>" />		
		<br /><br />
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("FHEM settings") ?></b></p>
				
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _t("Webservice") ?></td>
				<td>
					<input type="text" id="port" name="port" value="<?php echo $fhem->config->port ?>" />					
				</td>
				<td><?php echo _t("Set up port for FHEM") ?></td> 
			  </tr>	 			  
			</table>
			<br />
			<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save';submit();" value="<?php echo _("save") ?>" />
		</div>
		
	<?php }?>
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($fhem->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	