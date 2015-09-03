<input type="hidden" id="purpose" name="purpose" value="" />
		<h1 class="entry-header"><?php echo _t('Choose your preferred setup and purpose of Max2Play'); ?></h1>
		<?php echo _t('You may change all settings later, but for the first start, it is suggested to select something. The mediacenter Kodi is available and preinstalled in every setup.') ?><br /><br />
		<div id="purposeselect" style="max-height:500px;">
		  <h3><?php echo _t('Starter: A simple audioplayer for internet radio and local music (mp3)') ?></h3> 
		  <div>
		    <p>
		    	<?php echo _t('You want a simple way to play music from online radio streams and local sources (e.g. USB-drives or network storage)') ?>
		    	<ul class="description positive">
		    		<li><?php echo str_replace('|', '</li><li>', _t("!PURPOSESELECT_SIMPLE_MPD_POSITIVE")); ?></li>
		    	</ul>
		    	<ul class="description negative">
		    		<li><?php echo str_replace('|', '</li><li>', _t("!PURPOSESELECT_SIMPLE_MPD_NEGATIVE")); ?></li>
		    	</ul>
		    	<a class="button-small clickloading" href="#" onclick="document.getElementById('action').value='save_purpose';document.getElementById('purpose').value='mpd';$('#form1').submit();"><?php echo _("Select this setup!") ?></a>
		    </p>
		  </div>
		  <h3><?php echo _t('Advanced: A real multiroom audio player that integrates in a Squeezebox environment with powerful options for high-quality audio and a lot of features.') ?></h3>
		  <div>
		    <p>
		    <?php echo _t('Using Squeezebox-technology you have a squeezebox server running on one of your devices and use Squeezelite as audioplayer.') ?>
		    	<ul class="description positive">
		    		<li><?php echo str_replace('|', '</li><li>', _t("!PURPOSESELECT_SIMPLE_SQUEEEZE_POSITIVE")); ?></li>
		    	</ul>
		    	<ul class="description negative">
		    		<li><?php echo str_replace('|', '</li><li>', _t("!PURPOSESELECT_SIMPLE_SQUEEEZE_NEGATIVE")); ?></li>
		    	</ul>
		    	<a class="button-small clickloading" href="#" onclick="document.getElementById('action').value='save_purpose';document.getElementById('purpose').value='squeeze';$('#form1').submit();"><?php echo _("Select this setup!") ?></a>
		    
		    </p>
		  </div>
		  <h3><?php echo _t('Simple Airplay: Apple Airplay Device, that plays music streamed from iPhone, iPad or Mac.') ?></h3>
		  <div>
		    <p>
		    <?php echo _t('Just one player (shairport) is running. No additional setup needed.') ?>
		    	<ul class="description positive" >
		    		<li><?php echo str_replace('|', '</li><li>', _t("!PURPOSESELECT_SIMPLE_SHAIRPORT_POSITIVE")); ?></li>
		    	</ul>
		    	<ul class="description negative">
		    		<li><?php echo str_replace('|', '</li><li>', _t("!PURPOSESELECT_SIMPLE_SHAIRPORT_NEGATIVE")); ?></li>
		    	</ul>
		    	<a class="button-small clickloading" href="#" onclick="document.getElementById('action').value='save_purpose';document.getElementById('purpose').value='airplay';$('#form1').submit();"><?php echo _("Select this setup!") ?></a>
		    
		    </p>
		  </div>
		</div>	
	
	 <script>
	  $(function() {
	    $( "#purposeselect" ).accordion({
	      heightStyle: "content",
	      <?php if($hifiberry->view->purpose != false) echo "active: false,"; ?>
	      collapsible: true 
	    });   
	  });  
 	 </script>