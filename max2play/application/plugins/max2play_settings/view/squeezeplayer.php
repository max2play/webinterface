<?php
/**
	 Audioplayer View File
	
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

<style>
#eq span {
height:120px; float:left; margin:15px
}
</style>
<script>
$(function() {
	$( "#eq > span" ).each(function() {
	// read initial values from markup and remove that
	var value = parseInt( $( this ).text(), 10 );
	var id = $( this ).attr('id');
	$( this ).empty().slider({
		value: value,
		id: id,
		range: "min",
		min: 0,
		max: 100,
		animate: true,
		orientation: "vertical",
		slide: function(event, ui) {
	        $("#selectedEqual").val( ui.value );
	        $("#selectedEqualSpan").text( id );
	    },
	    stop : function(event, ui){	        
	        $('[id="'+ id +'"]').val(ui.value);	        
	    }
		});
	});
});
</script>

<h1 class="entry-header">
	<?php echo _("Mediaplayer Squeezelite & Airplay - Status, Start / Stop") ?>
</h1>
	
<div class="entry-content">
	
	<?php if(isset($sp->view->message[0]) || isset($ap->view->message[0]) || isset($ss->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php if(isset($sp->view->message[0])) echo implode('<br />', $sp->view->message); ?>
					<?php if(isset($ap->view->message[0])) echo implode('<br />', $ap->view->message); ?>
					<?php if(isset($ss->view->message[0])) echo implode('<br />', $ss->view->message); ?>
				</p>
			</div>
		</div>
	<?php } ?>
	
	<form action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-volume-on" style="float:left; margin:-2px 5px 0 0;"></span>					
			<?php if ($sp->view->pid) { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $sp->view->pid ?></b></p>
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stop';submit();" />
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('kill $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='kill';submit();" />
			<?php }else { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $sp->viewname, _('$SERVICENAME not running')) ?></p>
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $sp->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='start';submit();" />	
			<?php } ?>
		
		<input type="submit" onclick="document.getElementById('squeezelitedetails').style.display='';return false;" value="<?php echo _('Edit Advanced Options') ?>" />
		<div id="squeezelitedetails" class="optiondetails" style="display:none;">
			<table>
				<tr>
					<td><?php echo _('Soundcard') ?></td>					
					<td style="width: 25%;"><select name="squeezelite_soundcard" style="width: 90%;">
						<option value='plug:dmixer'>Default dmixer (Alsa Mixer Device)</option>
						</select>
					</td>
					<td><?php echo _('Select Audio output') ?></td>
				</tr>
				<tr>
					<td><?php echo _('Command Line Options') ?></td>					
					<td><input style="width: 90%;" type="text" name="squeezelite_commandline" value="<?php echo $sp->view->squeezelite_commandline ?>" />
					</td>
					<td><?php echo _('Advanced Users may add Start-Parameters here') ?></td>
				</tr>
			</table>
			<input type="button" name="save" value="<?php echo _('save') ?>" onclick="document.getElementById('action').value='save';submit();" />
		</div>
		
		<br /><br /><b><?php echo str_replace('$SERVICENAME', $sp->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
		<input type="checkbox" <?php if($sp->view->autostart) echo "checked"; ?> value="1" name="autostartsqueeze" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
		
		<br /><br /><br />
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-volume-on" style="float:left; margin:-2px 5px 0 0;"></span>
			<?php if ($ap->view->pid) { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $ap->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $ap->view->pid ?></b></p>
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $ap->viewname, _('stop $SERVICENAME')) ?>" name="stopap" onclick="document.getElementById('action').value='stopap';submit();" />
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $ap->viewname, _('kill $SERVICENAME')) ?>" name="killap" onclick="document.getElementById('action').value='killap';submit();" />
			<?php }else { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $ap->viewname, _('$SERVICENAME not running')) ?></p>
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $ap->viewname, _('start $SERVICENAME')) ?>" name="startap" onclick="document.getElementById('action').value='startap';submit();" />			
			<?php } ?>
		<input type="submit" onclick="document.getElementById('shairportdetails').style.display='';return false;" value="<?php echo _('Edit Advanced Options') ?>" />
		<div id="shairportdetails" class="optiondetails" style="display:none;">
			<table>
				<tr>
					<td><?php echo _('Soundcard') ?></td>
					<td style="width: 25%;">
					    <select name="shairport_soundcard" style="width: 90%;">
						<option <?php if($ap->view->shairport_soundcard == 'plug:plugequal') echo 'selected'; ?> value='plug:plugequal'>Use Equalizer</option>
						<option <?php if($ap->view->shairport_soundcard == 'plug:dmixer') echo 'selected'; ?> value='plug:dmixer'>Default dmixer (Alsa Mixer Device)</option>
						</select>
					</td>
					<td><?php echo _('Select Audio output') ?></td>
				</tr>
			</table>
			<input type="button" name="save" value="<?php echo _('save') ?>" onclick="document.getElementById('action').value='save';submit();" />
		</div>
		<br /><br /><b><?php echo str_replace('$SERVICENAME', $ap->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
		<input type="checkbox" <?php if($ap->view->autostart) echo "checked"; ?> value="1" name="autostartap" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
		
		<br /><br /><br />
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-volume-on" style="float:left; margin:-2px 5px 0 0;"></span>
			<?php if ($ss->view->pid) { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $ss->viewname, _('$SERVICENAME is running with processID')) ?> <b><?php echo $ss->view->pid ?></b></p>
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $ss->viewname, _('stop $SERVICENAME')) ?>" name="stop" onclick="document.getElementById('action').value='stopss';submit();" />
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $ss->viewname, _('kill $SERVICENAME')) ?>" name="kill" onclick="document.getElementById('action').value='killss';submit();" />
			<?php }else { ?>
				<b><?php echo _('Status') ?>:</b> <?php echo str_replace('$SERVICENAME', $ss->viewname, _('$SERVICENAME not running')) ?></p>
				<input type="button" value="<?php echo str_replace('$SERVICENAME', $ss->viewname, _('start $SERVICENAME')) ?>" name="start" onclick="document.getElementById('action').value='startss';submit();" />	
			<?php } ?>
		<input type="submit" onclick="document.getElementById('squeezeslavedetails').style.display='';return false;" value="<?php echo _('Edit Advanced Options') ?>" />
		<div id="squeezeslavedetails" class="optiondetails" style="display:none;">
			<table>
				<tr>
					<td><?php echo _('Soundcard') ?></td>
					<td><select name="soundcard">
						<option value='-n dmixer'>Default dmixer (Alsa Mixer Device)</option>
						<option value='-n plugequal'>Use Equalizer</option>
						</select>
					</td>
					<td><?php echo _('Select Audio output') ?></td>
				</tr>
			</table>
			<input type="button" name="save" value="<?php echo _('save') ?>" onclick="document.getElementById('action').value='save';submit();" />
		</div>
		<br /><br /><b><?php echo str_replace('$SERVICENAME', $ss->viewname, _('Autostart $SERVICENAME')) ?>:</b><br />
		<input type="checkbox" <?php if($ss->view->autostart) echo "checked"; ?> value="1" name="autostartss" />
		<input type="button" value="<?php echo _('save') ?>" name="save" onclick="document.getElementById('action').value='save';submit();" />
		
		<br />
	
		<?php foreach($sp->equalvalues as $key => $value) { ?>
			<input type="hidden" id="<?php echo $key ?>" name="settingsEqualizer[<?php echo $key ?>]" value="<?php echo $value; ?>">
		<?php } ?>
		
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-top:3em;">
			<span class="ui-icon ui-icon-signal" style="float:left; margin:-2px 5px 0 0;"></span>
			<?php echo _('Graphic Equalizer') ?>
		</p>
		<div id="eq">
			<?php foreach($sp->equalvalues as $key => $value) { ?>
				<span id="<?php echo $key ?>"><?php echo $value ?></span>
			<?php } ?>
		</div>
		<br />
		<input type="button" value="<?php echo _('Zurücksetzen') ?>" name="save" onclick="document.getElementById('action').value='resetEqualizer';submit();" /><br /><br />
		<input type="button" value="<?php echo _('Speichern') ?>" name="save" onclick="document.getElementById('action').value='saveEqualizer';submit();" /><br />
		<br />
		<input type="text" size="2" value="" id="selectedEqual" /> <span id="selectedEqualSpan"></span>
		<br /><br />
	</form>
	
	<br /><br /><br />
	<?php echo _('SQUEEZEPLAYER INFO DESCRIPTION') ?>
</div>	
															