<?php 

/**
   Basic Administration Controller
    
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

class Basic extends Service {		
	public $timezonesfile = '/opt/max2play/timezones.txt';
	
	public function __construct(){										
		parent::__construct();
		$this->pluginname = _('Settings / Reboot');
		
		$this->view->locales = array('deutsch / Deutschland' => 'de_DE.UTF-8', 'deutsch / Schweiz' => 'de_CH.UTF-8', 'english' => 'en_GB.UTF-8', 'italiano' => 'it_IT.UTF-8','français' => 'fr_FR.UTF-8', 'türkçe' => 'tr_TR.UTF-8', 'nederlands' => 'nl_NL.UTF-8', 'español' => 'es_ES.UTF-8','português' => 'pt_PT.UTF-8');
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'reboot'){
				shell_exec('sudo /sbin/reboot -n');
				$this->view->message[] = _("REBOOT gestartet");
			}
			
			if($_GET['action'] == 'reset'){
				$this->view->message[] = $this->resetFactoryDefaults();
			}
			
			if($_GET['action'] == 'shutdown'){
				$this->view->message[] = _("Shutdown initiated - press the power button on device to start it again");
				$this->view->message[] = $this->writeDynamicScript(array('poweroff'));				
			}
			
			if($_GET['action'] == 'expandfs'){
				$this->resizeFS();
			}
			
			if($_GET['action'] == 'fixusbmount'){
				$this->fixUsbMount();
			}
			
			if($_GET['action'] == 'checkMax2PlayUpdate'){
				$this->checkMax2PlayUpdate();
			}
			if($_GET['action'] == 'checkMax2PlayBetaUpdate'){
				$this->checkMax2PlayUpdate('beta');
			}
			
			if($_GET['action'] == 'pluginconfig'){
				$this->view->message[] = $this->pluginConfig($_GET['activeplugin'], $_GET['defaultplugin']);
				$this->loadViewHeader(true);
			}
			if($_GET['action'] == 'installplugin'){				
				$this->installPlugin($_GET['installplugin']);
			}
			
			if($_GET['action'] == 'save'){
				if(isset($_GET['playername']))
					$this->updatePlayername($_GET['playername']);
				if(isset($_GET['locale']))
					$this->updateLocale($_GET['timezone'], $_GET['locale']);
				
				$this->setDonateButton((isset($_GET['removedonate']) ? 1 : 0));
				$this->updateEmail($_GET['email']);
				$this->updateDisplayResolution($_GET['displayResolution']);
				$this->updateMax2playNetworkLookup();				
			}
		}
		$this->getLocale();
		$this->getMax2playNetworkLookup();
		$this->getPlayername();
		$this->getDisplayResolutions();
		$this->parsePlugins();
		$this->getDebug();
		$this->getEmail();
		$this->enableBetaUpdates();
		$this->view->removedonate = $this->getDonate();
		
	}		
	
	public function getDisplayResolutions(){
		$resolutions = array('1024x768' => '1024x768-noedid', '1920x1080@60' => '1080p-edid', '1080p' => '1080p-noedid', '1280x720M@60' => '720p-edid', '720p' => '720p-noedid');
		$output = shell_exec('ls /boot/ | grep boot-');
		preg_match_all("=boot-(.*?).scr=", $output, $matches);
		if($matches[1])
			$this->view->displayResolutions = $matches[1];
		else
			return false;
		$output = shell_exec('cat /boot/boot.scr');
		preg_match_all("=HDMI-A-1:([0-9xM@]*)=", $output, $matches);
		
		if($matches[1][0])
			//EDID Options
			$this->view->currentResolution = $resolutions[$matches[1][0]];
		else{
			//NOEDID Options
			preg_match_all("=-([0-9px]{4,})-(edid|noedid)=", $output, $matches);
			if($matches[1][0]){
				$this->view->currentResolution = $resolutions[$matches[1][0]];
			}else
				$this->view->currentResolution = 'auto_edid';
		}
		return true;
	}
	
	public function updateDisplayResolution($newResolution = ''){
		if(!$this->getDisplayResolutions())
			return false;
		if($this->view->currentResolution != $newResolution && in_array($newResolution,$this->view->displayResolutions)){			
			$output = shell_exec('sudo cp /boot/boot-'.$newResolution.'.scr /boot/boot.scr');
			$this->view->message[] = _('Changed display resolution - Reboot needed');
		}else{
			//$this->view->message[] = _('no valid resolution choosen');
		}
		return true;
	}
	
	public function getPlayername(){
		$output = trim(shell_exec('cat /opt/max2play/playername.txt'), "\n");
		$this->view->playername = $output;
		$output = trim(shell_exec('cat /etc/hostname'), "\n");
		$this->view->hostname = $output;
		return $output;
	}
	
	/**
	 * get settings for timezone
	 */
	public function getLocale(){
		$this->view->currentTimezone = trim(shell_exec('cat /etc/timezone'), "\n");
		if(!file_exists($this->timezonesfile)){
			$script[] = "find /usr/share/zoneinfo/right/ -type f | sed 's/\/usr\/share\/zoneinfo\/right\///' > /opt/max2play/timezones.txt";
			$this->writeDynamicScript($script);
			$this->view->message[] = _('Timezone file created');
		}
		$this->view->timezones = explode("\n", shell_exec('cat '.$this->timezonesfile));
		
		$output = shell_exec('cat /etc/default/keyboard');
		preg_match('=XKBLAYOUT\="([a-z]{2})"=', $output, $match);
		$this->view->currentKeyboard = $match[1];
		
		//Sprache setzen
		$output = shell_exec('cat /etc/default/locale');
		preg_match('=LANG\=["]?([a-zA-Z0-9\.\-\_]*)=', $output, $match);
		$this->view->currentLocale = $match[1];
		
		return true;
	}
	
	public function updateLocale($timezone = '', $locale = ''){		
		$this->getLocale();
		$script = array();
		if(in_array($timezone, $this->view->timezones)) {
			if($this->view->currentTimezone == $timezone){
				//No changes!				
			}else{			
				//Timezone setzen			
				$script[] = 'echo "'.$timezone.'" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata';			
			}
			
			if($this->view->currentLocale == $locale){
				//No changes!
			}else{								
				//Keyboard Layout setzen
				$script[] = "sed -i 's/XKBLAYOUT=.*/XKBLAYOUT=\"".substr($locale,0,2)."\"/' /etc/default/keyboard";			
				
				//Ist die neue Sprache verfügbar?
				if(trim(shell_exec('locale -a | grep '.str_replace('UTF-8','utf8',$locale)),"\n") == ''){
					//Unterscheidung PI / ODROID
					if($this->getSystemUser() == 'pi'){
						//Datei anpassen
						$script[] = "sed -i 's/# ".$locale." UTF-8/".$locale." UTF-8/' /etc/locale.gen";
						$script[] = "locale-gen";
					}else{
						$script[] = 'locale-gen '.$locale;
					}				
				}
				$script[] = "update-locale LANG=".$locale;							
			}
			if(isset($script[0])){
				$output = $this->writeDynamicScript($script);			
				$this->view->message[] = _("Changes successful - Reboot needed");
			}
			return true;
		}else{
			$this->view->message[] = _("Value for Timezone/Language not found.");
			return false;
		}		
	}
	
	
	/**
	 * Change Player Name
	 */
	public function updatePlayername($name = ''){
		$name = preg_replace('=[^a-zA-Z0-9\.]=i', '', $name);
		if($name != ''){
			$this->getPlayername();		
			if($name != $this->view->playername){				
				//Playername anpassen
				$output = shell_exec('echo '.$name.' > /opt/max2play/playername.txt');
				
				//Hostname anpassen
				$output = shell_exec('echo '.$name.' > /etc/hostname');
				
				//Hosts anpassen
				$output = $this->writeDynamicScript(array('sed -i \'s/'.$this->getHostname($this->view->playername).' '.$this->view->playername.'/'.$this->getHostname($name).' '.$name.'/\' /etc/hosts'));
				
				$this->view->message[] = _("Changes successful - Reboot needed");
				return true;
			}
		}else{			 
			$this->view->message[] = _("Name not possible! Please do not use special characters.");
			return false;
		}		
	}
	
	/**
	 * Alle Grundeinstellungen wiederherstellen: Autostart, XBMC, PlayerName, Wlan, etc.
	 */
	public function resetFactoryDefaults(){
		//Ursprungsfiles sind als Sicherungen in den jeweiligen Ordnern enthalten
		$files = array('/opt/max2play/playername.txt', 
					   '/etc/hostname', 
				       '/etc/network/interfaces',				 
					   '/etc/fstab',
					   '/boot/boot.scr',
					   '/opt/max2play/wpa_supplicant.conf',
					   '/opt/max2play/samba.conf',
					   '/opt/max2play/options.conf', //TODO: .sav
					   '/opt/max2play/audioplayer.conf', //TODO: .sav
				);
		
		foreach($files as $filename){
			if(file_exists($filename) && file_exists($filename.'.sav'))
				$output = shell_exec('cat '.$filename.'.sav > '.$filename);
		}
		
		return _("Config Files restored for ").": <br />".implode('<br />',$files);
	}
	
	public function getMax2playNetworkLookup(){
		return $this->view->Max2PlayNetworkLookup = $this->checkAutostart('Max2PlayNetworkLookup', true);
	}
	
	public function updateMax2playNetworkLookup(){
		if($this->updateAutostart('Max2PlayNetworkLookup', (bool)$_GET['Max2PlayNetworkLookup'], true)){
			$this->view->message[] = _('Max2Play Network Player Lookup saved');
			$this->loadViewHeader(true);
		}
		return true;
	}
	
	public function setDonateButton($removedonate = 0){
		if($this->saveConfigFileParameter('/opt/max2play/options.conf', 'removedonate', $removedonate)){
			$this->loadViewHeader(true);
			$this->view->message[] = _('Thank you so much for your donation! We will keep up the work on the project to make it even better!');
		}
		return true;	
	}
	
	public function updateEMail($email = ''){
		if($this->saveConfigFileParameter('/opt/max2play/options.conf', 'email', $email)){
			$this->view->message[] = _('Your eMail-address is saved.');
			if($this->checkLicense() == true){
				$this->view->message[] = _('Your license is validated. Now you have access to all features and plugins.');
			}else{
				$this->view->message[] = _('Your license could not be validated. Did you choose the right eMail-Address that is registered as a customer at www.max2play.com?');
			}
			return true;
		}
		return true;
	}

	public function getEmail(){
		$this->view->email = $this->getConfigFileParameter('/opt/max2play/options.conf', 'email');
		return true;
	}
	
	/**
	 * check for available Updates and do it
	 */
	public function checkMax2PlayUpdate($version = ''){		
		$this->getVersion();
		if($version == 'beta'){
			$this->info->version = 0; // Reset
		}
		
		//Check auf Update
		$file = file_get_contents('http://shop.max2play.com/media/downloadable/currentversion/version.txt');
		if((float)$this->info->version < (float)$file){
			$this->view->message[] = _('Max2Play update started');
			//Start Script -> Download Files for Webserver and /opt/max2play
			$shellanswer = shell_exec('sudo /opt/max2play/update_max2play.sh '.$version);			
			$this->view->message[] = $this->formatMessageOutput($shellanswer);
			if(strpos($shellanswer, 'inflating: /opt/max2play/list_devices.sh') !== FALSE && strpos($shellanswer, 'extracting: /var/www/max2play/application/config/version.txt') !== FALSE){
				$this->view->message[] = _('UPDATE SUCCESSFUL');
				$this->view->message[] = _('Max2Play-Webinterface Restarted - Reload Page to see Changes');
				//Reload apache!
				$this->writeDynamicScript(array('/etc/init.d/apache2 reload'));
			}
			else
				$this->view->message[] = _('UPDATE NOT SUCCESSFUL');
			
			$this->checkLicense();
			
			$this->loadViewHeader(true);
		}else{
			$this->view->message[] = _('Max2Play is up to date - no update required');			
		}
		return true;
	}
	
	/**
	 * get all plugins
	 * user may activate plugins and add them to the navigation
	 */
	public function parsePlugins(){
		$plugins_avail = array();
		//Parse Folder				
		$handle = opendir(APPLICATION_PATH.'/plugins');
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				try {
					$handle_controller = opendir(APPLICATION_PATH.'/plugins/'.$file.'/controller');
					if($handle_controller){
						while (false !== ($action = readdir($handle_controller))) {
							if ($action != "." && $action != "..") {
								$path = '/plugins/'.$file.'/controller/'.$action;
								//Parse Pluginname							
								$output = shell_exec('cat '.APPLICATION_PATH.$path.' | grep "this->pluginname"');							
								
								if(preg_match('=\$this-\>pluginname \= \_\(\'(.*)\'=', $output, $match)){					
									//only activate plugin if name is set (class of plugin may just be part of another class)
									$plugins_avail[$match[1]] = array('name' => $match[1], 
														     'navigation' => array('translate' => $match[1]), 
														     'path' => $path
													  );
								}							
							}
						}
						closedir($handle_controller);
					}
				}catch(Exception $e){
					$this->view->message[] = _('Plugin Error');
				}				
			}
		}		
		closedir($handle);		
		
		// get current Configuration from XML
		$pluginConf = $this->getActivePlugins();
		
		$plugins['configuration'] = $pluginConf['plugin'];
		$plugins['available'] = $plugins_avail;
		//Prepare Output for Choosing plugins in Multi SELECT
		$pos = 100;
		$position = 0;
		foreach($plugins['available'] as $pa){
			$active = $default = $position = false;
			foreach($plugins['configuration'] as $key => $pc){
				if($pa['name'] == $pc['name'] && isset($pc['active']) && $pc['active'] == 1){
					$active = true;
					$position = $key;
					if(isset($pc['default']) && $pc['default'] == 1){
						$default = 1;
					}
				}
			}
			//$pluginselect[$pa['name']] = array('name' => $pa['name'], 'active' => $active, 'default' => $default);
			if($active)
				$pluginselect[$position] = array('name' => $pa['name'], 'active' => $active, 'default' => $default);
			else
				$pluginselect[$pos++] = array('name' => $pa['name'], 'active' => $active, 'default' => $default);
		}
		ksort($pluginselect);
		$this->view->pluginselect = $pluginselect;
		return $plugins;
	}
	
	/**
	 * Save Plugin Configuration to XML
	 * @param string $plugins
	 */
	private function pluginConfig($pluginchoose = false, $defaultplugin = false){				
		//Make config Folder and Plugin Config File writeable
		$this->writeDynamicScript(array('chmod -R 777 '.APPLICATION_PATH.'/config'));
		
		//Check active Plugins
		$plugins = $this->parsePlugins();
		$pos = 100;
		foreach($plugins['available'] as $pa){
			$pa['active'] = 0;
			$pa['pos'] = $pos++;
			foreach($pluginchoose as $key => $pc){
				if($pc == $pa['name']){
					$pa['active'] = 1;
					$pa['pos'] = $key;
				}
			}
			if($defaultplugin == $pa['name']){
				$pa['default'] = 1;				
			}
			$newconfig['plugin'][$pa['pos']] = $pa;			
		}
		ksort($newconfig['plugin']);
		
		include_once(APPLICATION_PATH.'/library/array2xml.php');
		$xml = Array2XML::createXML('config', $newconfig);

		$xml->save(APPLICATION_PATH.'/config/plugins.xml');
		
		//Reload Plugins
		global $service;
		$service->plugins = $this->getActivePlugins();
		
		return _('Plugin configuration updated');				
		
	}
	
	/**
	 * Install a new Plugin from HTTP-Resource
	 * @param string $pathToPlugin
	 */
	private function installPlugin($pathToPlugin = ''){		
		if($this->checkLicense(true) == false)
			return true;
		if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$pathToPlugin)) {
  			$this->view->message[] = _("Invalid Plugin-URL");
		}else{
			$this->view->message[] = nl2br($this->writeDynamicScript(array('/opt/max2play/install_plugin.sh '.$pathToPlugin)));
		}
		return true;
	}
	
	/**
	 * Expandiere Root-FS auf ODROID auf Max. Größe
	 */
	private function resizeFS(){		
		//Get root Partition for Resize
		$script = array('blkid');
		$output = $this->writeDynamicScript($script);
		$partitions = explode("\n", trim($output, "\n"));
		$resizePart = substr($partitions[count($partitions) -1], 5, strpos($partitions[count($partitions) -1], ':') - 5);
		$this->view->message[] = _('Resize Filesystem').': '.$resizePart;
		
		//Do the Resize for typical odroid / raspberry Partitioning (normal image) | problem with noobs image!
		if(in_array($resizePart, array('mmcblk0p2'))){ // ,'mmcblk0p6'
			$script = array('/opt/max2play/expandfs.sh '.$resizePart.' > /opt/max2play/cache/resize-max2play-log.txt');		
			$this->view->message[] = nl2br($this->writeDynamicScript($script));
			$this->view->message[] = shell_exec('cat /opt/max2play/cache/resize-max2play-log.txt');
		}else {
			$this->view->message[] = _('No Resize possible - no valid partition found to expand. Contact Max2Play-Support to add support for further file-systems.');
		}
		return true;
	}
	
	/**
	 * get Debuginformation
	 */
	private function getDebug(){
		$out['FILESYSTEM'] = shell_exec('df');
		$out['LOAD AVERAGE'] = shell_exec('cat /proc/loadavg');	
		$out['KERNEL'] = shell_exec('uname -a');
		$out['LINUX-VERSION'] = shell_exec('lsb_release -a');
		
		$this->view->debug = $out;
	}
	
	/**
	 * Install ntfs-3g update Udev-Rules for Udisks2 and configure usbmount
	 */
	private function fixUsbMount(){
		$script[] = 'apt-get install ntfs-3g';
		$script[] = 'echo SUBSYSTEMS==\"usb\",ENV{UDISKS_AUTO}=\"0\" > /etc/udev/rules.d/99-udisks2.rules';
		$script[] = 'udevadm control --reload-rules';
		$this->view->message[] = nl2br($this->writeDynamicScript($script));
		$this->view->message[] = _('udev-Rules added and reloaded... Completed');
		return true;
	}
	
	/**
	 * Difference between FQDN and Hostname -> divided by fist "."
	 */
	public function getHostname($name){
		if(strpos($name,'.') !== FALSE){
			$parts = explode('.',$name);
			return $parts[0];
		}else
			return $name;
	}
	
	/**
	 * For Development and easy Beta-Updates
	 * @return boolean
	 */
	public function enableBetaUpdates(){
		if($this->checkLicense(true, true) == false)
			return true;
		$this->view->betaEnabled = true;
	}
}

//Create Instance for view
$basic = new Basic();
include_once(dirname(__FILE__).'/../view/basic.php');