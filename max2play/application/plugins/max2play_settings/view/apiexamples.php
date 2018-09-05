<?php
/**
 Api Examples View File

 @Copyright 2018 Stefan Rick
 @author Stefan Rick
 Mail: stefan@rick-software.de
 Web: http://www.netzberater.de

 */
?>


<h1 class="entry-header">
	<?php echo _("API Examples for Max2Play") ?>
</h1>
<div class="entry-content">
	<?php if(isset($apiexamples->view->message[0])){ ?>
		<div class="ui-widget">
			<div class="ui-state-highlight ui-corner-all" style="margin-bottom: 10px; padding: 0.4em .7em;">
				<p>
					<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<?php echo implode('<br />', $apiexamples->view->message); ?>					
				</p>
			</div>
		</div>
	<?php } ?>
			
	<?php echo _('HINT: Use the Max2Play OpenHAB Plugin as a powerful homeautomation server that already comes with support for Squeezebox Server, Alexa, Google Home and offers an easy integration in existing homeautomatisation environments.');?>
	<br /><br />	
	
	 <p id="examples" class="ui-state-default ui-corner-all" style="padding:4px;margin-bottom:1em;">
						<span class="ui-icon ui-icon-wrench" style="float:left; margin:-2px 5px 0 0;"></span>
						<b><?php echo _("Understanding how to call Actions via HTTP-Get Request or CLI (Squeezebox Server)") ?></b></p>	 
	 <?php echo _('Just like calling a URL in your Browser you can call URLs from different Apps. The Apps can be "Tasker" on Android Smartphone or "HTTPDevice" App in Z-Wave-Server or any other program. Following HTTP-URLs can be used as an example to get an idea how to control Max2Play and your Squeezebox Server and all other devices in your local network that offer HTTP-Requests.')?>	 
	 
	 <br /><br />
	 <style>
	 	.copyurl {
	 	background-color: #f0f8ff;
	    border: 1px solid #cee1ef;
	    color: #1891a8;
	    font-family: "source_sans_proregular",Arial,Helvetica,Tahoma,sans-serif;
	    padding: 10px;
	    overflow: auto;
    	white-space: pre;
	    }
	    #tabs-min, #systemselect {
		    color: #444444;
		    font-family: Helvetica,Arial,sans-serif;
		    font-size: 1rem;		    
		    font-family: "Open Sans",Helvetica,Arial,sans-serif;
		}
		#tabs-min { 
		    background: transparent; 
		    border: none; 
		} 
		#tabs-min .ui-widget-header { 
		    background: transparent; 
		    border: none; 
		    border-bottom: 1px solid #c0c0c0; 
		    -moz-border-radius: 0px; 
		    -webkit-border-radius: 0px; 
		    border-radius: 0px; 
		} 
		#tabs-min .ui-tabs-nav .ui-state-default { 
		    background: transparent; 
		    border: none; 
		} 
		#tabs-min .ui-tabs-nav .ui-state-active { 
		    background: transparent url(/css/ui-lightness/images/uiTabsArrow.png) no-repeat bottom center; 
		    border: none; 
		} 		
	 </style>
	 <div id="tabs-min">
	  <ul>
	    <li><a href="#tabs-1">Squeezebox Control</a></li>
	    <li><a href="#tabs-2">Kodi</a></li>
	    <li><a href="#tabs-3">Jivelite</a></li>
	    <li><a href="#tabs-4">Max2Play Commands</a></li>
	  </ul>
	  <div id="tabs-1">
	    <p>
	    Squeezebox Server is identified with IP-Address or network-name and each Player is identified by its MAC address. Be aware that when switching from LAN to WLAN the MAC address of your player changes because of the new interface.<br />
	    You have two options to control the Squeezebox Server and its players: the HTTP-GET API and the Command Line Interface. Both have detailled examples for your setup below.
	    <br /><br />
	    </p>
	    <p>
	    <?php if ($apiexamples->view->realsbsip){ ?>
	    	Current Squeezebox-Server is found on IP <b><?php echo $apiexamples->view->realsbsip; ?></b><br />
	    	<script>
	    		var SBSIP = "<?php echo $apiexamples->view->realsbsip; ?>"; 
		    </script>
	    <?php }else { ?>
	    	<script>
	    		var SBSIP = "<?php echo $_SERVER['HTTP_HOST']; ?>";
		    </script>
	    <?php }?>
	    <?php if ($apiexamples->view->sbsplayers){ ?>
	    	Choose Player to create the example commands for:
	    	<select id="sbsplayerselect" name="sbsplayerselect">
	    	<?php foreach($apiexamples->view->sbsplayers as $player) {?>
	    		<option value="<?php echo $player['mac']?>"><?php echo $player['name']?></option>
	    	<?php }?>
	    	</select>
	    	<br /><br />
	    	<script>				
			    function updateExampleURLs(mac){
			    	$( "#httpplay" ).html("http://" + SBSIP + ":9000/status.html?p0=play&player=" + mac);
			    	$( "#httppause" ).html("http://" + SBSIP + ":9000/status.html?p0=pause&player=" + mac);
			    	$( "#httpstatus" ).html("http://" + SBSIP + ":9000/status.html?p0=status&player=" + mac);
			    	$( "#httpvolumeup" ).html("http://" + SBSIP + ":9000/status.html?p0=mixer&p1=volume&p2=%2B10&player=" + mac);
			    	$( "#httpvolumedown" ).html("http://" + SBSIP + ":9000/status.html?p0=mixer&p1=volume&p2=-10&player=" + mac);
			    	$( "#clipoweron" ).html('printf "' + decodeURIComponent(mac) + ' power 1\\nexit\\n" | nc ' + SBSIP + ' 9090 2>/dev/null | cut "-d " -f 3');
			    	$( "#clistatus" ).html('printf "' + decodeURIComponent(mac) + ' status\\nexit\\n" | nc ' + SBSIP + ' 9090 2>/dev/null');
			    	$( "#cliplay" ).html('printf "' + decodeURIComponent(mac) + ' mode play\\nexit\\n" | nc ' + SBSIP + ' 9090 2>/dev/null | cut "-d " -f 3');
			    	$( "#clipause" ).html('printf "' + decodeURIComponent(mac) + ' mode pause\\nexit\\n" | nc ' + SBSIP + ' 9090 2>/dev/null | cut "-d " -f 3');
				}
    			$( "#sbsplayerselect" ).change(function() {
	    		  alert( "Documentation updated with selected player "+ (this.value));
	    		  updateExampleURLs(this.value);
	    		});	    		
	    	</script>
	    <?php } ?>
	    </p>
	    <h3>HTTP-GET API (e.g. simple call in browser URL)</h3>
	    <p>
	    META URL for HTTP-GET Requests: <br />
	    <div class="copyurl">http://SQUEEBOXSERVERIP:PORT/status.html?p0=play&player=MACADDRESS</div><br />
	    Start Playing: <br />
	 	<div id="httpplay" class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>:9000/status.html?p0=play&player=<?php echo $apiexamples->view->hwaddress ?></div><br />
	 	Player Pause: <br />
	 	<div id="httppause" class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>:9000/status.html?p0=pause&player=<?php echo $apiexamples->view->hwaddress ?></div><br />
	 	Get Status for Player (in Z-Wave-Server Status Field): <br />
	 	<div id="httpstatus" class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>:9000/status.html?p0=status&player=<?php echo $apiexamples->view->hwaddress ?></div><br />
	 	Set Volume for Player (UP +10): <br />
	 	<div id="httpvolumeup" class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>:9000/status.html?p0=mixer&p1=volume&p2=%2B10&player=<?php echo $apiexamples->view->hwaddress ?></div><br />	 	
	 	Set Volume for Player (DOWN -10): <br />
	 	<div id="httpvolumedown" class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>:9000/status.html?p0=mixer&p1=volume&p2=-10&player=<?php echo $apiexamples->view->hwaddress ?></div><br />	 	
	 	
	 	Additionally this code is needed to parse the status in Homekit ("Inline Javascript to parse incoming data to 'on'/'off' strings" in Z-Wave-Server)<br />
	 	<div class="copyurl">str.indexOf("&lt;b&gt;Play&lt;/b&gt;") > 0 ? 'on' : 'off'</div>Change "Play" with "Wiedergabe" when your Squeezebox Server is running in German.<br /><br />
	 	Some more examples:<br />
	 	<a href="http://tutoriels.domotique-store.fr/content/54/95/fr/api-logitech-squeezebox-server-_-player-http.html" target="_blank">http://tutoriels.domotique-store.fr/content/54/95/fr/api-logitech-squeezebox-server-_-player-http.html</a>
	 	<br /><br />
	 	</p>	 	
	 	<h3>Using the Command Line Interface of Squeezebox Server</h3>
	 	<p>
	 	Command Line Interface documentation for Squeezebox Server CLI can be found here (if running Squeezebox Server on this device)<br />
	 	<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>:9000/html/docs/cli-api.html" target="_blank">http://<?php echo $_SERVER['HTTP_HOST'] ?>:9000/html/docs/cli-api.html</a><br /><br />
	 	The CLI-API can be used or tested with netcat, a simple command line tool available on this device. Following examples show the usage with <i>nc</i> started from the console (bash) and how to get the return value of the API:<br />
	 	Set player Power ON: <br />
	 	<div id="clipoweron" class="copyurl">printf "<?php echo $apiexamples->view->hwaddress ?> power 1\\nexit\\n" | nc <?php echo $_SERVER['HTTP_HOST'] ?> 9090 2>/dev/null | cut "-d " -f 3</div><br />
	 	Get player Lastmode: <br />
	 	<div id="clistatus" class="copyurl">printf "<?php echo $apiexamples->view->hwaddress ?> status\\nexit\\n" | nc <?php echo $_SERVER['HTTP_HOST'] ?> 9090 2>/dev/null</div><br />
	 	Start Playing: <br />
	 	<div id="cliplay" class="copyurl">printf "<?php echo $apiexamples->view->hwaddress ?> mode play\\nexit\\n" | nc <?php echo $_SERVER['HTTP_HOST'] ?> 9090 2>/dev/null | cut "-d " -f 3</div><br />
	 	Pause Playing: <br />
	 	<div id="clipause" class="copyurl">printf "<?php echo $apiexamples->view->hwaddress ?> mode pause\\nexit\\n" | nc <?php echo $_SERVER['HTTP_HOST'] ?> 9090 2>/dev/null | cut "-d " -f 3</div><br />	 	
	 	<br />
	 		 	 	
	 	<?php if ($apiexamples->view->sbsplayers){ ?>	    	
	    	<script>							  
	    		// Default to first Player
	    		updateExampleURLs("<?php echo $apiexamples->view->sbsplayers[0]['mac']?>");	    		
	    	</script>
	    <?php } ?>
	    </p>
	  </div>
	  <div id="tabs-2">
	    <p>
	    Start Kodi:<br /> 
	 		<div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Xbmc.php?action=start</div><br /><br />
	 	Stop Kodi: <br />
	 		<div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Xbmc.php?action=stop</div><br /><br />
	    </p>
	  </div>
	  <div id="tabs-3">
	    <p>
	    Start Jivelite:<br /> 
	 		<div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/jivelite/controller/Setup.php?action=start</div><br /><br />
	 	Stop Jivelite: <br />
	 		<div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/jivelite/controller/Setup.php?action=stop</div><br /><br />
	    </p>	    
	  </div>
	  
	 <div id="tabs-4">
	    <p>
	    Shutdown this Device: <br />
	 	  <div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Basic.php?action=shutdown</div><br />
	 	Restart Squeezebox Server: <br />
	 	  <div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Squeezeserver.php?action=restart</div><br />  
	 	Stop Squeezelite Player: <br />
	 	  <div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Squeezeplayer.php?action=stop</div><br />
	 	Start Squeezelite Player: <br />
	 	  <div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Squeezeplayer.php?action=start</div><br />
	 	Start Shairport Player: <br />
	 	  <div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Squeezeplayer.php?action=startap</div><br />
	 	Stop Shairport Player: <br />
	 	  <div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/plugins/max2play_settings/controller/Squeezeplayer.php?action=stopap</div><br />
	 	API-Doc for Equalizer: <br />
	 	  <div class="copyurl">http://<?php echo $_SERVER['HTTP_HOST'] ?>/apidoc/</div><br />
	 	<br />
	 	HINT: A lot of functionality with HTTP-GET can be found by executing the task in this web interface and check the browser URL for the parameter (mostly the "action" parameter).   
	 	</p>
	  </div>
	</div>
	
	  <script>
	  $(function() {
	    $( "#tabs-min" ).tabs();
	  });
	  </script>
	  
	 <br /><br />
</div>