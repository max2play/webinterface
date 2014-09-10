<?php
/**
	 Callblocker tellows Setup
	
	 @Copyright 2014 Stefan Rick
	 @author Stefan Rick
	 Mail: stefan@rick-software.de
 	 Web: http://www.netzberater.de

 */ 
?>											
<style>
<!--
.description {
    list-style: circle outside none;
    padding-left: 20px;
}
-->
</style>

<h1 class="entry-header">
	<?php echo _t("tellows Callblocker Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($cs->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $cs->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>		
	
	<?php echo _t("This is the Callblocker Setup Page. You need a tellows API Key to get the blocklists from tellows.") ?>
	<br /><br />
	
<?php if($cs->view->installed) { ?>	

	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Main tellows Settings - Connected to tellows: ") ?><?php echo $cs->tellows->registered ?></b></p>
			
	<?php echo str_replace(array('$COUNT', '$DATE'), array($cs->tellows->blacklist_entries, $cs->tellows->blacklist_date),_t('The current tellows blacklist has $COUNT phonenumbers and was last updated on $DATE.')) ?>			
	<br /><br />
	<table class="settings">
	  <tr>
		<td><?php echo _t("Block calls") ?></td>
		<td><select name="tellows_minscore">
				<option value="7" <?php if($cs->tellows->minscore == 7) echo 'selected' ?>>Score 7</option>
				<option value="8" <?php if($cs->tellows->minscore == 8) echo 'selected' ?>>Score 8</option>
				<option value="9" <?php if($cs->tellows->minscore == 9) echo 'selected' ?>>Score 9</option>
			</select></td>
		<td><?php echo _t("Block calls from numbers that have a tellows score euqal or higher than this value and at least 3 comments.") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _t("API-Key") ?></td>
		<td><input type="text" value="<?php echo $cs->tellows->apikey ?>" name="tellows_apikey" /></td>
		<td><?php echo _t("You need a API-Key to get all numbers that should be blocked.") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _t("Country ISO Code") ?></td>
		<td><select name="tellows_country">
				<option <?php if($cs->tellows->country == 'de') echo 'selected' ?>>de</option>
				<option <?php if($cs->tellows->country == 'it') echo 'selected' ?>>it</option>
				<option <?php if($cs->tellows->country == 'uk') echo 'selected' ?>>uk</option>
				<option <?php if($cs->tellows->country == 'fr') echo 'selected' ?>>fr</option>
				<option <?php if($cs->tellows->country == 'at') echo 'selected' ?>>at</option>
				<option <?php if($cs->tellows->country == 'ch') echo 'selected' ?>>ch</option>
				<option <?php if($cs->tellows->country == 'pl') echo 'selected' ?>>pl</option>
				<option <?php if($cs->tellows->country == 'es') echo 'selected' ?>>es</option>
				<option <?php if($cs->tellows->country == 'ru') echo 'selected' ?>>ru</option>
				<option <?php if($cs->tellows->country == 'us') echo 'selected' ?>>us</option>				
			</select>
		</td>
		<td><?php echo _t("In which country do you live? Important for getting the correct blacklist and for identifying international numbers.") ?></td> 
	  </tr>
	</table>
	<input type="button" value="<?php echo _t('save') ?>" name="save" onclick="document.getElementById('action').value='savetellows';submit();" />
	<br /><br />
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("SIP Settings for VOIP Setup - Register Status: ") ?><?php echo $cs->linphone->registered ?></b>
	</p>
	<table class="settings">
	  <tr>
		<td><?php echo _t("VOIP-Host") ?></td>
		<td><input type="text" value="<?php echo $cs->linphone->host ?>" name="linphone_host" /></td>
		<td><?php echo _t("If you have a fritzbox the value is &quot;fritz.box&quot;") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _t("VOIP-User") ?></td>
		<td><input type="text" value="<?php echo $cs->linphone->user ?>" name="linphone_user" /></td>
		<td><?php echo _t("If you have a fritzbox the value is problably &quot;620&quot;") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _t("VOIP-Password") ?></td>
		<td><input type="text" value="<?php echo $cs->linphone->password ?>" name="linphone_password" /></td>
		<td><?php echo _t("This is your VOIP-Password.") ?></td> 
	  </tr>
	  <tr>
		<td><?php echo _t("Audio File for Blacklist") ?></td>
		<td>
			<select name="tellows_audiofile">
				<option value="0" <?php if($cs->tellows->audiofile == 0) echo 'selected'; ?> ><?php echo _t("Hang up without Sound") ?></option>
				<option value="1" <?php if($cs->tellows->audiofile == 1) echo 'selected'; ?> ><?php echo _t("Play: You are blocked") ?></option>
			</select>
		</td>
		<td><?php echo _t("Choose which audio file should be played when the incoming caller is on the blacklist.") ?></td> 
	  </tr>	
	</table>
	<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='savelinphone';submit();" />	
	<br />
	<br />	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Modem for PSTN (analog) Connected: ") ?><?php echo $cs->view->modemconnected ?></b></p>
	<?php if ($cs->view->modemconnected) { ?>
		<table class="settings">
		  <tr>
			<td><?php echo _t("Hangup Type") ?></td>
			<td><select name="hangup_type">
					<option value="0" <?php if($cs->modem->hangup_type == 0) echo 'selected'; ?> ><?php echo _t("Do Nothing (not recommended)") ?></option>
					<option value="1" <?php if($cs->modem->hangup_type == 1) echo 'selected'; ?> ><?php echo _t("Hangup (block call)") ?></option>
					<option value="2" <?php if($cs->modem->hangup_type == 2) echo 'selected'; ?> ><?php echo _t("Answer as Fax-Machine") ?></option>
				</select></td>
			<td><?php echo _t("Choose how the callblocker will react on a blacklisted Caller (number on blacklist)") ?></td> 
		  </tr>
		</table>
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='savemodemsettings';submit();" />
	<?php } ?>	
	</form>
	<br />
	
	<h3><?php echo _t('How it works'); ?></h3>	
	<?php echo _t('SETUP_DESCRIPTION'); ?>	
	<br /><br />
	
	<h3><?php echo _t('Copyrights'); ?></h3>
	<?php echo _t('Copyrights_TEXT'); ?>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;">DEBUG Informationen</a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($cs->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>

<?php }?>
	<form action="" method="get">
		<?php echo _t('Current Callblocker Version') ?>: <?php echo $cs->view->version ?>
		<input type="hidden" id="actionupdate" name="actionupdate" value="" />
		<input type="button" value="<?php echo _t('Update tellows Callblocker') ?>" name="update" onclick="document.getElementById('actionupdate').value='updateCallblocker';submit();" />
	</form>

</div>	