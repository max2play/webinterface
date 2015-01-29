<?php 

/**
 Example Setup Controller
 
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
class Fhem_Setup extends Service {
	
	/**
	 * To add your scripts in your own folder change the scriptPath to /opt/myscripts/
	 */
	public $scriptPath = '/opt/max2play/';
	
	/**
	 * Add further global variables like this one to get access from view
	 */
	public $inputtext = 'My text';
	
	public function __construct(){
		parent::__construct();
		
		//Set your Pluginname
		$this->pluginname = _('Fhem Setup');
		
		//If Button clicked or Form sent do Something
		if(isset($_GET['action'])){
			if($_GET['action'] == 'doSomething'){
				$this->_doSomething();
			}
			
			if($_GET['action'] == 'save'){
				$this->view->message[] = _('Saved');
			}
		}
		
		//Functions to call everytime
		$this->_getSomething();
		
		//Get Debug Info
		$this->_getAllLogs();
	}
	
	/**
	 * Demo function to save some data for view
	 */
	private function _getSomething(){
		$this->view->example = 'This is an example';
	}
	
	/**
	 * Demo function to do Something
	 * e.g. Call Scripts and return Output to view
	 */
	private function _doSomething(){		
		$this->view->message[] = _('Do Something started');
		
		//#1 You can call scripts that do not need root-rights with:
		$output = nl2br(shell_exec('ls /opt/max2play/'));
		
		//Send Output to View-Script
		$this->view->message[] = 'Normalscript Output: '.$output;
		
		
		//#2 If you want to run scripts with root rights do this:		
		$output2 = nl2br($this->writeDynamicScript(array('ls /opt/max2play/')));
		
		$this->view->message[] = 'Rootscript Output: '.$output2;
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
$fhem = new Fhem_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

