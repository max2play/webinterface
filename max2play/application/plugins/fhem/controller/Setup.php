<?php 

/**
 FHEM Setup Controller
 
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
class Fhem_Setup extends Service {
		
	public $fhemconfig = '/opt/fhem/fhem.cfg';
	public $scriptPath = '';
	
	public function __construct(){
		parent::__construct();
		$this->scriptPath = dirname(__FILE__).'/../scripts/';		
		//Set your Pluginname
		$this->pluginname = _('Fhem Setup');
		$this->registerLocale(dirname(__FILE__).'/../locale', 'fhem');
		
		if($this->checkLicense(true) == false)
			return true;
		
		if($_GET['ajax'] == 1 && $_GET['action'] == 'install'){
			//Function to get Progress of Installation
			$this->_install(1);
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
		
		$this->_getFhem();
		
		//TODO: autostart settings
		if(isset($_GET['action'])){
			if($_GET['action'] == 'install'){
				$this->_install();
			}
			
			if($_GET['action'] == 'uninstall'){
				$this->_uninstall();
			}
			
			if($_GET['action'] == 'save'){
				$this->view->message[] = _('Saved');
			}
		}
		
		//Get Debug Info
		$this->_getAllLogs();
	}
	
	private function _getFhem(){
		if($this->view->fhemversion = shell_exec('dpkg -s fhem | grep Version')){
			if(preg_match('=[0-9]+=', shell_exec('cat '.$this->fhemconfig.' | grep "WEB FHEMWEB"'), $matches)){
				$this->config->port = $matches[0];
			}			
		}
		return true;
	}
	
	private function _install($ajax = 0){
		if($ajax == 0){
			ignore_user_abort(true);
			set_time_limit(3600);
			$this->view->message[] = _t('FHEM install started');	
			if($this->getProgressWithAjax('/opt/max2play/cache/install_fhem.txt', 1, 1)){
				$shellanswer = $this->writeDynamicScript(array($this->scriptPath."install.sh >> /opt/max2play/cache/install_fhem.txt &"));
			}
		}else{
			//Get only last 20 Lines
			$status = $this->getProgressWithAjax('/opt/max2play/cache/install_fhem.txt',0, 0, 20);
			$this->view->message[] = nl2br($status);
			if(strpos($status, 'Finished') !== FALSE){
				shell_exec('rm /opt/max2play/cache/install_fhem.txt');
			}
		}
		return true;
	}
	
	private function _uninstall(){
		$this->view->message[] = _('FHEM uninstall started');		
		$script[] = 'dpkg -P fhem';
		$this->view->message[] = $this->formatMessageOutput($this->writeDynamicScript($script));
		$this->_getFhem();
		return true;
	}
	
	/**
	 * get some Debug Output and save it for view
	 */
	private function _getAllLogs(){		
		$out = array();
		if($this->view->fhemversion){
			$out['FHEM Installation'] = shell_exec('dpkg -s fhem');
			
			$handle = opendir('/opt/fhem/log');
			$files = array();
			
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "empty_file.txt") {
					try {						
						$files[filemtime($file)] = $file;						
					} catch(Exception $e){
						
					}
				}
			}
			closedir($handle);
			
			ksort($files);
			
			$i=0;
			foreach($files as $file) {
				$out['Logfile '.$file] = shell_exec('cat /opt/fhem/log/'.$file);
				if($i++ > 1)
					break;
			}						
					
		}
		$this->view->debug = $out;
	}
}

$fhem = new Fhem_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

