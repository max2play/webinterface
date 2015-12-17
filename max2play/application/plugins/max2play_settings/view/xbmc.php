<?php
/**
	 XBMC Settings View File
	
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
	<?php echo _("Kodi / XBMC - Status, Start & Stop") ?>
</h1>
	
<div class="entry-content">
	<?php if(isset($sp->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $sp->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>		
	
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
	<?php if ($sp->view->pid) { ?>
		<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $sp->view->pid ?></b><br />
		<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />		
	<?php }else { ?>
		<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME not running')) ?><br /><br />				
		<?php echo _('Reinitialize Display (if screen is black or HDMI was not connected on startup)') ?><input type="checkbox" value="1" name="reinitx" />
		<br />
		<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />
	<?php } ?>
		<br /><br />
		<b><?php echo str_replace('$SERVICENAME', $sp->viewname, _('Autostart $SERVICENAME')) ?>:</b><sup>#1</sup><br />
		<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostart" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
		
		<br /><br />
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?> - <?php echo str_replace('$VERSION', $sp->xbmcversion, _('Replaces current $VERSION')) ?></p>
			
		<?php echo _('Choose Kodi/XBMC-Package by http-link (package must be .deb) for Update') ?>: 
		<input id="downloadurl" type="text" value="<?php if ($sp->getHardwareInfo() == "Raspberry PI") echo 'http://cdn.max2play.com/kodi-15.1-wheezy.tar.gz'; ?>" name="downloadurl" style="width:200px;" /><br /><br /> 	
		<input type="button" value="<?php echo str_replace('$NAME', $sp->viewname, _('$NAME start installation')) ?>" name="install" onclick="document.getElementById('action').value='install';submit();" />
		<br /><br /> <?php echo _('The installation may take some time depending on your internet connection. At first it downloads the package from given source and afterwards it installs the package.')?>			
		<br /><br />
		
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<?php echo _('Get Kodi / XBMC Addon-Repository from HTTP-URL or by choosing from Select-Box')?></p>			
		<?php echo _('You can enable repositories for further addons in settings menu of Kodi / XBMC: <i>System &gt; Addons</i>. After downloading the Addon-Repository Zip-File with the following button, you can find it with Kodi/XBMC (choose <i>add from zipfile</i>) in folder <i>/opt/max2play/cache</i> (choose root-folder and navigate there) and enable the Addons-Repository. After this, you will see the new addons available in the addons-list.') ?> 
		<br />
		<input id="addonurl" type="text" value="" name="addonurl" style="width:200px;" /><br />
		<select name="addonbox" onclick="copytodownloadurl(this);">
			<option value=""><?php echo _('Choose Addon')?></option>
			<option value="https://addonscriptorde-beta-repo.googlecode.com/files/repository.addonscriptorde-beta.zip">Amazon Instant Video (old XBMC version)</option>
			<option value="https://github.com/XLordKX/kodi/raw/master/zip/repository.xlordkx/repository.xlordkx-1.0.0.zip">Amazon Instant Video (new Kodi version)</option>
			<option value="http://cdn.max2play.com/kodi-15-pvr/max2play-u3-repository.zip">Kodi 15 PVR-Addons Repository (for ODROID and Kodi version 15 ONLY!)</option>
			<option value="http://cdn.max2play.com/kodi-15-pvr/max2play-rpi-repository.zip">Kodi 15 PVR-Addons Repository (for Raspberry Pi and Kodi version 15 ONLY!)</option>
		</select>
		<script type="text/javascript">
			function copytodownloadurl(item){
				document.getElementById("addonurl").value = item.options[item.selectedIndex].value;
				document.getElementById("addonurl").style.width = "500px";
			}
		</script>
		<input type="button" value="<?php echo _('Get Addon-Repository for Kodi / XBMC') ?>" name="getaddon" onclick="document.getElementById('action').value='getaddon';submit();" />
		
	</form>
	
	<br /><br />
	<?php echo _('XBMC INFO DESCRIPTION') ?>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;">DEBUG Information</a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($sp->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	
															