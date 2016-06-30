<?php
/**
	 Footer HTML
	 Create Custom Footer if needed in this directory: footer_custom.php
	 
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

</div>
			</div>
			<div id="secondary" class="widget-area">
			
			<?php if (isset($helpOnSidebar) && $helpOnSidebar !== false && isset($helpOnSidebar['toppicture'])){ ?>
				<script>
					document.getElementById('toppicture').style.backgroundImage='url("<?php echo $helpOnSidebar['toppicture']?>")';
					document.getElementById('toppicture').style.display='';
				</script>
			<?php }?>
			
			<?php if (isset($helpOnSidebar) && $helpOnSidebar !== false && isset($helpOnSidebar['content'])){ ?>			
				<script>
					function closeGuide(){
						$("#guide").display='none';
						window.alert('<?php echo _('Help on Sidebar can be reactivated on Settings-Page.') ?>');
						window.open(document.URL+'?closeguide=1&closeguide=1');
					}
				</script>
				<aside id="guide" class="widget guide" style="margin-bottom: 10px;">
 				   <h3 class="widget-title" style="background: #e8641b;color: #ffffff;text-align: center;"><?php echo $helpOnSidebar['title'] ?><span style="float: right; margin-right: .3em;" class="ui-icon ui-icon-close" onclick="closeGuide();"></span></h3>
				   <?php echo $helpOnSidebar['content'] ?>    
				</aside>
			<?php } ?>
				
				<aside class="widget">
					<h3 class="widget-title"><?php echo _('Topics') ?></h3>
					<ul>
						<li><a href="/" title="Info"><?php echo _('Features') ?></a></li>	
						<li><a href="<?php echo _('http://www.max2play.com/faq/') ?>" target="_blank" title="FAQ"><?php echo _('FAQ - Questions & Answers') ?></a></li>
						<li><a href="<?php echo _('http://www.max2play.com/en/addons/') ?>" target="_blank" title="Addons"><?php echo _('Addons and Feature Requests') ?></a></li>
						<li><a href="<?php echo _('http://www.max2play.com/en/forums/') ?>" target="_blank" title="Support"><?php echo _('Technical Support') ?></a></li>
					</ul>
				</aside>

				<aside class="widget">
					<h3 class="widget-title"><?php echo _('News') ?></h3>
					<?php include_once(APPLICATION_PATH.'/controller/News.php'); echo $news->getNews(0); ?>
				</aside>

			</div>
		</div>
		
		<div class="divider"> </div>
		<footer>
			<div class="site-info">
				<br /><br />
				<?php echo _('Max2Play - <a target="_blank" href="http://www.max2play.com/en/features/" title="Powered by Max2Play">the <i>easy to use</i> webinterface and framework for mini-computers</a>!'); ?>
				<br /><br />
			</div>
		</footer>
	</div>
	<div class="modal"></div>

	<?php include_once(APPLICATION_PATH.'/view/debug.php');?>
	
	<?php if($_REQUEST['action'] == 'reboot'){ 
		//to preload loading-gif on Restart	?>
		<div id="preload"> </div>
		<script type="text/javascript">
			$('#preload').css('background', "url(http://<?php echo $_SERVER['SERVER_ADDR']; ?>/css/wait.gif) no-repeat -9999px -9999px");
		</script>
	<?php } ?>
</body>
</html>