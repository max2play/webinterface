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
	
	public function __construct(){										
		parent::__construct();
		$this->pluginname = _('Settings / Reboot');
		
		$this->view->locales = array('Europe/Berlin' => 'de_DE.UTF-8', 'Europe/Zurich' => 'de_CH.UTF-8', 'Europe/London' => 'en_GB.UTF-8','Europe/Rome' => 'it_IT.UTF-8', 'Europe/Paris' => 'fr_FR.UTF-8', 'Europe/Istanbul' => 'tr_TR.UTF-8', 'Europe/Amsterdam' => 'nl_NL.UTF-8');
		
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
				$this->view->message[] = $this->checkMax2PlayUpdate();
			}
			
			if($_GET['action'] == 'pluginconfig'){
				$this->view->message[] = $this->pluginConfig($_GET['plugins'], $_GET['defaultplugin']);
			}
			
			if($_GET['action'] == 'save'){
				$this->view->message[] = $this->updatePlayername($_GET['playername']);
				$this->view->message[] = $this->updateLocale($_GET['locale']);
				//$this->view->message[] = $this->updateDisplayResolution($_GET['displayResolution']);
				$this->updateMax2playNetworkLookup();
			}
		}
		$this->getLocale();
		$this->getMax2playNetworkLookup();
		$this->getPlayername();
		//$this->getDisplayResolutions(); deactivated
		$this->parsePlugins();
		$this->getDebug();
	}		
	
	public function getDisplayResolutions(){
		$output = shell_exec('ls /boot/ | grep boot-hdmi');
		preg_match_all("=boot-hdmi-(.*?).scr=", $output, $matches);
		if($matches[1])
			$this->view->displayResolutions = $matches[1];
		$output = shell_exec('cat /boot/boot.scr ');
		preg_match_all("=Boot\.scr for hdmi at ([0-9pihz]*)=", $output, $matches);
		
		if($matches[1][0])
			$this->view->currentResolution = $matches[1][0];
		return true;
	}
	
	public function updateDisplayResolution($newResolution = ''){
		$this->getDisplayResolutions();
		if($this->view->currentResolution != $newResolution && in_array($newResolution,$this->view->displayResolutions)){			
			$output = shell_exec('sudo cp /boot/boot-hdmi-'.$newResolution.'.scr /boot/boot.scr');
			return _('Changed display resolution - Reboot needed');
		}else{
			return _('no valid resolution choosen');
		}
	}
	
	public function getPlayername(){
		$output = shell_exec('cat /opt/max2play/playername.txt');
		$this->view->playername = $output;
		$output = shell_exec('cat /etc/hostname');
		$this->view->hostname = $output;
		return $output;
	}
	
	public function getLocale(){
		$this->view->currentLocale = trim(shell_exec('cat /etc/timezone'), "\n");
		return $this->view->currentLocale;
	}
	
	public function updateLocale($locale = ''){		
		if(isset($this->view->locales[$locale])) {
			//Timezone setzen			
			$script[] = 'echo "'.$locale.'" > /etc/timezone && dpkg-reconfigure -f noninteractive tzdata';			
			
			//Keyboard Layout setzen
			$output = shell_exec('cat /etc/default/keyboard');
			preg_match('=XKBLAYOUT\="([a-z]{2})"=', $output, $match);
			$current_keyboard_layout = $match[1];
			
			$script[] = 'echo \''.str_replace(array($match[0],"'"), array('XKBLAYOUT="'.substr($this->view->locales[$locale], 0,2).'"', ""), trim($output, "\n")).'\' > /etc/default/keyboard';			
			
			//Ist die neue Sprache verfügbar?
			if(trim(shell_exec('locale -a | grep '.str_replace('UTF-8','utf8',$this->view->locales[$locale])),"\n") == ''){
				$script[] = 'locale-gen '.$this->view->locales[$locale];
			}
			
			//Sprache setzen
			$output = shell_exec('cat /etc/default/locale');
			preg_match('=LANG\=["]?([a-zA-Z0-9\.\-\_]*)=', $output, $match);
			$current_locale = $match[1];
			
			preg_match('=LANGUAGE\=["]?([a-zA-Z0-9\.\-\_:]*)=', $output, $match);
			$current_language = $match[1];
			
			$script[] = 'echo \''.str_replace(array($current_locale, $current_language), $this->view->locales[$locale], trim($output, "\n")).'\' > /etc/default/locale';			
			
			$this->view->message[] = $this->writeDynamicScript($script);
		}else{
			return _("Value for Timezone/Language not found.");
		}
		return _("Changes successful - Reboot needed");
	}
	
	
	/**
	 * Change Player Name
	 */
	public function updatePlayername($name = ''){
		$name = preg_replace('=[^a-zA-Z0-9]=i', '', $name);
		if($name != ''){
			if($name != $this->view->playername){
				//Squeezelite anpassen
				$output = shell_exec('echo '.$name.' > /opt/max2play/playername.txt');
				
				//Hostname anpassen
				$output = shell_exec('echo '.$name.' > /etc/hostname');
			}
		}else{
			return _("Name not possible! Please do not use special characters.");
		}
		return _("Changes successful - Reboot needed");
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
					   '/opt/wpa_supplicant.conf'
				);
		
		foreach($files as $filename){
			if(file_exists($filename))
				$output = shell_exec('cat '.$filename.'.sav > '.$filename);
		}
		
		return _("Config Files restored for ").": <br />".implode('<br />',$files);
	}
	
	public function getMax2playNetworkLookup(){
		$this->view->Max2PlayNetworkLookup = $this->checkAutostart('Max2PlayNetworkLookup', true);
	}
	
	public function updateMax2playNetworkLookup(){		
		$this->updateAutostart('Max2PlayNetworkLookup', (bool)$_GET['Max2PlayNetworkLookup'], true);
		$this->view->message[] = _('Max2Play Network Player Lookup saved');
	}
	
	/**
	 * check for available Updates and do it
	 */
	public function checkMax2PlayUpdate(){
		$this->getVersion();
		//Check auf Update
		$file = file_get_contents('http://shop.max2play.com/media/downloadable/currentversion/version.txt');
		if((float)$this->info->version < (float)$file){
			$this->view->message[] = _('Max2Play update started');			
			//Start Script -> Download Files for Webserver and /opt/max2play
			$shellanswer = shell_exec('sudo /opt/max2play/update_max2play.sh');			
			$this->view->message[] = nl2br($shellanswer);
			if(strpos($shellanswer, 'inflating: /opt/max2play/list_devices.sh') !== FALSE && strpos($shellanswer, 'extracting: /var/www/max2play/application/config/version.txt') !== FALSE)
				$this->view->message[] = _('UPDATE SUCCESSFUL');
			else
				$this->view->message[] = _('UPDATE NOT SUCCESSFUL');
		}else{
			$this->view->message[] = _('Max2Play is up to date - no update required');
		}
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
		foreach($plugins['available'] as $pa){
			$active = $default = false;
			foreach($plugins['configuration'] as $pc){
				if($pa['name'] == $pc['name'] && isset($pc['active']) && $pc['active'] == 1){
					$active = true;
					if(isset($pc['default']) && $pc['default'] == 1){
						$default = 1;
					}
				}
			}
			$pluginselect[$pa['name']] = array('name' => $pa['name'], 'active' => $active, 'default' => $default);
		}
		
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
		$pos = 1;
		foreach($plugins['available'] as $pa){
			$pa['active'] = 0;
			foreach($pluginchoose as $pc){
				if($pc == $pa['name'])
					$pa['active'] = 1;
			}
			if($defaultplugin == $pa['name']){
				$pa['default'] = 1;
				$newconfig['plugin'][0] = $pa;
			}else{
				$newconfig['plugin'][$pos++] = $pa;
			}
		}
		ksort($newconfig['plugin']);
		
		include_once(APPLICATION_PATH.'/library/array2xml.php');
		$xml = Array2XML::createXML('config', $newconfig);

		$xml->save(APPLICATION_PATH.'/config/plugins.xml');
		return _('Plugin configuration updated - Reload Page to see changes');
		
		//TODO: redirect to self in 3 seconds
		
	}
	
	/**
	 * Expandiere Root-FS auf ODROID auf Max. Größe
	 */
	private function resizeFS(){
		$script = array('/opt/max2play/expandfs.sh > /opt/max2play/cache/resize-max2play-log.txt');
		$this->view->message[] = _('Resize Filesystem');
		$this->view->message[] = $this->writeDynamicScript($script);
		$this->view->message[] = shell_exec('cat /opt/max2play/cache/resize-max2play-log.txt');
		return true;
	}
	
	/**
	 * get Debuginformation
	 */
	private function getDebug(){
		$out['FILESYSTEM'] = shell_exec('df');
		$out['LOAD AVERAGE'] = shell_exec('cat /proc/loadavg');	
		$out['KERNEL'] = shell_exec('uname -a');
		
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
}

//Create Instance for view
$basic = new Basic();
include_once(dirname(__FILE__).'/../view/basic.php');