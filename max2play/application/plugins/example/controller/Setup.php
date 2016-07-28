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
 * This is a PHP-File without HTML or Javascript Content
 *
 */
class Exampleclass_Setup extends Service {
		
	public $scriptPath = '';
	
	/**	 
	 * If working with Process Start / Stop / Kill, this is the name of the process
	 */
	protected $pname = 'processname';
	
	/**
	 * Name of the Process in the View-File (HTML-Content)
	 */
	public $viewname = 'Example-Viewname';
	
	/**
	 * Add further global variables like this one to get access from view
	 */
	public $inputtext = 'My text';
	
	public function __construct(){
		// Call Construct function from Service Class
		parent::__construct();
		
		//Set your Pluginname (for Navigation and Plugin-Configuration)		
		$this->pluginname = _('Exampleclass Setup');
		
		/** 
		 * Set Custom Translation File for Multilanguage
		 * Use PO-Edit to parse your Plugin-folder for _t() Translation strings 
		 * Translation strings are translated with your custom .po files in folder /locale/[iso-lang-code]/LC_MESSAGES/translation.po
		*/
		$this->registerLocale(dirname(__FILE__).'/../locale', 'example');
		
		/**
		 * Set Script Path to Plugin-Folder - create and Shell-Scripts for more Complex tasks
		 */
		$this->scriptPath = dirname(__FILE__).'/../scripts/';
		
		/**
		 * set up handler for automatic Page refresh (message output for progess during complex tasks that take some time) 
		 */
		$this->checkAjaxCall('install');
		
		/**
		 * If Button clicked or Form sent do Something
		 * Parse Request Object for action variable
		 */
		if(isset($_REQUEST['action'])){
			if($_REQUEST['action'] == 'doSomething'){
				$this->_doSomething();
			}
			
			if($_REQUEST['action'] == 'save'){
				$this->view->message[] = _('Saved');
			}
			
			if($_REQUEST['action'] == 'runMyScript'){
				$this->_runMyScript();
			}
			
			if($_REQUEST['action'] == 'install'){
				$this->install();
			}
			
			/**
			 * Start / Stop / Kill by Processname
			 */
			if($_REQUEST['action'] == 'start'){			
				$this->view->message[] = $this->start($this->pname, $command = '', $statusname = '', $rootstart = false, $background = false);
			}
			
			if($_REQUEST['action'] == 'stop'){			
				$this->view->message[] = $this->stop($this->pname);			
			}
			
			if($_REQUEST['action'] == 'kill'){
				$this->view->message[] = $this->kill($this->pname);
			}
		}
		
		// Check for running process of Processname
		$this->view->pid = $this->status($this->pname);
		
		//Functions to call everytime
		$this->_getSomething();
		
		//Get Debug Info
		$this->_getAllLogs();
		
		//Get Sidebar Infos
		$this->showHelpSidebar();
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
	 * Complex installer with Ajax Calls to get updates from running script
	 * @param int $ajax
	 * @return boolean
	 */
	public function install($ajax = 0){
		$progressfile = $this->scriptPath.'install_progress.txt';
		if($ajax == 0){
			// First run - start Script and create temporary file for Output				
			if($this->getProgressWithAjax($progressfile, $create = 1, $reloadWhenFinished = 1, $lastlines = 0, $message = _t("Install Something Started"))){				
				$shellanswer = $this->writeDynamicScript(array($this->scriptPath."install.sh >> ".$progressfile." 2>&1 &"), $background = false, $daemon = true);				
			}
		}else{
			// Ajax Call every 3 seconds to get progression
			$status = $this->getProgressWithAjax($progressfile, $create = 0, $reloadWhenFinished = 1, $lastlines = 30);
			$this->view->message[] = nl2br($status);
			// Parse Output for String "finished"
			if(strpos($status, 'finished') !== FALSE){
				//Finished Progress - did not delete progressfile yet
				$this->view->message[] = _t('Installation finished');
				//Delete Progressfile
				shell_exec('rm '.$progressfile);
			}
		}
		return true;
	}	
	
	/**
	 * Run Custom Script with Root-Rights
	 * Usefull for more complex tasks
	 * @return boolean
	 */
	private function _runMyScript(){
		$this->view->message[] = $this->formatMessageOutput($string = $this->writeDynamicScript(array($this->scriptPath.'myscript.sh')), $hide = true);
		return true;
	}
	
	/**
	 * get some Debug Output and save it for view
	 */
	private function _getAllLogs(){		
		$out['EXAMPLE'] = shell_exec('ps -Al | grep apache');	
		$this->view->debug = $out;
	}
	
	public function showHelpSidebar(){
		global $helpSidebar;
		$helpSidebar['title'] = _('Help - Example');
		$helpSidebar['content'] = _('<ul><li>This Plugin contains some example functions and code to copy&paste to create your own Plugin.</li><li>Use the <a href="https://www.max2play.com/en/portfolio/plugin-pluginbuilder-2/" target=_blank>Pluginbuilder</a> for development and the <a href="https://www.max2play.com/en/portfolio/plugin-remote-control/" target=_blank>Remote Control Plugin</a> for direct SSH-access.</li></ul>');
		$helpSidebar['wikilink'] = 'https://www.max2play.com/en/wiki/pluginbuilder/';
		return true;
	}
}

//Create an instance of your Class
$exampleclass = new Exampleclass_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

