<?php 

/**
 Api Examples Setup Controller
 
 @Copyright 2018 Stefan Rick
 @author Stefan Rick
 Mail: stefan@rick-software.de
 Web: http://www.netzberater.de
  
 */

class ApiExamples_Setup extends Service {	
	public $scriptPath = '';	
	public $viewname = 'API-Examples';
	
	public function __construct(){
		parent::__construct();
		$this->pluginname = _('API-Examples');				
		$this->getExampleURLs();			
	}
			
	public function getExampleURLs(){
		// get Mac-Adress for example
		$hwaddr = explode('\n', $this->shell_exec('LANG=LC && /sbin/ip addr | /usr/bin/tr "\n" " " | grep -o "ether [a-f0-9:]\+ brd ff:ff:ff:ff:ff:ff \+inet [0-9.]\+" | grep -o "[a-f0-9:]\{17\}" | grep -v "ff:ff:ff:ff:ff:ff"'));
		$this->view->hwaddress = $hwaddr[0];
		
		/*better integration for Squeezebox Server*/		
		$sbs = explode(':', $this->shell_exec('/opt/max2play/discover_squeezebox_server | grep SERVER'));
		if(isset($sbs[1]) && $sbs[1] != ""){
			$this->view->realsbsip = trim($sbs[1]);
			$playerresult = preg_match_all("=playerindex%3A[0-9]+ playerid%3A([a-f0-9%A]+).*? ip%3A([^\s%]+).*? name%3A([^\s]+)=", $this->shell_exec('printf "players 0 20\nexit\n" | nc '. trim($sbs[1]).' 9090'), $matches);
			if(count($matches[1]) > 0){
				$this->view->sbsplayers = array();
				for($i = 0; $i < count($matches[1]); $i++){
					$this->view->sbsplayers[] = array('mac' => $matches[1][$i], 'name' => urldecode($matches[3][$i]), 'ip' => urldecode($matches[2][$i]));
				}
			}else{
				$this->view->sbsplayers = false;
			}
		}else{
			$this->view->realsbsip= false;
		}
		
		return true;
	}	
}

$apiexamples = new ApiExamples_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/apiexamples.php');

