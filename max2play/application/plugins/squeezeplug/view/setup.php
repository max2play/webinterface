<h1 class="entry-header">
	<?php echo _t("Squeezeplug & Max2Play - Info") ?>
</h1>
	
<div class="entry-content">
    <?php if(isset($squeezeplug->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $squeezeplug->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>	
	<h2><?php echo _t("Welcome to Squeezeplug with Max2Play - Your easy to use Interface for Multiroom-Audio Solution!")?></h2>	
	<br />
	<?php echo _t("The Max2Play Interface is used to configure the device without having to set up monitor, keyboard and mouse on the device itsself or login by ssh.<br />All settings are directly written to the players config files."); ?><br />  
	<br />
	<h3><?php echo _t("What is Max2Play?")?></h3>
	<br />
	<iframe width="560" height="315" src="<?php echo _("//www.youtube.com/embed/GjYnHZF2Oek")?>" frameborder="0" allowfullscreen></iframe>
	<br /><br />
	<?php echo _t('Questions concerning this project? Please refer to <a href="http://shop.max2play.com" target="_blank">Shop.Max2Play</a>') ?><br />
	<br /><br />
	<b><?php echo _t("READ CAREFULLY: THIS INTERFACE IS STILL A BETA AND MIGHT HAVE BUGS OR UNEXPECTED BEHAVIOR!") ?></b>
</div>	