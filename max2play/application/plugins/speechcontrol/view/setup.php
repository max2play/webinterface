<?php
/**
	 Speech Control Setup View File
	
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
	<?php echo _("Speech Control Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($speechcontrol->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $speechcontrol->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<?php echo _t('Enable speech communication for your Max2Play device!');?>	
	<br />
	
	<?php echo _t('This plugin is still in development. Make sure no audioplayer is running while using Jasper.');?>
	
	<br /><br />
	<?php echo _t('This happens on installation: 
			<ul class="description">
				<li>Jasper Framework gets installed and configured</li>
				<li>The TextToSpeech Engine ESpeak gets installed and configured</li>
				<li>SpeachToText Engine SphinxBase and Pocketsphinx get installed</li>				
			</ul>')?>
		
	<br /><br />		
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		<?php if(!$speechcontrol->view->installed){ ?>
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Install Speech Control on this device. You need a microphone connected to your device (probably with USB).") ?></b></p>
				<input type="button" id="install" name="<?php echo _("install") ?>" onclick="document.getElementById('action').value='install';submit();" value="<?php echo _("install (Takes ~ 2 hours!)") ?>" />		
		<?php } else { ?>
			
			
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t('Speech Control is installed!'); ?></b></p>
			<?php if ($speechcontrol->view->pid) { ?>
				<?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $speechcontrol->view->pid ?></b>				
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $speechcontrol->viewname, _('stop $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
			<?php }else { ?>
				<?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME not running')) ?>
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $speechcontrol->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />	
			<?php } ?>
			
			
			<br /><br />
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Test reading with eSpeak") ?></b></p>
			<div class="optiondetails"> 
				<table class="settings">
				  <tr>
				  <td colspan=3>
				  	<textarea rows="5" cols="40" name="readtext"><?php if(isset($_GET['readtext'])) echo $_GET['readtext']; else echo _t('Just type in some text, that should be read by your device.') ?></textarea>
				  </td>
				  </tr>
				  <tr>
					<td><?php echo _t('Spoken Language') ?></td>
					<td>
						<input type="text" size="2" name="readlang" value="<?php if(isset($_GET['readlang'])) echo $_GET['readlang']; else echo _t("en");?>" />
					</td>
					<td><?php echo _t("Language for reading of text. E.g. 'en' or 'de'") ?></td> 
				  </tr>
				  <tr>
					<td><?php echo _t('Reading Speed') ?></td>
					<td>
						<input type="text" size="2" name="readspeed" value="<?php if(isset($_GET['readspeed'])) echo $_GET['readspeed']; else echo "120";?>" />
					</td>
					<td><?php echo _t("Speed for reading. Should be between 60 and 400") ?></td> 
				  </tr>		
				  <tr>
					<td><?php echo _t('Audio Device') ?></td>
					<td>
						<input type="text" size="2" name="readdevice" value="<?php if(isset($_GET['readdevice'])) echo $_GET['readdevice']; else echo "default";?>" />
					</td>
					<td><?php echo _t("Audio Output Device. Default is a good value. Other Soundcards might need a proper asound configuration.") ?></td> 
				  </tr>					 
				</table>
				<input type="button" id="readtest" name="<?php echo _("readtest") ?>" onclick="document.getElementById('action').value='readtest';submit();" value="<?php echo _("start read test") ?>" />
			</div>					
						
			<br /><br />
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Edit basic Speech Control settings") ?></b></p>
			<div class="optiondetails"> 
				<table class="settings">
				  <tr>
					<td><?php echo _t("Language") ?></td>
					<td>
						<input type="text" id="jasper_language" name="jasper_language" value="<?php echo $speechcontrol->config->jasper->language ?>" />
					</td>
					<td><?php echo _t("Language for input and output of speech") ?></td> 
				  </tr>	 				 
				</table>
				<br />
				<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='saveSettings';submit();" value="<?php echo _("save") ?>" />
			</div>
			
			<br /><br />
			<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
				<span class="ui-icon ui-icon-cancel" style="float:left; margin:-2px 5px 0 0;"></span>
				<b><?php echo _t("Completely remove Speech Control") ?></b></p>				
				<input type="button" id="uninstall" name="<?php echo _("uninstall") ?>" onclick="document.getElementById('action').value='uninstall';submit();" value="<?php echo _("uninstall") ?>" />			
			
		<?php } ?>
		
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($speechcontrol->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	