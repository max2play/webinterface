<h1 class="entry-header">
	<?php echo _("Max2Play Media Player - Info") ?>
</h1>
	
<div class="entry-content">
    <?php if(isset($service->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $service->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	<b><?php echo _("READ CAREFULLY: THIS INTERFACE IS STILL A BETA AND MIGHT HAVE BUGS OR UNEXPECTED BEHAVIOR!") ?></b>
	<br /><br />
	<?php echo _("The Max2Play Interface is used to configurate the device without having to set up montior, keyboard and mouse on the device itsself or login by ssh.<br />All settings are directly written to the players config files."); ?><br />  
	<br />
	<?php echo _('Questions concerning this project? Please refer to <a href="http://shop.max2play.com" target="_blank">Shop.Max2Play</a>') ?><br />
	
	
</div>	