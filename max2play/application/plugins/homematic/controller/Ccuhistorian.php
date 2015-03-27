<?php 

/**
 Homematic Setup Controller
 
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

/**
 * 
 * @author Stefan Rick
 * Have a close look at the Service Class with all its functions!
 *
 */
include_once('Homematic.php');

class Ccuhistorian extends Homematic {		
	
	protected $pname = 'ccu-historian.sh';
	protected $prozessname = 'java';
	
	public function __construct(){
		parent::__construct();				
		
		$this->configfile = '/opt/ccu-historian/ccu-historian.config';
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'save'){
				$this->_saveConfig($_GET['config']);
			}
			if($_GET['action'] == 'install'){
				$this->_install();
			}
			if($_GET['action'] == 'start'){
				$this->view->message[] = $this->start($this->pname, '', 'java', true);
				sleep(15); //Timeout to get process in processlist
			}
			
			if($_GET['action'] == 'kill'){
				$this->view->message[] = $this->kill('java');
				sleep(4); //Timeout to get process in processlist
			}
				
			if($_GET['action'] == 'stop'){
				$this->view->message[] = $this->stop($this->pname, '', 'java', true);
				sleep(15); //Timeout to get process off processlist
			}
		}
		
		if($this->view->installed = $this->_checkInstall()){
			$this->_getConfig();
			
			$this->view->autostart = $this->checkAutostart($this->pname);
		}			
		
		$this->view->pid = $this->status($this->prozessname);
		
		//Get Debug Info
		$this->_getAllLogs();
				
	}
	
	private function _checkInstall(){
		if (file_exists($this->configfile))
			return true;
		else
			return false;
	}
		
	private function _getConfig(){		
		$this->config->webserver_port = $this->getConfigFileParameter($this->configfile, 'webServer.port');
		$this->config->devices_device1_type = $this->getConfigFileParameter($this->configfile, 'devices.device1.type');
		$this->config->devices_device1_address = $this->getConfigFileParameter($this->configfile, 'devices.device1.address');
		$this->config->devices_device1_plugin1_type = $this->getConfigFileParameter($this->configfile, 'devices.device1.plugin1.type');		
	}
		
	private function _saveConfig($config){				
		if($this->saveConfigFileParameter($this->configfile, 'webServer.port', $config['webserver_port'])){
			$this->view->message[] = _('Webserver Port gespeichert');
		}
		if($this->saveConfigFileParameter($this->configfile, 'devices.device1.type', $config['devices_device1_type'])){
			$this->view->message[] = _('Device Type gespeichert');
		}
		if($this->saveConfigFileParameter($this->configfile, 'devices.device1.address', $config['devices_device1_address'])){
			$this->view->message[] = _('Device Adresse gespeichert');
		}
		if($this->saveConfigFileParameter($this->configfile, 'devices.device1.plugin1.type', $config['devices_device1_plugin1_type'])){
			$this->view->message[] = _('Plugin Type gespeichert');
		}
		$this->selectAutostart(isset($config['autostart']) ? 1 : 0, false);
		
		return true;
	}
	
	private function _install(){		
		$this->view->message[] = nl2br($this->writeDynamicScript(array($this->scriptPath.'install_ccu_historian.sh "'.$_GET['downloadurl'].'" "'.$this->scriptPath.'"')));		
		return true;
	}
	
	private function _getAllLogs(){		
		
		$out['RUNNING'] = shell_exec('ps -Al | grep java');
		//$out['LOGFILE'] = shell_exec('cat /opt/max2play/cache/fritzbox.txt');
	
		$this->view->debug = $out;
	}
}

//Create an instance of your Class
$ccuhistorian = new Ccuhistorian();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/ccuhistorian.php');

