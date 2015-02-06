<?php 

/**
 * Klasse für Services mit Methoden zur Steuerung
 * 
 * Alle Scripte müssen in die sudoers Datei mit Rechten für die Gruppe www-data - visudo oder nano /etc/sudoers.d/max2play
 * 
 */

/**
 Services Administration Controller

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

include_once '../application/model/Info.php';
//TODO: DEBUG für Alle Systembefehle ausgeben

class Service {
	public $view;	
	public $viewname; //Name of Service in View
	public $info;
	public $plugins;
	public $autostartconf = '/opt/max2play/autostart.conf';
	public $dynamicScript = '/opt/max2play/dynamic_script.sh';
	
	public function __construct(){
		$this->view = new stdClass();
		$this->view->message = array(); // Array of Messages for View
		$this->info = new Info();				
	}
	
	/**
	 * Function for Instance of Serviceclass to load Global Parameters (e.g. for Header)
	 */
	public function loadDefaults(){
		$this->getPlayername();
		$this->getAllNetworkPlayers();
		$this->getVersion();
		$this->getDonate();
		$this->getHardwareInfo();
		return true;
	}
	
	public function status($name = ''){
		if($name == '')
			return false;
		
		$shellanswer = shell_exec("ps -Al | grep ".$name);		
		if($shellanswer != ''){
			preg_match('=([ ]*)([0-9]*)=', substr($shellanswer, 10,5), $match);
			if($match[2]){
				$process_id = $match[2];
				return $process_id;
			}		
		}
		return false;			
	}
	
	public function start($name = '', $command = '', $statusname = '', $rootstart = false){
		if($name == '')
			return false;
		if($statusname != '')
			$pid = $this->status($statusname);
		else
			$pid = $this->status($name);
		
		if($pid !== FALSE){
			$shellanswer = _('Process is already running');
			return $shellanswer;
		}
		if($command != ''){
			$startcom = $command;
		}else{
			$startcom = 'sudo /etc/init.d/'.$name.' start';
		}
		
		if(!$rootstart){
			shell_exec($startcom);
		}else{
			$this->writeDynamicScript(array($startcom));
		}
		
		sleep(2);
		
		$shellanswer = _('Trying to launch');
		
		if($statusname != '')
			$pid = $this->status($statusname);
		else
			$pid = $this->status($name);
		
		if($pid != FALSE){
			$shellanswer .= ' ... '._('successful with ID').' '.$pid;
		}else{
			$shellanswer .= ' ... '._('NOT successful');
		}
		return $shellanswer;
	}
	
	public function stop($name = '', $command = '', $statusname = ''){
		if($name == '')
			return false;
		
		if($statusname != '')
			$pid = $this->status($statusname);
		else
			$pid = $this->status($name);
		
		if($pid === FALSE){
			$shellanswer = _('Unable to stop process - it seems that it is not running');
			return $shellanswer;
		}
		
		if($command != ''){
			$stopcom = str_replace('$PID', $pid ,$command);
		}else{
			$stopcom = 'sudo /etc/init.d/'.$name.' stop';
		}
		shell_exec($stopcom);		
		
		sleep(3);
		
		$shellanswer = _('Trying to stop');
		
		if($statusname != '')
			$pid = $this->status($statusname);
		else
			$pid = $this->status($name);
		
		if($pid === FALSE){
			$shellanswer .= ' ... '._('successfully stopped');
		}else{
			$shellanswer .= ' ... '._('could NOT stop process - try kill instead');
		}
		return $shellanswer;
	}
	
	/**
	 * Funktion kill - kills by name in processlist
	 * @param string $name
	 * @return boolean|string
	 */
	public function kill($name = ''){
		if($name == '')
			return false;
		$pid = $this->status($name);
		if($pid === FALSE){
			$shellanswer = _('Unable to kill process - it seems that it is not running');
			return $shellanswer;
		}
		
		shell_exec("sudo kill -9 ".$pid);
		
		sleep(3);
		
		$shellanswer = _('Trying to stop');
		$pid = $this->status($name);
		if($pid === FALSE){
			$shellanswer .= ' ... '._('successfully killed');
		}else{
			$shellanswer .= ' ... '._('could not kill process');
		}
		return $shellanswer;
	}
	
	/**
	 * 
	 * @param string $name Servicename - i.e. Scriptname in /etc/init.d
	 * @param string $autostartconf Use /opt/max2play/autostart.conf for Autostart
	 * @return boolean
	 * 
	 * Important: XBMC uses differend autostart for XServer (defined in /controller/Xbmc.php)
	 */
	public function checkAutostart($name = '', $autostartconf = false){
		if($name == '')
			return false;
		if(!$autostartconf){
			$isactive = shell_exec("ls /etc/rc2.d | grep ".$name);
			if(strpos($isactive, $name) !== FALSE){
				$isactive = true;
			}else{
				$isactive = false;
			}
			return $isactive;
		}else{
			$output = shell_exec('cat '.$this->autostartconf);
			if(strpos($output, $name.'=1') !== FALSE){
				$isactive = true;
			}else{
				$isactive = false;
			}
			return $isactive;
		}
	}
	
	/**
	 * Configure Autostart
	 * @param bool $autostart
	 * @param bool $autostartconf defines wether update-rc.d or the autostartconfigfile in /opt/max2play should be used
	 */
	public function selectAutostart($autostart = false, $autostartconf = true){
		$isactive = $this->checkAutostart($this->pname, $autostartconf);
		if(!$autostart && $isactive){
			$this->updateAutostart($this->pname, false, $autostartconf);
			$this->view->message[] = $this->pname." "._('successfully removed from autostart');
		}elseif($autostart && !$isactive){
			$this->updateAutostart($this->pname, true, $autostartconf);
			$this->view->message[] = $this->pname." "._('successfully added to autostart');
		}
		return true;
	}
	
	/**
	 * Update Autostart by Config-File OR update-rc.d
	 * @param string $name
	 * @param string $active
	 * @return bool Value Changed
	 */
	public function updateAutostart($name = '', $active = false, $autostartconf = true){
		
		$output = preg_replace('=[\r\n ]*$=','',shell_exec('cat '.$this->autostartconf));
		
		if($name == '')
			return false;
		if($active == false){			
			if(!$autostartconf){
				shell_exec("sudo update-rc.d -f ".$name." remove");
			}else{
				//Write Config-file
				return $this->saveConfigFileParameter($this->autostartconf, $name, 0);
				//shell_exec("echo '".str_replace($name.'=1', $name.'=0', $output)."' > ".$this->autostartconf);
			}
		}else{
			if(!$autostartconf){
				shell_exec("sudo update-rc.d ".$name." defaults");
			}else{
				//Write Config-file
				return $this->saveConfigFileParameter($this->autostartconf, $name, 1);
				//shell_exec("echo '".str_replace($name.'=0', $name.'=1', $output)."' > ".$this->autostartconf);
			}
		}
		return true;
	}
	
	/**
	 * Name of Player - also used in /etc/hostname 
	 * @return string
	 */
	public function getPlayername(){
		$output = shell_exec('cat /opt/max2play/playername.txt');
		$this->info->playername = $output;
		return $output;
	}
	
	/** 
	 * get a list of all Max2Play-Devices in local network
	 * Needs package nmap to list devices responding on port 5002
	 * uses /opt/max2play/list_devices.sh
	 */
	public function getAllNetworkPlayers(){
		//Only do network search if activated in basic-settings
		if(!$this->checkAutostart('Max2PlayNetworkLookup', true)){
			return false;
		}
		//Caching in txt-file - reload every $reload
		$reload = 3600; // 1 hour
		
		$devices_txt = shell_exec('cat /opt/max2play/cache/list_devices.txt');
		
		if(preg_match('=([0-9\-: ]{15,})=', $devices_txt, $matches)){
			$lastcheck = strtotime($matches[0]);
		}else{
			$lastcheck = false;
		}
		
		if((time() - $reload) > $lastcheck){
			//Recheck available Devices			
			shell_exec('/opt/max2play/list_devices.sh');			
			$devices_txt = shell_exec('cat /opt/max2play/cache/list_devices.txt');
		}
				
		//parse Nmap scan report
		preg_match_all('=report for ((.*?) \(([0-9\.]*)\))=', $devices_txt, $matches);
		preg_match('=^[0-9\.]*=', $devices_txt, $match);
		$ipadress = $match[0];
		
		if(isset($matches[2])){
			for($i=0; $i< count($matches[2]); $i++){
				$device = array('name' => $matches[2][$i], 'ip' => $matches[3][$i], 'current' => false);
				if($matches[3][$i] == $ipadress){
					$device['current'] = true;					
				}
				$this->info->devices[] = $device;
			}
		}else{
			$this->info->devices = false;
		}

		return true;						
		
	}
	
	/**
	 * get Current Version of Max2Play-Webinterface
	 */
	public function getVersion(){
		$this->info->version = file_get_contents(APPLICATION_PATH.'/config/version.txt');
	}		
	
	public function getDonate(){
		$this->info->removedonate = $this->getConfigFileParameter('/opt/max2play/options.conf', 'removedonate');
		return $this->info->removedonate;
	}
	
	public function checkForUpdate(){
		$this->getVersion();
		//Check auf Update
		$file = file_get_contents('http://shop.max2play.com/media/downloadable/currentversion/version.txt');
		if((float)$this->info->version < (float)$file){
			$this->view->message[] = _('Max2Play update is available - start update on tab Settings / Reboot');
		}
		return true;
	}
	
	/**
	 * Parse Plugin Configuration
	 */
	public function getActivePlugins(){
		$xml = simplexml_load_file(APPLICATION_PATH.'/config/plugins.xml');
		$json = json_encode($xml);
		$this->plugins = json_decode($json,TRUE);
		return $this->plugins;
	}
	
	/**
	 * Write to File that has Root Rights to launch specific installations and configs
	 * $script is an array separated by lines for each task	
	 * $background to run script in background
	 * $daemon to run as real daemon - survives even a apache restart. e.g. for update and upgrade
	 */
	public function writeDynamicScript($script = '', $background = false, $daemon = false){
		$fp = fopen($this->dynamicScript, 'w+');
		
		fwrite($fp,"#!/bin/bash\n");				
		
		foreach ($script as $s)
			fwrite($fp, "\n".str_ireplace("\x0D", "", $s));			
		
		fclose($fp);
		
		if($daemon){
			$command = escapeshellarg('sudo '.$this->dynamicScript);
			exec("php /opt/max2play/dynamicscriptdaemon.php {$command} >> /dev/null 2>&1 &");
		}else{
			$output = shell_exec('sudo './*((true == $background) ? 'nohup ' : ' ').*/$this->dynamicScript.((true == $background) ? ' > /dev/null &' : ''));
		}		
		
		return $output;
	}
	
	/**
	 * 
	 * @param string $directory
	 * @param string $domain
	 */
	public function registerLocale($directory = '', $domain = ''){		
		//Add Plugin Translation
		bindtextdomain($domain, $directory);
		bind_textdomain_codeset($domain, 'UTF-8');
		define('newLocale', $domain);
	}
	
	/**
	 * Function to save specific Parameter to specified Configfile
	 * @param string $configfile
	 * @param string $parameter
	 * @param string $value
	 */
	public function saveConfigFileParameter($configfile = '', $parameter = '', $value = ''){		
		if(file_exists($configfile)){
			$old_parameter = trim($this->getConfigFileParameter($configfile, $parameter));
			
			if($old_parameter == $value){
				//No changes
				return false;
			}
			//Check for empty entry
			$param_exists = shell_exec('grep -a "'.$parameter.'" '.$configfile.' | wc -l');
			if($old_parameter != '' || $param_exists > 0){
				$this->writeDynamicScript(array('sed -i "s/^'.$parameter.'.*$/'.$parameter.'='.$value.'/g" '.$configfile));
				$this->view->message[] = _("Update Configfile - existing Entry changed");
			}else{
				//check for Newline in Last Line in config file
				if(strpos(shell_exec('xxd -p '.$configfile.' | tail -c 3'), '0a') === FALSE){
					//Newline missing -> add one
					$parameter = "\n".$parameter;
				}
				$this->writeDynamicScript(array('echo "'.$parameter.'='.$value.'" >> '.$configfile));
				$this->view->message[] = _("Update Configfile - new Entry created");
			}
		}
		else{
			$this->writeDynamicScript(array('echo "'.$parameter.'='.$value.'" > '.$configfile));
			$this->view->message[] = _("Update Configfile - new Configfile created");
		}
		return true;
	}
	
	/**
	 * Function to get specific Parameter from specified Configfile
	 * @return boolean
	 */
	public function getConfigFileParameter($configfile = '', $parameter = ''){
		if(!file_exists($configfile))
			return false;
		$output = trim(shell_exec('grep -a "'.$parameter.'" '.$configfile.' | sed -n -e "s/^[A-Za-z_0-9]*\=//p"'));
		return $output;
	}
	
	/**
	 * Longer Tasks need some progress shown to the user while completing
	 * Call this function first and than put all Output into the progressfile
	 *  1. Ending when Progressfile is deleted by calling Script at the end
	 *  2. Ending when Progressfile has output "finished" somewhere
	 * @param $progressfile File that as long as it exists shows current status of install
	 * @param $create First call creates Outputfile and Message
	 * @param $reloadWhenFinished Reload Window when everything is finished
	 * @return Message for Ajax-Output
	 */
	public function getProgressWithAjax($progressfile = '', $create = 0, $reloadWhenFinished = 0, $lastlines = 0){
		if(!file_exists($progressfile) && $create == 1){		
			//Create File and set Message Output for Ajax-Call
			shell_exec('echo `date +"%Y-%m-%d %H:%M|"` > '.$progressfile);
			$this->view->message[] = _('Installation startet - This Messages refreshes every 3 seconds to show current status of installation. If finished this message disappears.');
			//Separate Parameters from current Filename
			$url = preg_replace('=\?.*$=', '', $_SERVER['REQUEST_URI']);
			$this->view->message[] = '<div id="msgprogress"></div><script type="text/javascript">setTimeout(function(){reloadprogress("msgprogress", "'.$url.'", '.$reloadWhenFinished.')}, 3000);</script>';
			return true;
		}elseif(file_exists($progressfile) && $create == 0){
			//Check for Status finished and return current status and progressfile and reload if neccesary
			if($lastlines > 0){
				$shellanswer = shell_exec("tail -$lastlines $progressfile");
			}else
				$shellanswer = shell_exec("cat $progressfile");			
			return $shellanswer;
		}elseif(file_exists($progressfile) && $create == 1){
			//File should not be existing - show error and delete file!
			$shellanswer = shell_exec("cat $progressfile");
			preg_match('=[0-9\: -]*=', $shellanswer, $started);					
			$this->view->message[] = _('Something went wrong in last Install Attempt - Deleting Progressfile');
			$this->writeDynamicScript(array("rm $progressfile"));
			return false;
		}else{
			//!file_exists($progressfile) && $create == 0 --> Finished
			$this->view->message[] = '<!-- finished -->';
			return true;
		}
	}
	
	/**
	 * Function to render View Header
	 * @param reload Set to reload Header after Changes (if global settings changed)
	 */
	public function loadViewHeader($reload = false){
		global $service;		
	
		if(true == $reload){
			//Clear Output
			ob_end_clean();
			$service->loadDefaults(); //Global Scope
		}
		if(file_exists(APPLICATION_PATH.'/view/header_custom.php'))
			include(APPLICATION_PATH.'/view/header_custom.php');
		else
			include(APPLICATION_PATH.'/view/header.php');
	
		return true;
	}
	
	public function loadViewFooter(){
		global $service;
		if(file_exists(APPLICATION_PATH.'/view/footer_custom.php'))
			include_once(APPLICATION_PATH.'/view/footer_custom.php');
		else
			include_once(APPLICATION_PATH.'/view/footer.php');
	
		return true;
	}
	
	public function getHardwareInfo(){
		$output = shell_exec("cat /proc/cpuinfo | grep Hardware");
		if(strpos($output, 'BCM2708'))
			$this->info->hardware = 'Raspberry PI';
		else{
			preg_match('=Hardware.*: ([^ ]*)=', $output, $matches);
			$this->info->hardware = $matches[1];
		}
		return true;
	}
	
	public function getSystemUser(){
		$system_user = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'SYSTEM_USER');
		if($system_user)
			$this->info->system_user = $system_user;
		else
			$this->info->system_user = 'odroid';
		return $this->info->system_user;
	}		
	
	/**
	 * 
	 * @param string $local do only local check (on update always check online)
	 * @param boolean $silent do not print messages
	 * @return boolean
	 */
	public function checkLicense($local = false, $silent = false){
		if($local == true){
			if($this->getConfigFileParameter('/opt/max2play/options.conf', 'license') == 1){				
				return true;
			}else{
				if(!$silent)
					$this->view->message[] = _('No valid Max2Play-License for additional plugins and features found. Please enter a valid eMail-address on the Settings-page to verify that you are a customer of Max2Play and to access all features.');
				return false;
			}
		}
		$email = $this->getConfigFileParameter('/opt/max2play/options.conf', 'email');
		
		include_once '../application/model/CheckLicense.php';
		if($license == true){
			$this->saveConfigFileParameter('/opt/max2play/options.conf', 'license', '1');
			return true;
		}else{
			$this->saveConfigFileParameter('/opt/max2play/options.conf', 'license', '0');
			return false;
		}
	}
}

//Create Instance of Service Class
$service = new Service();
$service->loadDefaults();

