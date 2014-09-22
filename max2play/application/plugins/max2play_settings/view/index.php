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
	<h2><?php echo _("Welcome to Max2Play - Your easy to use Interface for Multiroom-Audio and Video!")?></h2>	
	<br />
	<?php echo _("The Max2Play Interface is used to configure the device without having to set up monitor, keyboard and mouse on the device itsself or login by ssh.<br />All settings are directly written to the players config files."); ?><br />  
	<br />
	<h3><?php echo _("What is Max2Play?")?></h3>
	<br />
	<iframe width="560" height="315" src="//www.youtube.com/embed/GjYnHZF2Oek" frameborder="0" allowfullscreen></iframe>
	<br /><br />
	<?php echo _('Questions concerning this project? Please refer to <a href="http://shop.max2play.com" target="_blank">Shop.Max2Play</a>') ?><br />
	<br /><br />
	<b><?php echo _("READ CAREFULLY: THIS INTERFACE IS STILL A BETA AND MIGHT HAVE BUGS OR UNEXPECTED BEHAVIOR!") ?></b>
</div>	