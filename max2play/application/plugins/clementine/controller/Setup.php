<?php 

/**
 Clementine Setup Controller
 
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
class Clementine_Setup extends Service {
	
	/**
	 * To add your scripts in your own folder change the scriptPath to /opt/myscripts/
	 */
	public $scriptPath = '';
	
	public function __construct(){
		parent::__construct();
		
		$this->scriptPath = dirname(__FILE__).'/../scripts/';
		
		$this->pluginname = _('Clementine Setup');
		
		if($_GET['ajax'] == 1 && $_GET['action'] == 'install'){
			//Function to get Progress of Installation
			$this->_install(1);
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'install'){
				$this->_install();
			}
			
			if($_GET['action'] == 'save'){
				$this->view->message[] = _('Saved');
			}
		}

		//Get Debug Info
		$this->_getAllLogs();
	}	
	
	/**
	 * Install Clementine
	 */
	private function _install($ajax = 0){		
		if($ajax == 0){
			ignore_user_abort(true);
			set_time_limit(3000);
			$this->view->message[] = _('Clemetine install started');				
			if($this->getProgressWithAjax('/opt/max2play/cache/install_clementine.txt', 1, 0)){
				$shellanswer = $this->writeDynamicScript(array($this->scriptPath."install_clementine.sh >> /opt/max2play/cache/install_clementine.txt &"));
			}
		}else{
			$status = $this->getProgressWithAjax('/opt/max2play/cache/install_clementine.txt');
			$this->view->message[] = nl2br($status);
			if(strpos($status, 'Finished') !== FALSE){
				shell_exec('rm /opt/max2play/cache/install_clementine.txt');
			}
		}
		return true;
	}
	
	/**
	 * get some Debug Output and save it for view
	 */
	private function _getAllLogs(){		
		
		$out['INSTALLED'] = shell_exec('whereis clementine');
	
		$this->view->debug = $out;
	}
}

//Create an instance of your Class
$clementine = new Clementine_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

