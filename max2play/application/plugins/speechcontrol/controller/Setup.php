<?php 

/**
 Speechcontrol Setup Controller
 
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
class Speechcontrol_Setup extends Service {
	protected $pname = 'python';
	public $scriptPath = '';
	public $config;
	
	public function __construct(){
		parent::__construct();
		$this->scriptPath = dirname(__FILE__).'/../scripts/';
		$this->pluginname = _('Speech Control');
		$this->registerLocale(dirname(__FILE__).'/../locale', 'speechcontrol');		
		
		if($_GET['ajax'] == 1 && $_GET['action'] == 'start'){
			//Function to get Progress of Installation
			$this->_startJasper(1);
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
		if($_GET['ajax'] == 1 && $_GET['action'] == 'install'){
			//Function to get Progress of Installation
			$this->_install(1);
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
		
		if($this->checkLicense(true) == false)
			return true;			
		
		if(isset($_GET['action'])){						
			if($_GET['action'] == 'install'){
				$this->_install();
			}
			
			if($_GET['action'] == 'start'){
				$this->_startJasper();
			}
			
			if($_GET['action'] == 'stop'){
				$this->view->message[] = $this->stop($this->pname);
			}
				
			if($_GET['action'] == 'kill'){
				$this->view->message[] = $this->kill($this->pname);
			}
			
			if($_GET['action'] == 'uninstall'){
				$this->_uninstall();
			}
			if($_GET['action'] == 'readtest'){
				$this->_readText();
			}			
			if($_GET['action'] == 'saveSettings'){
				$this->_saveSettings();
			}
		}
		if($this->_checkInstall()){
			$this->_getSettings();
			$this->view->pid = $this->status($this->pname);
		}
		//Get Debug Info
		$this->_getAllLogs();
	}
	
	private function _startJasper($ajax = 0){		
		if($ajax == 0){
			ignore_user_abort(true);
			set_time_limit(15000);
			$this->view->message[] = _t('Install Jasper, Google STT, ESpeak TTS');
			if($this->getProgressWithAjax('/opt/max2play/cache/jasperoutput.txt', 1, 0, 20)){
				$shellanswer = nl2br($this->writeDynamicScript(array('sudo -u pi -H /opt/jasper/jasper.py > /opt/max2play/cache/jasperoutput.txt 2>&1 &')));				
			}
		}else{
			//Get only last 20 Lines
			$status = $this->getProgressWithAjax('/opt/max2play/cache/jasperoutput.txt',0, 0, 20);
			$this->view->message[] = nl2br($status);
			if(strpos($status, 'IOError') !== FALSE){
				shell_exec('rm /opt/max2play/cache/jasperoutput.txt');
				$this->view->message[] = _t('Process Terminated!');
			}
		}
		return true;
	}
	
	private function _install($ajax = 0){								
		if($ajax == 0){
			ignore_user_abort(true);
			set_time_limit(15000);
			$this->view->message[] = _t('Install Jasper, SphinxEngine STT, ESpeak TTS');
			if($this->getProgressWithAjax('/opt/max2play/cache/jasper.txt', 1, 0, 50)){
				$shellanswer = nl2br($this->writeDynamicScript(array($this->scriptPath.'install_jasper.sh '.$this->scriptPath.' >> /opt/max2play/cache/jasper.txt &')));
			}
		}else{
			//Get only last 20 Lines
			$status = $this->getProgressWithAjax('/opt/max2play/cache/jasper.txt',0, 0, 50);
			$this->view->message[] = nl2br($status);
			if(strpos($status, 'Finished') !== FALSE){
				shell_exec('rm /opt/max2play/cache/jasper.txt');
				$this->view->message[] = _t('Reboot is needed to work properly!');
			}
		}
		return true;			
	}
	
	private function _uninstall(){		
		$this->view->message[] = nl2br($this->writeDynamicScript(array($this->scriptPath.'uninstall_jasper.sh')));		
		return true;
	}
	
	private function _getSettings(){		
		$this->config->jasper->language = $this->getConfigFileParameter('/opt/max2play/options.conf', 'SPEECH_JASPER_LANGUAGE');		
		
		return true;
	}		
	
	private function _readText(){
		$this->view->message[] = _t('Start reading Text');
		$this->view->message[] = $this->writeDynamicScript(array('/usr/bin/espeak -v '.$_GET['readlang'].' -s '.$_GET['readspeed'].' "'.strip_tags($_GET['readtext']).'" --stdout | aplay -D '.$_GET['readdevice']));	
		return true;
	}
	
	/**
	 * Check important parts of installation
	 * @return boolean
	 */
	private function _checkInstall(){
		$this->config->jasper_installed = file_exists('/opt/jasper/client');		
		$this->config->espeak_installed = file_exists('/usr/bin/espeak');		
		if($this->config->jasper_installed && $this->config->espeak_installed){
			$this->view->installed = 1;
		}else{
			$this->view->installed = 0;
		}
		return $this->view->installed;
	}
	
	/**
	 * get some Debug Output and save it for view
	 */
	private function _getAllLogs(){		
		$out['JASPER Installed'] = $this->config->jasper_installed;
		$out['ESPEAK Installed'] = $this->config->espeak_installed;
		$out['POCKETSPHINX Installed'] = $this->config->pocketsphinx_installed;
		$out['JASPER Running'] = shell_exec('ps -Al | grep jasper');		
	
		$this->view->debug = $out;
	}
}

//Create an instance of your Class
$speechcontrol = new Speechcontrol_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

