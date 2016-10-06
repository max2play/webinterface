<h1 class="entry-header">
	<?php echo _("Max2Play - Start") ?>
</h1>
	
<div class="entry-content">
    <?php $message=$start->view->message;
    	  $error=$start->view->error; 
    	  include(APPLICATION_PATH.'/view/messages.php');?>
	
	<h2><?php echo _("Welcome to Max2Play - Your easy to use Interface for Multiroom-Audio and Video!")?></h2>	
	<br />
	<?php echo _("The Max2Play Web Interface is used to configure the device without having to set up monitor, keyboard and mouse on the device itsself or login by ssh.<br />All settings are directly written to the players config files."); ?><br />  
	<br />
		
	<form action="" method="post" id="form1">
		<input type="hidden" id="action" name="action" value="" />		
		<?php if($start->easyinstaller == 1) include_once('easyinstaller.php'); ?>
		
	</form>
	<br /><br />	
	<?php echo _("Max2Play gives you the best experience with High-Quality Sound together with an easy configuration and control. With the extensions of Max2Play you can easily choose and install new features like the following:")?>	
	<br /><br />
	
	<h1 class="entry-header">
		<?php echo _("Features with Max2Play"); ?>
	</h1>
	<ul class="description">
	<li><?php echo str_replace('|', '</li><li>', _("!FEATURE LIST")); ?>
	</li>
	</ul>
	
	<h1 class="entry-header">
		<?php echo _("Premium Features with Max2Play"); ?>
	</h1>
	<ul class="description">
	<li><?php echo str_replace('|', '</li><li>', _("!FEATURE LIST PREMIUM")); ?>
	</li>
	</ul>
	<a style="float:left;" href="http://shop.max2play.com/en/max2play-full-license.html/" target="_blank"><img src="http://shop.max2play.com/media/wysiwyg/Premium-box-en_white.png" /></a>		
	<br /><br />
	<?php echo _("<b>Max2Play</b> makes the configuration as easy as possible. There is no need for connecting a monitor, a keyboard or a mouse on the device itself. It all runs headless and no special tools like Putty are needed! This webinterface is optimized for smartphones and all actions can also be called from external HTTP-Requests like Smarthome-Servers."); ?>
	
	<br style="clear:both" />
	<br />
	  	
	<h3><?php echo _("What is Max2Play?")?></h3>
	<br />	
	<iframe width="560" height="315" src="<?php echo _("http://www.youtube.com/embed/pZB4aSiW4c0")?>" frameborder="0" allowfullscreen></iframe>	
	
	<br /><br />
	<?php echo _('Questions concerning this project? Please refer to <a href="http://www.max2play.com" target="_blank">www.Max2Play.com</a>') ?><br />	
		
</div>	