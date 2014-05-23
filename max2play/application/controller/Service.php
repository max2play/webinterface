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
	public $autostartconf = '/opt/max2play/autostart.conf';
	public $dynamicScript = '/opt/max2play/dynamic_script.sh';
	
	public function __construct(){
		$this->view = new stdClass();
		$this->view->message = array(); // Array of Messages for View
		$this->info = new Info();				
	}
	
	public function status($name = ''){
		if($name == '')
			return false;
		
		$shellanswer = shell_exec("ps -Al | grep ".$name);		
		if($shellanswer != ''){
			preg_match('=([ ]?)([0-9]*)=', substr($shellanswer, 10,5), $match);
			if($match[2]){
				$process_id = $match[2];
				return $process_id;
			}		
		}
		return false;			
	}
	
	public function start($name = '', $command = '', $statusname = ''){
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
		shell_exec($startcom);
		
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
	 */
	public function updateAutostart($name = '', $active = false, $autostartconf = true){
		
		$output = preg_replace('=[\r\n ]*$=','',shell_exec('cat '.$this->autostartconf));
		
		if($name == '')
			return false;
		if($active == false){			
			if(!$autostartconf){
				shell_exec("sudo update-rc.d -f ".$name." remove");
			}else{
				shell_exec("echo '".str_replace($name.'=1', $name.'=0', $output)."' > ".$this->autostartconf);
			}
		}else{
			if(!$autostartconf){
				shell_exec("sudo update-rc.d ".$name." defaults");
			}else{
				shell_exec("echo '".str_replace($name.'=0', $name.'=1', $output)."' > ".$this->autostartconf);
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
	
	/**
	 * Write to File that has Root Rights to launch specific installations and configs
	 * $script is an array separated by lines for each task	
	 */
	public function writeDynamicScript($script = ''){
		$fp = fopen($this->dynamicScript, 'w+');
		
		fwrite($fp,"#!/bin/bash\n");
		
		foreach ($script as $s)
			fwrite($fp, "\n".$s);
		
		fclose($fp);
		$output = shell_exec('sudo '.$this->dynamicScript);
		return $output;
	}
	
}

//Create Instance of Service Class
$service = new Service();
$service->getPlayername();
$service->getAllNetworkPlayers();
$service->getVersion();

