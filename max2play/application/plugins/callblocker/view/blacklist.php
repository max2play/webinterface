<?php
/**
	 Callblocker tellows Blacklist
	
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
	<?php echo _t("Callblocker Blacklist & Whitelist") ?>
</h1>
<div class="entry-content">
	<?php if(count($cb->view->message) > 0){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $cb->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>		
	
	<?php echo _t("On this page you may edit a local blacklist and whitelist. This is used additionally to the tellows blacklist. Use one column per number.") ?>
	<br /><br />
	<form action="" method="post">
	<input type="hidden" id="action" name="action" value="" />
	<input type="hidden" id="number" name="number" value="" />
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-arrowthickstop-1-w" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Callerlog for the last incoming Calls") ?></b></p>	
	<table style="width:100%">
	<tr>
		<th style="width:140px;"><?php echo _t('DATE')?></th>
		<th><?php echo _t('LINE')?></th>
		<th><?php echo _t('NUMBER')?></th>
		<th><?php echo _t('NAME')?></th>
		<th><?php echo _t('BLOCKED')?></th>
		<th><?php echo _t('ACTION')?></th>
	</tr>	
	<?php foreach($cb->calllog as $log){ ?>
		<tr>
			<td><span <?php if ($log['blacklist']){ ?>class="ui-icon ui-icon-alert" title="<?php echo _t('Blocked by Blacklist')?>" <?php } ?>style="float:left; margin:-2px 5px 0 0; width:16px;height:16px;"></span>
				<?php echo $log['date'] ?></td>
			<td><?php echo $log['line']?></td>
			<td><a href="http://www.tellows.<?php echo $cb->tellows->country; ?>/num/<?php echo urlencode($log['number']) ?>" style="text-decoration: none;" target="_blank">
				<?php if ($log['blacklist_type'] != '' && !$log['whitelist']) echo '<span style="color:red;" title="'._t('Number is on Blacklist').'">'.$log['number'].'</span>'; 
					  elseif($log['whitelist']) echo '<span style="color:green;" title="'._t('Number is on Whitelist').'">'.$log['number'].'</span>'; 
					  else echo $log['number']; ?>
			    </a>
			</td>
			<td><?php echo $log['name'] ?></td>
			<td><?php echo $log['blacklist_type'] ?></td>
			<td><?php if ($log['blacklist_type'] != '' && !$log['whitelist']){ ?>
				<input type="button" value="<?php echo _t('whitelist')?>" onclick="document.getElementById('number').value='<?php echo $log['number'] ?>';document.getElementById('action').value='whitelist';submit();" />
				<?php } else { ?>
				<input type="button" value="<?php echo _t('blacklist')?>" onclick="document.getElementById('number').value='<?php echo $log['number'] ?>';document.getElementById('action').value='blacklist';submit();" />
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
	</table>
	
	<br />
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Modify Blacklist") ?></b></p>	
	<?php echo _t('To block Callers that do not transfer their Caller ID enter "PRIVATE" in a separate row.') ?>
	<br />
	<textarea rows="20" cols="20" name="blacklist"><?php echo $cb->view->blacklist ?></textarea>	
	<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
	<br /><br />
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t("Modify Whitelist (numbers that will definitly NOT be blocked)") ?></b></p>
	
	<textarea rows="20" cols="20" name="whitelist"><?php echo $cb->view->whitelist ?></textarea>
	<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
	</form>
	
	<br /><br />
</div>	