<?php 

/**
 Raspberry Settings Controller
 
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
 *
 */
class Raspberrysettings_Setup extends Service {
		
	public $scriptPath = '';
	public $usbSoundCards = array('' => 'none', 'hifiberry-dacplus' => 'Hifi Berry Card', 'iqaudio-dacplus' => 'IQaudio Card');
	public $armFrequency = array('default','700', '800');
	
	public function __construct(){
		parent::__construct();
		$this->scriptPath = dirname(__FILE__).'/../scripts/';
		
		//Set your Pluginname
		$this->pluginname = _('Raspberry Settings');

		if($this->checkLicense(true) == false)
			return true;
		
		if($this->getHardwareInfo() != 'Raspberry PI'){			
			$this->view->message[] = _('This function is for Raspberry PI ONLY! It seems, that you do not have a Raspberry PI.');
			return false;
		}
		
		//get Configuration for USB-Cards and Performance (CPU / GPU)
		$this->_getDTOverlayConfig();
		
		$this->_getCPUGPUConfig();
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'save_dtoverlay'){
				$this->_saveDtoverlay();
			}
			if($_GET['action'] == 'save_performance'){
				$this->_saveCPUGPUConfig();
			}
		}		
		
		//Get Debug Info
		$this->_getAllLogs();
	}
	
	private function _getDTOverlayConfig(){
		$this->view->dtoverlay = $this->getConfigFileParameter('/boot/config.txt', 'dtoverlay');
		return true;
	}
	
	private function _getCPUGPUConfig(){
		$this->view->gpu_mem = $this->getConfigFileParameter('/boot/config.txt', 'gpu_mem');
		$this->view->arm_freq = $this->getConfigFileParameter('/boot/config.txt', 'arm_freq');
	}
	
	private function _saveCPUGPUConfig(){
		if(isset($_GET['gpu_mem']) && $_GET['gpu_mem'] < 512 && $_GET['gpu_mem'] > 16 && $_GET['gpu_mem'] != $this->view->gpu_mem){
			$this->saveConfigFileParameter('/boot/config.txt', 'gpu_mem', $_GET['gpu_mem']);			
			$this->view->message[] = _("GPU memory parameter changed");
		}elseif($_GET['gpu_mem'] == ''){
			$this->deleteConfigFileParameter('/boot/config.txt', 'gpu_mem');
			$this->view->message[] = _("GPU memory parameter removed");
		}

		if(isset($_GET['arm_freq']) && in_array($_GET['arm_freq'], $this->armFrequency)){
			if($_GET['arm_freq'] == 'default')
				$this->deleteConfigFileParameter('/boot/config.txt', 'arm_freq');
			else
				$this->saveConfigFileParameter('/boot/config.txt', 'arm_freq', $_GET['arm_freq']);
			$this->view->message[] = _("ARM-Frequency parameter changed");
		}
		$this->view->message[] = _('Reboot needed');
		$this->_getCPUGPUConfig();
		return true;
	}
	
	private function _saveDtoverlay(){
		if(isset($_GET['dtoverlay']) && in_array($_GET['dtoverlay'], array_keys($this->usbSoundCards))){
			$this->saveConfigFileParameter('/boot/config.txt', 'dtoverlay', $_GET['dtoverlay']);
			$this->view->message[] = _("Boot config parameters changed");
			$this->view->message[] = _('Reboot needed');
		}
		$this->_getDTOverlayConfig();
		return true;
	}
		
	
	/**
	 * get some Debug Output and save it for view
	 */
	private function _getAllLogs(){		
		
		$out['EXAMPLE'] = shell_exec('ps -Al | grep apache');
	
		$this->view->debug = $out;
	}
}

//Create an instance of your Class
$rs = new Raspberrysettings_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

