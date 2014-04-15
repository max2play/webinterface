<?php
/**
	 Index Page
	
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

<?php include_once('../application/controller/Service.php') ?>

<?php include_once('header.php') ?>														

<h1 class="entry-header">
	<?php echo _("Max2Play Media Player - Info") ?>
</h1>
	
<div class="entry-content">
	<b><?php echo _("READ CAREFULLY: THIS INTERFACE IS STILL A BETA AND MIGHT HAVE BUGS OR UNEXPECTED BEHAVIOR!") ?></b>
	<br /><br />
	<?php echo _("The Max2Play Interface is used to configurate the device without having to set up montior, keyboard and mouse on the device itsself or login by ssh.<br />All settings are directly written to the players config files."); ?><br />  
	<br />
	<?php echo _('Questions concerning this project? Please refer to <a href="http://shop.max2play.com" target="_blank">Shop.Max2Play</a>') ?><br />
	
	
</div>	
															
<?php include_once('footer.php') ?>				