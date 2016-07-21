<?php
/**
	 Example Setup View File
	
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
	<?php echo _("Exampleclass Setup") ?>
</h1>
<div class="entry-content">
	<?php if(isset($exampleclass->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $exampleclass->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
	
	<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _("This is the Example Setup Page. Configure and Test your own Plugin :)") ?></b></p>
		
	<br />
	<br />
	
	<?php echo _("Example Variable: ") ?><?php echo $exampleclass->view->example ?>
	
	<form id="form1" action="" method="get">
		<input type="hidden" id="action" name="action" value="" />
		
		<?php /*
			This is a comment
			Put everything that should be sent inside this form - you may have a look at the other files of max2play and copy / paste their content		
			*/?>
		
		<div class="optiondetails"> 
			<table class="settings">
			  <tr>
				<td><?php echo _("Example Button") ?></td>
				<td><input type="button" id="doSomething" name="doSomething" onclick="document.getElementById('action').value='doSomething';submit();" value="<?php echo _("Do Something") ?>" /></td>
				<td><?php echo _("Do something to get things done ...") ?></td> 
			  </tr>	 
			  <tr>
				<td><?php echo _("Example Input") ?></td>
				<td><input type="text" id="inputtext" name="inputtext" value="<?php echo $exampleclass->inputtext ?>" /></td>
				<td><?php echo _("Well - explain what this should do...") ?></td> 
			  </tr>	 
			</table>
			
			<input type="button" id="save" name="<?php echo _("save") ?>" onclick="document.getElementById('action').value='save';submit();" value="<?php echo _("save") ?>" /></td>		
		</div>
		<br />
		
		
		<?php /*
			Tab navigation	
			*/?>
		<p class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
			<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
			<b><?php echo _t('This is an example of a tab navigation within the page:') ?></b></p>				
		<div id="tabs-min">
		  <ul>
		    <li><a href="#tabs-1"><?php echo _t('name of tab 1') ?></a></li>
		    <li><a href="#tabs-2"><?php echo _t('name of tab 2') ?></a></li>	    
		  </ul>
		  <div id="tabs-1">
		    <p>
		    	<?php echo _t('Enter something here')?> <input type="text" id="installpath" name="installpath" value="" />	
				<input type="button" value="<?php echo _("Install or do something") ?>" name="installplugin" onclick="document.getElementById('action').value='installplugin';submit();" /><br />	    	 
		 	</p>
		  </div>
		  <div id="tabs-2">
		    <p>
		      <?php echo _('Custom tab content 2');?>: 			  
			  <input type="button" value="<?php echo _t("Do something else...") ?>" name="installplugin" onclick="document.getElementById('action').value='installplugin';submit();" /><br />		     
		    </p>
		  </div>	  
		</div>
	
		  <script>
		  $(function() {
		    $( "#tabs-min" ).tabs();
		  });
		  </script>
		  
		  
		<?php /*
			Accordion Example	
			*/?>
		
		<div id="accordion" style="max-height:500px;">
		  <h3><?php echo _t('First Row in Accordion') ?></h3> 
		  <div>
		    <p>
		    	<?php echo _t('Do something nice...') ?>
		    	<a class="button-small clickloading" href="#" onclick="document.getElementById('action').value='save_purpose';$('#form1').submit();"><?php echo _("Push the button") ?></a>
		    </p>
		  </div>
		  <h3><?php echo _t('Second Row ') ?></h3>
		  <div>
		    <p>
		    	<?php echo _('This is easy - isn\'t it?') ?>
		    	<ul class="description">
		    		<li><?php echo _t('One Point'); ?></li>
		    		<li><?php echo _t('Second Point'); ?></li>
		    	</ul>		    	
		    	<a class="button-small clickloading" href="#" onclick="document.getElementById('action').value='save_purpose';$('#form1').submit();"><?php echo _("Let's do it!") ?></a>
		    
		    </p>
		  </div>
		  <h3><?php echo _t('Third Row') ?></h3>
		  <div>
		    <p>
		    <?php echo _t('Running out of ideas...') ?>		    			   
		    </p>
		  </div>
		</div>	
	
	 <script>
	  $(function() {
	    $( "#accordion" ).accordion({
	      heightStyle: "content",	      
	      collapsible: true 
	    });   
	  });  
 	 </script>
		
	</form>
	
	<br /><br />
	<a href="#javascript" onclick="document.getElementById('debug').style.display='';return false;"><?php echo _("DEBUG Informations") ?></a>
	<textarea id="debug" rows="30" cols="70" style="display:none;"><?php foreach ($exampleclass->view->debug as $key => $debug) {
			echo "#### ". $key. " ####\n"; 
			 echo $debug." \n\n"; 
		 }?>
	</textarea>
</div>	