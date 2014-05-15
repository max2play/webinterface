<?php
/**
	 Header HTML
	
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
	<link rel='stylesheet' id=''  href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700&#038;subset=latin,latin-ext' type='text/css' media='all' />
	<link rel='stylesheet' id=''  href='/style.css' type='text/css' media='all' />
</head>

<body>
	<div id="page">
		<header>		
			<a href="/" class="logo" title="Max2Play - Your Media Solution"></a>			
			
			<div class="chooseplayer">
				<?php if($service->info->devices) { ?>
					<?php echo _('Choose active Player')?>
					<select name="chooseplayer">
						<?php foreach($service->info->devices as $device) { ?>
							<option <?php if($device['current'] == 1) echo 'selected'; ?> onclick="window.open('http://<?php echo $device['ip'] ?>');" value="<?php echo $device['ip'] ?>"><?php echo $device['name'] ?> (<?php echo $device['ip'] ?>)</option>
						<?php }?>		
					</select>
				<?php }?>
				<br /><br /><span style="float:right";><?php echo _('Version').' '.$service->info->version ?></span>
			</div>
			
			<nav id="navigation" class="navigation">					
				<div class="nav-menu">
					<ul>
						<li><a href="/" title="Home" class="<?php if($_SERVER['REQUEST_URI'] == '/') echo 'current';?>"><?php echo _('Start')?></a></li>
						<?php if(isset($plugins['plugin']) && count($plugins['plugin']) > 0) { 							
							foreach($plugins['plugin'] as $plugin){ 
								if(isset($plugin['navigation']) && $plugin['navigation'] != ''){?>
									<li><a href="<?php echo $plugin['path'] ?>" class="<?php if(strpos($_SERVER['REQUEST_URI'], $plugin['path']) !== FALSE) echo 'current';?>"><?php echo (isset($plugin['navigation']['translate'])) ? _($plugin['navigation']['translate']) : _($plugin['navigation']) ?></a></li>
						  <?php }
							}							
						}?>
												
					</ul>
				</div>
			</nav>
	
		</header>
	
		<div id="main" class="wrapper">
			<div id="primary" class="site-content">
				<div id="content" role="main">	