<?php
/**
	 Jivelite Setup
	
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
	<?php echo _t("Jivelite Squeezebox Desktop Visualisation") ?>
</h1>
<div class="entry-content">
	<?php if(isset($jl->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $jl->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>		
	
	<?php echo _t("This is the Jivelite Setup Page.") ?>
	<br /><br />
	<form action="" method="get">
	<input type="hidden" id="action" name="action" value="" />
	
<?php if($jl->view->installed) { ?>			
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-video" style="float:left; margin:-2px 5px 0 0;"></span>					
			<?php if ($jl->view->pid) { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $jl->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $jl->view->pid ?></b></p>				
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $jl->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
			<?php }else { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $jl->viewname, _('$SERVICENAME not running')) ?></p>
				<?php echo _('Reinitialize Display (if screen is black or HDMI was not connected on startup)') ?><input type="checkbox" value="1" name="reinitx" />
	  		    <br />
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $jl->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />	
			<?php } ?>	
	<br /><br />
	<b><?php echo str_replace('$SERVICENAME', $jl->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
	<input type="checkbox" <?php if($jl->view->autostart) echo "checked"; ?> value="1" name="autostart" />
	<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
<?php }else { ?>	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
		<span class="ui-icon ui-icon-gear" style="float:left; margin:-2px 5px 0 0;"></span>
		<?php echo str_replace('$NAME', $jl->viewname, _('$NAME start installation')) ?></p>	
	<input type="button" value="<?php echo str_replace('$NAME', $jl->viewname, _('$NAME start installation')) ?>" name="install" onclick="document.getElementById('action').value='install';submit();" />
	
<?php } ?>
	
	<br /><br />
	<b><?php echo _t('Autohide Panel (Taskbar) for Fullscreen') ?>:</b><br />
	<input type="checkbox" <?php if($jl->view->autohidepanel) echo "checked"; ?> value="1" name="autohidepanel" />
	<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />	

	</form>

	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;">DEBUG Informationen</a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($jl->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>

</div>	