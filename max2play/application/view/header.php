<?php
/**
	 Header HTML
	 Create Custom Header if needed in this directory: header_custom.php 
	
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
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title>Max2Play | Media Solution - <?php echo $service->info->playername; ?></title>
	<meta name='robots' content='noindex,nofollow' />
	<meta name='viewport' content='width=device-width,initial-scale=1.0'>
	<link href="/css/ui-lightness/jquery-ui-1.10.4.custom.min.css" rel="stylesheet">
	<script src="/js/jquery-1.10.2.js"></script>
	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript" src="/js/reloadprogress.js?v=<?php echo $service->info->version?>"></script>	
	<link rel='stylesheet' id=''  href='/style-new.css?v=<?php echo $service->info->version?>' type='text/css' media='all' />
	<?php if(file_exists(APPLICATION_PATH.'/../public/custom.css')) { ?>
		<link rel='stylesheet' id=''  href='/custom.css' type='text/css' media='all' />
	<?php } ?>
	<link rel="icon" href="/favicon.ico" type="image/x-icon" />
	<script>$(document).on("click", "input[type=button]", function() { $("body").addClass("loading");});
			$(document).on("click", ".clickloading", function() { $("body").addClass("loading");});
	</script>
</head>

<body>
	<div id="page">
		<header>		
			<a href="/" class="logo" title="Max2Play - Your Media Solution"></a>			
			
			<div class="chooseplayer">
				<?php if($service->info->devices) { ?>
					<?php //echo _('Choose active Player')?>
					<select name="chooseplayer" class="chooseplayer" style="width: 84%;margin-bottom:5px;" onChange="window.open(this.options[this.selectedIndex].value,'_self');">
						<?php foreach($service->info->devices as $device) { ?>
							<option <?php if($device['current'] == 1) echo 'selected'; ?> value="http://<?php echo $device['ip'] ?>"><?php echo $device['name'] ?> (<?php echo $device['ip'] ?>)</option>
						<?php }?>		
					</select>
					<a class="button-small ui-state-default ui-corner-all clickloading" title="Refresh Device List" style="float:left;background-image: none; background-color: rgb(255, 255, 255);padding: 0;" href="<?php echo "//{$_SERVER['HTTP_HOST']}".strtok($_SERVER["REQUEST_URI"],'?').'?refreshDeviceList=1'; ?>" >
						<span class="ui-icon ui-icon-refresh"></span>
					</a>
				<?php }?>				
				<span class="headerinfo">					
					<?php echo _('Version').' '.$service->info->version ?>
					<br /><?php echo ($service->info->boardname) ? $service->info->boardname : $service->info->hardware; ?>
					
					<?php if($service->checkLicense(true, true) == true){ ?>
						<div class="ui-state-disabled ui-corner-all" style="margin-top:5px">				
							<span class="ui-icon ui-icon-circle-check" style="float: left; margin-right: .3em;"></span>
								<?php echo _('License Activated'); ?>
						</div>
					<?php } else { ?>
						<a href="/plugins/max2play_settings/controller/Basic.php" style="text-decoration:none;">
							<div class="ui-state-error ui-corner-all" style="margin-top:5px">				
								<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
									<?php echo _('Not Activated'); ?>
							</div>
						</a>
					<?php } ?>
				</span>
			</div>						
	
		</header>
		<nav id="navigation" class="navigation">					
			<div id="hiddennav" class="nav-menu invisible">
				<ul id="navlist">						
					<?php if(isset($service->plugins['plugin']) && count($service->plugins['plugin']) > 0) { 							
						foreach($service->plugins['plugin'] as $plugin){ 
							if(isset($plugin['navigation']) && $plugin['navigation'] != '' && isset($plugin['active']) && $plugin['active'] == 1){?>
								<li><a href="<?php echo $plugin['path'] ?>" 
									   class="<?php if(strpos($_SERVER['REQUEST_URI'], $plugin['path']) !== FALSE || $_SERVER['REQUEST_URI'] == '/' && isset($plugin['default']) && $plugin['default'] == 1) echo 'current';?>"
									   ><?php echo (isset($plugin['navigation']['translate'])) ? _($plugin['navigation']['translate']) : _($plugin['navigation']) ?></a>
							    </li>
					  <?php }
						}							
					}?>
											
				</ul>
			</div>
			<div id="scrollingnav" class="nav-menu ">
				<ul id="navlist">						
					<?php if(isset($service->plugins['plugin']) && count($service->plugins['plugin']) > 0) { 							
						foreach($service->plugins['plugin'] as $plugin){ 
							if(isset($plugin['navigation']) && $plugin['navigation'] != '' && isset($plugin['active']) && $plugin['active'] == 1){?>
								<li><a href="<?php echo $plugin['path'] ?>" 
									   class="<?php if(strpos($_SERVER['REQUEST_URI'], $plugin['path']) !== FALSE || $_SERVER['REQUEST_URI'] == '/' && isset($plugin['default']) && $plugin['default'] == 1) echo 'current';?>"
									   ><?php echo (isset($plugin['navigation']['translate'])) ? _($plugin['navigation']['translate']) : _($plugin['navigation']) ?></a>
							    </li>
					  <?php }
						}							
					}?>
											
				</ul>
			</div>
			<script>
			$(function() {			    
			    var header = $("#scrollingnav");
			    var hiddennav = $("#hiddennav");
			    $(window).scroll(function() {
			        var scroll = $(window).scrollTop();
			        if (scroll >= 120) {
			            header.addClass('navfixed');
			            hiddennav.removeClass('invisible');
			        }else {
			            header.removeClass('navfixed');
			            hiddennav.addClass('invisible');
			        }			        
			    });
			});			
			</script>
		</nav>
		
		<div id="toppicture" style="display:none;"> </div>
		
		<div id="main" class="wrapper">
			<div id="primary" class="site-content">
				<div id="content" role="main">	
				  <a href="<?php echo "//{$_SERVER['HTTP_HOST']}".strtok($_SERVER["REQUEST_URI"],'?'); ?>" class="button-small ui-state-default ui-corner-all" style="float:right;background-image: none;background-color:#fff;" title="Reload Page"><span class="ui-icon ui-icon-refresh"></span></a>
	