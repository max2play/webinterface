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
class Homematic_Setup extends Service {
	
	/**
	 * To add your scripts in your own folder change the scriptPath to /opt/myscripts/
	 */
	public $scriptPath = '/opt/max2play/';
	
	/**
	 * Add further global variables like this one to get access from view
	 */
	
	protected $pname = 'presence_detection';
	
	public function __construct(){
		parent::__construct();
		$this->configfile = '/opt/max2play/options.conf';
		
		//Set your Pluginname
		$this->pluginname = _('Homematic Setup');
		
		//If Button clicked or Form sent do Something
		if(isset($_GET['action'])){
			if($_GET['action'] == 'save'){
				$this->_saveConfig($_GET['config']);
			}

		}
		
		//Functions to call everytime
		$this->_getConfig();
		
		//Get Debug Info
		$this->_getAllLogs();
		
		$this->view->autostart = $this->checkAutostart($this->pname, true);
	}
	
	/**
	 * 
	 */
	private function _getConfig(){
		$this->config->ip = $this->getConfigFileParameter($this->configfile, 'HM_FRITZ_IP');
		$this->config->user = $this->getConfigFileParameter($this->configfile, 'HM_FRITZ_USER');
		$this->config->secret = $this->getConfigFileParameter($this->configfile, 'HM_FRITZ_SECRET');
		$this->config->device_list = $this->getConfigFileParameter($this->configfile, 'HM_FRITZ_DEVICE_LIST');
		$this->config->hmvar = $this->getConfigFileParameter($this->configfile, 'HM_FRITZ_HMVAR');
		$this->config->looptime = $this->getConfigFileParameter($this->configfile, 'HM_FRITZ_LOOPTIME');
		$this->config->homematic_ip = $this->getConfigFileParameter($this->configfile, 'HM_HOMEMATIC_IP');
	}
	
	/**
	 * 
	 * 
	*/
	private function _saveConfig($config){		
		if($this->saveConfigFileParameter($this->configfile, 'HM_FRITZ_IP', $config['ip'])){			
			$this->view->message[] = _('IP saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'HM_FRITZ_USER', $config['user'])){
			$this->view->message[] = _('User saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'HM_FRITZ_SECRET', $config['secret'])){
			$this->view->message[] = _('Password saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'HM_FRITZ_DEVICE_LIST', $config['device_list'])){
			$this->view->message[] = _('Devicelist saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'HM_FRITZ_HMVAR', $config['hmvar'])){
			$this->view->message[] = _('Homematic Variables saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'HM_FRITZ_LOOPTIME', $config['looptime'])){
			$this->view->message[] = _('Looptime saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'HM_HOMEMATIC_IP', $config['homematic_ip'])){
			$this->view->message[] = _('Homematic IP saved');
		}
		$this->selectAutostart(isset($config['autostart']) ? 1 : 0);
		
		return true;
	}
		
	
	/**
	 * get some Debug Output and save it for view
	 */
	private function _getAllLogs(){		
		
		$out['RUNNING'] = shell_exec('ps -Al | grep fritzbox');
		$out['LOGFILE'] = shell_exec('cat /opt/max2play/cache/fritzbox.txt');
	
		$this->view->debug = $out;
	}
}

//Create an instance of your Class
$homematic = new Homematic_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

