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

class Service {
	public $view;	
	public $viewname; //Name of Service in View
	public $info;
	public $plugins;
	public $debug = false;
	public $readonly_fs = 'unset'; // Readonly Filesystem
	public $autostartconf = '/opt/max2play/autostart.conf';
	public $dynamicScript = '/opt/max2play/dynamic_script.sh';
	
	public $equal = array('01. 31 Hz', '02. 63 Hz', '03. 125 Hz', '04. 250 Hz', '05. 500 Hz', '06. 1 kHz', '07. 2 kHz', '08. 4 kHz', '09. 8 kHz', '10. 16 kHz');
	public $equalvalues = array();
	
	public function __construct(){
		$this->view = new stdClass();
		$this->view->message = array(); // Array of Messages for View
		$this->info = new Info();
		if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == true){
			$this->debug = true;
		}	
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
		if(isset($_REQUEST['closeguide']) && $_REQUEST['closeguide'] == 1){
			$this->setHelpOnSidebar(false);
		}
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
	
	public function start($name = '', $command = '', $statusname = '', $rootstart = false, $background = false){
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
			$startcom = 'sudo /etc/init.d/'.$name.' start 2>&1';
		}
		
		if(!$rootstart){
			$answer = shell_exec($startcom);
		}else{
			if($background)
				$answer = $this->writeDynamicScript(array($startcom), $background);
			else
				$answer = $this->writeDynamicScript(array($startcom));
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
			if($background)
				$shellanswer .= ' '.$this->formatMessageOutput(shell_exec('cat '.$background));
			else 
				$shellanswer .= ' '.$this->formatMessageOutput($answer);
		}
		return $shellanswer;
	}
	
	public function stop($name = '', $command = '', $statusname = '', $rootstop = false){
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
		
		if(!$rootstop){
			shell_exec($stopcom);
		}else{
			$this->writeDynamicScript(array($stopcom));
		}			
		
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
	 * Use init.d Script to restart daemon
	 * @param Script $name
	 */
	public function restart($name = '', $command = '', $statusname = ''){
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
			
		$startcom = 'sudo /etc/init.d/'.$name.' restart';		
			
		$this->writeDynamicScript(array($startcom));
		return _('restarted');
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
			$output = shell_exec('grep -i "'.$name.'=1" '.$this->autostartconf);
			if(strpos($output, $name.'=1') === 0){
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
	public function selectAutostart($autostart = false, $autostartconf = true, $name = ''){
		if($name == '')
			$name = $this->pname;
		$isactive = $this->checkAutostart($name, $autostartconf);
		if(!$autostart && $isactive){
			$this->updateAutostart($name, false, $autostartconf);
			$this->view->message[] = $name." "._('successfully removed from autostart');
		}elseif($autostart && !$isactive){
			$this->updateAutostart($name, true, $autostartconf);
			$this->view->message[] = $name." "._('successfully added to autostart');
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
	
	/**
	 * If an Addon needs a minimum Version of Max2Play
	 * @param number $minversion
	 * @return boolean
	 */
	public function checkMinVersion($minversion = 0){
		$this->getVersion();
		if((float)$this->info->version < $minversion && strpos($this->info->version, 'Beta') === FALSE){
			$this->view->message[] = str_replace('$MINVERSION', $minversion, _('You need at least Max2Play Version $MINVERSION to run this addon. Please update Max2Play on Settings tab!'));
			return false;
		}
		return true;
	}
	
	public function checkForUpdate(){
		$this->getVersion();
		//Check for Update
		$file = $this->getExternalFile('http://shop.max2play.com/media/downloadable/currentversion/version.txt', 3);
		if(strpos($this->info->version, 'Beta') !== FALSE){
			$this->view->message[] = _('You are running a Beta-Version of Max2Play!');
		}elseif((float)$this->info->version < (float)$file){
			$this->view->message[] = _('Max2Play update is available - start update on tab Settings / Reboot');
		}
		return true;
	}
	
	/**
	 * Get external File with defined timeout
	 * Do it with Curl (if installed) or by file_get_contents
	 */
	public function getExternalFile($file = '', $timeout = 5, $curl = false){
		if($file != ''){						
			if($curl === true){
				try{
					$ch=curl_init();						
					curl_setopt($ch, CURLOPT_URL, $file);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);			
					$response=curl_exec($ch);
					curl_close($ch);
					if($response){
						$header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);				
						$content = substr( $response, $header_size );
						return $content;
					}else 
						return false;
				}catch(Exception $e){
					$this->view->message[] = _('No Curl for HTTP-Request available!');
				}
			}else{				
				$ctx = stream_context_create(array('http'=>	array('timeout' => $timeout)));
				$content = file_get_contents($file, false, $ctx);				
				return $content;
			}
		}
		return false;
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
		if($this->readonly_fs === 'unset'){
			if($this->getConfigFileParameter('/opt/max2play/options.conf', 'readonly') == '1'){
				$this->readonly_fs = true;
				$this->dynamicScript = '/tmp/dynamic_script.sh';				
				if(!file_exists('/tmp/writemode.txt'))
					$this->view->message[] = _('SD-Card is Read-Only! Changes on settings will not be saved!');
			}else{
				$this->readonly_fs = false;
			}
		}
		
		$fp = fopen($this->dynamicScript, 'w+');
		
		fwrite($fp,"#!/bin/bash\n");				
		
		foreach ($script as $s)
			fwrite($fp, "\n".str_ireplace("\x0D", "", $s));			
		
		fclose($fp);				
		
		if($this->readonly_fs)
			shell_exec('chmod 777 '.$this->dynamicScript);
		
		if($this->debug){
			global $debuglog;			
			$debuglog[] = get_class($this).' '. shell_exec('cat '.$this->dynamicScript);			
		}
		
		$backgroundfile = '/dev/null';
		if(strpos($background,'/tmp') !== FALSE){
			$backgroundfile = $background;			
		}
		
		if($daemon){
			$command = escapeshellarg('sudo '.$this->dynamicScript);
			exec("php /opt/max2play/dynamicscriptdaemon.php {$command} >> /dev/null 2>&1 &");
		}else{
			$output = shell_exec('sudo './*((true == $background) ? 'nohup ' : ' ').*/$this->dynamicScript.((true == $background) ? ' > '.$backgroundfile.' &' : ''));
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
	public function saveConfigFileParameter($configfile = '', $parameter = '', $value = '', $separator = '='){		
		if(file_exists($configfile)){
			$old_parameter = trim($this->getConfigFileParameter($configfile, $parameter));
			
			if($old_parameter == $value){
				//No changes
				return false;
			}
			//Check for empty entry
			$param_exists = shell_exec('grep -aP "^[ \t]*'.$parameter.'" '.$configfile.' | wc -l');
			if($old_parameter != '' || $param_exists > 0){
				$this->writeDynamicScript(array('sed -i "s/^[ \t]*'.$parameter.'.*$/'.$parameter.$separator.$value.'/g" '.$configfile));
				$this->view->message[] = _("Update Configfile - existing Entry changed");
			}else{
				//check for Newline in Last Line in config file
				if(strpos(shell_exec('xxd -p '.$configfile.' | tail -c 3'), '0a') === FALSE){
					//Newline missing -> add one
					$parameter = "\n".$parameter;
				}
				$this->writeDynamicScript(array('echo "'.$parameter.$separator.$value.'" >> '.$configfile));
				$this->view->message[] = _("Update Configfile - new Entry created");
			}
		}
		else{
			$this->writeDynamicScript(array('echo "'.$parameter.$separator.$value.'" > '.$configfile));
			$this->view->message[] = _("Update Configfile - new Configfile created");
		}
		return true;
	}
	
	/**
	 * Function to delete specific Parameter from specified Configfile
	 * @param string $configfile
	 * @param string $parameter
	 */
	public function deleteConfigFileParameter($configfile = '', $parameter = ''){
		if(!file_exists($configfile))
			return false;
		$param_exists = shell_exec('grep -aP "^[ \t]*'.$parameter.'" '.$configfile.' | wc -l');
		if($param_exists > 0){
			$this->writeDynamicScript(array('sed -i "s/^[ \t]*'.$parameter.'.*$//g" '.$configfile));
		}
		return true;
	}
	
	/**
	 * Function to get specific Parameter from specified Configfile
	 * @return boolean
	 */
	public function getConfigFileParameter($configfile = '', $parameter = '', $separator = '\='){
		if(!file_exists($configfile))
			return false;
		$output = trim(shell_exec('grep -aP "^[ \t]*'.$parameter.'" '.$configfile.' | sed -n -e "s/^[ \t]*[A-Za-z_0-9\.]*'.$separator.'//p"'));
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
	 * @param $lastlines Show X lastlines of Progressfile
	 * @param $message Show this Message instead of "Installation started"
	 * @param $url Redirect URL after finished
	 * @return Message for Ajax-Output
	 */
	public function getProgressWithAjax($progressfile = '', $create = 0, $reloadWhenFinished = 0, $lastlines = 0, $message = false, $url = false){
		if(!file_exists($progressfile) && $create == 1){		
			//Create File and set Message Output for Ajax-Call
			shell_exec('echo `date +"%Y-%m-%d %H:%M:%S|"` > '.$progressfile);
			if($message)
				$this->view->message[] = $message;
			else
				$this->view->message[] = _('Installation startet - This Messages refreshes every 3 seconds to show current status of installation. If finished this message disappears.');
			//Separate Parameters from current Filename
			if(!url)
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
	 * Format Message-Output for view
	 * @param string $string content
	 * @param boolean $hide generate link to display content
	 */
	public function formatMessageOutput($string, $hide = false){
		if($hide)
			return nl2br($string);
		else{
			$id = rand();
			$out = '<a style="color: #21759b;" href="#javascript" onclick="document.getElementById(\''.$id.'\').style.display=\'\';return false;">'._("Click here to show detailed information").'</a>';
			$out .= '<span style="display:none;" id="'.$id.'"><br />'.nl2br($string).'</span>';
			return $out;
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
		$helpOnSidebar = $this->getHelpOnSidebar();
		if(file_exists(APPLICATION_PATH.'/view/footer_custom.php'))
			include_once(APPLICATION_PATH.'/view/footer_custom.php');
		else
			include_once(APPLICATION_PATH.'/view/footer.php');
		
		return true;
	}
	
	public function setHelpOnSidebar($active = 0){
		$this->saveConfigFileParameter('/opt/max2play/options.conf', 'showHelpOnSidebar', $active);
		return true;
	}
	
	public function getHelpOnSidebar(){		
		if($this->getConfigFileParameter('/opt/max2play/options.conf', 'showHelpOnSidebar') == "1"){
			global $helpSidebar;
			if(isset($helpSidebar) && $helpSidebar != false){
				return $helpSidebar;
			}			
			return false;
		}
		return false;
	}
	
	public function getHardwareInfo(){
		if(!$this->info->hardware){
			$output = shell_exec("cat /proc/cpuinfo | grep Hardware");
			$this->info->hardware = '';
			if(preg_match('=Hardware.*: ([^ ]*)=', $output, $matches)){
				if(strpos($output, 'BCM2708') || strpos($output, 'BCM2709')){
					$this->info->hardware = 'Raspberry PI';
					$this->info->chipset = trim($matches[1]);
				}else{			
					$this->info->hardware = trim($matches[1]);
					$this->info->chipset = trim($matches[1]);
				}
			}
		}
		return $this->info->hardware;
	}
	
	public function getFreeDiskSpace(){
		$this->getSystemUser();
		if($this->info->system_user == 'pi'){			
			$this->info->freespace = shell_exec("df -km /dev/root | tail -1 | awk '{print $4}'");
		}			
		if($this->info->system_user == 'odroid'){			
			$this->info->freespace = shell_exec("df -km /dev/mmcblk0p2 | tail -1 | awk '{print $4}'");
		}
		return $this->info->freespace;
	}
	
	public function getSystemUser(){
		if(!$this->info->system_user){
			$system_user = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'SYSTEM_USER');
			if($system_user)
				$this->info->system_user = $system_user;
			else
				$this->info->system_user = 'odroid';
		}
		return $this->info->system_user;
	}
	
	/**
	 * get Linux Version of System
	 * e.g. Ubuntu
	 * @return array 0=> Ubuntu 1=> trusty
	 */
	public function getLinuxVersion(){
		if($this->info->linux)
			return $this->info->linux;
		$output = trim(shell_exec('lsb_release -a 2>/dev/null | grep "Distributor\|Codename" | sed "s/Distributor ID:\t//;s/Codename:\t//"'), "\n");
		if($output){
			$this->info->linux = explode("\n", $output);			 
		}
		if(!isset($this->info->linux[0]) || $this->info->linux[0] == ''){
			$this->info->linux[0] = 'Unknown';
		}			
		return $this->info->linux;
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
	
	/**
	 * Install a new Plugin from HTTP-Resource
	 * @param string $pathToPlugin
	 * @param autoenable add to navigation 
	 * @param position at position X
	 */
	public function installPlugin($pathToPlugin = '', $autoenable = false, $position = false, $default = false){
		$this->getEmail();
		if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$pathToPlugin)) {
			$this->view->message[] = _("Invalid Plugin-URL");
		}else{
			$linux = $this->getLinuxVersion();
			$add_params = '"?email='.$this->view->email.'&premium='.$this->checkLicense(true, true).'&hardware='.urlencode($this->getHardwareInfo()).'&linux='.urlencode($linux[0]).'"';
			$output = nl2br($this->writeDynamicScript(array('/opt/max2play/install_plugin.sh '.$pathToPlugin.' '.$add_params)));
			$this->view->message[] = $output;
				
			if($autoenable == true){
				if(preg_match("=Installing Plugin ([a-zA-Z0-9 _-]*)=", $output, $match)){
					if(preg_match('=\$this-\>pluginname \= \_\(\'(.*)\'=', shell_exec('grep -i \'$this->pluginname\' /opt/max2play/cache/newplugin/'.$match[1].'/controller/Setup.php'), $namematch)){
						$pluginname = $namematch[1];
	
						$this->enablePlugin($pluginname, $position, $default);

						$this->view->message[] = str_replace('$PLUGINNAME', _($pluginname),_('Plugin $PLUGINNAME activated and added to main navigation. You may change the position and visibility in the addon configuration on the <a href="/plugins/max2play_settings/controller/Basic.php#pluginconfigblock">settings page</a>.'));
						$this->loadViewHeader(true);
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * 
	 * @param array $pluginnames
	 */
	public function enablePlugin($pluginname = '', $position = false, $default = false){
		$this->parsePlugins();
		foreach($this->view->pluginselect as $key => $value){
			if($value['active'] == 1){
				if($position !== false && $key >= $position) $key++;
				$activeplugins[$key] = $value['name'];
			}
			if($value['default'] == 1)
				$defaultplugin = $value['name'];
		}
		if($position !== false)
			$activeplugins[$position] = $pluginname;
		else
			$activeplugins[] = $pluginname;
		if($default != false)
			$defaultplugin = $pluginname;
		$this->pluginConfig($activeplugins, $defaultplugin);
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
									//add version / timestamp to plugin to get later updates from config.txt if existing
									$updateURL = $lastUpdate = '';
									if(file_exists(APPLICATION_PATH.'/plugins/'.$file.'/config.txt')){
										$updateURL = $this->getConfigFileParameter(APPLICATION_PATH.'/plugins/'.$file.'/config.txt', 'UPDATEURL');
										$lastUpdate = $this->getConfigFileParameter(APPLICATION_PATH.'/plugins/'.$file.'/config.txt', 'LASTUPDATE');
									}
									$plugins_avail[$match[1]] = array('name' => $match[1],
											'navigation' => array('translate' => $match[1]),
											'path' => $path,
											'updateurl' => $updateURL,
											'lastupdate' => $lastUpdate,
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
	public function pluginConfig($pluginchoose = false, $defaultplugin = false){
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
	
	public function getEmail(){
		$this->view->email = $this->getConfigFileParameter('/opt/max2play/options.conf', 'email');
		return $this->view->email;
	}
	
	public function removePlugins($plugins = array()){		
		$this->parsePlugins();
		foreach($this->view->pluginselect as $key => $value){
			if($value['active'] == 1 && !in_array($value['name'], $plugins))
				$activeplugins[$key] = $value['name'];
			if($value['default'] == 1)
				$defaultplugin = $value['name'];
		}
		$this->pluginConfig($activeplugins, $defaultplugin);
		$this->loadViewHeader(true);
		return true;
	}
	
	/**
	 * use Alsaequal
	 */
	public function updateEqualizer($equalvalue, $user = false, $card='equal' ){
		if(!$user)
			$user = $this->getSystemUser();
		//if($_GET['use_equalizer'] == 1 && $this->checkLicense(true) == false)
		//	return true;
		if($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_EQUALIZER', ($_REQUEST['use_equalizer'] == 1) ? 1 : 0)){
			//Changes successful
		}
	
		foreach($this->equal as $key){
			$value = (isset($equalvalue[$key])) ? (int)$equalvalue[$key] : 66;
			$script[] = 'su - '.$user.' -c \'amixer -D '.$card.' -q set "'.$key.'" '.$value.'\'';
		}
	
		$this->view->message[] = $this->writeDynamicScript($script);
		$this->view->message[] = _("Updated Equalizer Settings");
	}
	
	/**
	 * get Alsaequal Settings
	 */
	public function getEqualizer($user = false, $card = 'equal'){
		$this->view->use_equalizer = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_EQUALIZER');
		if($this->view->use_equalizer){
			if(!$user)
				$user = $this->getSystemUser();
			foreach($this->equal as $key){
				$script = array('su - '.$user.' -c \'amixer -D '.$card.' sget "'.$key.'"\'');
				$output = $this->writeDynamicScript($script);
				preg_match('=\[(.*)\]=', $output, $match);
				$this->equalvalues[$key] = $match[1];
			}
		}
		return true;
	}
}

//Create Instance of Service Class
$service = new Service();
$service->loadDefaults();

