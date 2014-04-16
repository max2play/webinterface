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
			</div>
			
			<nav id="navigation" class="navigation">					
				<div class="nav-menu">
					<ul>
						<li><a href="/" title="Home" class="<?php if($_SERVER['REQUEST_URI'] == '/') echo 'current';?>"><?php echo _('Start')?></a></li>
						<li><a href="/Squeezeplayer.php" class="<?php if(strpos($_SERVER['REQUEST_URI'],'Squeezeplayer') !== FALSE) echo 'current';?>"><?php echo _('Audioplayer')?></a></li>
						<li><a href="/Squeezeserver.php" class="<?php if(strpos($_SERVER['REQUEST_URI'],'Squeezeserver') !== FALSE) echo 'current';?>"><?php echo _('Squeezeserver')?></a></li>
						<li><a href="/Xbmc.php" class="<?php if(strpos($_SERVER['REQUEST_URI'],'Xbmc') !== FALSE) echo 'current';?>"><?php echo _('XBMC (Mediacenter)')?></a></li>
						<li><a href="/Filesystem.php" class="<?php if(strpos($_SERVER['REQUEST_URI'],'Filesystem') !== FALSE) echo 'current';?>"><?php echo _('Filesystem Mount')?></a></li>
						<li><a href="/Wlan.php" class="<?php if(strpos($_SERVER['REQUEST_URI'],'Wlan') !== FALSE) echo 'current';?>"><?php echo _('WiFi & LAN')?></a></li>
						<li><a href="/Basic.php" class="<?php if(strpos($_SERVER['REQUEST_URI'],'Basic') !== FALSE) echo 'current';?>"><?php echo _('Reset & Reboot')?></a></li>
					</ul>
				</div>
			</nav>
	
		</header>
	
		<div id="main" class="wrapper">
			<div id="primary" class="site-content">
				<div id="content" role="main">	