<?php if(isset($service->view->error[0])){ ?>
<div class="ui-widget">
	<div class="ui-state-error ui-corner-all"
		style="margin-bottom: 10px; padding: 0.4em .7em;">
		<p>
			<span class="ui-icon ui-icon-alert"
				style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br /><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>', $service->view->error); ?>					
				</p>
	</div>
</div>
<?php } ?>
<?php if(isset($service->view->message[0])){ ?>
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all"
		style="margin-bottom: 10px; padding: 0.4em .7em;">
		<p>
			<span class="ui-icon ui-icon-info"
				style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br /><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>', $service->view->message); ?>					
				</p>
	</div>
</div>
<?php } ?>
<?php if(isset($error[0])){ ?>
<div class="ui-widget">
	<div class="ui-state-error ui-corner-all"
		style="margin-bottom: 10px; padding: 0.4em .7em;">
		<p>
			<span class="ui-icon ui-icon-alert"
				style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br /><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>', $error); ?>					
				</p>
	</div>
</div>
<?php } ?>
<?php if(isset($message[0])){ ?>
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all"
		style="margin-bottom: 10px; padding: 0.4em .7em;">
		<p>
			<span class="ui-icon ui-icon-info"
				style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br /><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>', $message); ?>					
				</p>
	</div>
</div>
<?php } ?>