<?php 

/**
 Squeezeserver Administration Controller

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


class Squeezeserver extends Service {
	protected $pname = 'logitechmediaserver';
	protected $prozessname = 'squeezeboxserve';
	public $viewname = 'Logitechmediaserver / Squeezeboxserver';
	
	public function __construct(){						
		parent::__construct();
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'start'){			
				$this->view->message[] = $this->start($this->pname, '', $this->prozessname);	
				sleep(10); //Timeout to get process in processlist
			}
			
			if($_GET['action'] == 'stop'){			
				$this->view->message[] = $this->stop($this->pname, '', $this->prozessname);
				sleep(10); //Timeout to get process off processlist
			}
			
			if($_GET['action'] == 'save'){
				$this->selectAutostart(isset($_GET['autostart']) ? 1 : 0, false);
			}
			
			if($_GET['action'] == 'install'){
				$this->installLMS();
			}
		}
		
		$this->view->installed = $this->checkInstall();
		$this->view->autostart = $this->checkAutostart($this->pname);
		$this->view->pid = $this->status($this->prozessname);
	}
	
	/**
	 * Due to the lizenz of LMS (not completly GPL) the user needs to start the installation manual
	 * Preinstalled LMS on images is not allowed
	 * This needs the sudoers.d rights for the script /opt/max2play/install_lms.sh
	 */
	public function installLMS(){
		ignore_user_abort(true);
		set_time_limit(7200);
		$shellanswer = shell_exec("cat /opt/max2play/cache/install_lms.txt");
		if($shellanswer != ''){
			preg_match('=[0-9\: -]*=', $shellanswer, $started);
			//Use WGET Timestamp - install should never take more than 2 hours			
			if((time() - 2*60*60) > strtotime(trim($started[0], '- '))){
				$this->view->message[] = _('Something went wrong in last Install Attempt - Deleting Progressfile');
				shell_exec("rm /opt/max2play/cache/install_lms.txt");
			}
			$shellanswer = preg_replace('=[0-9]{1,}s.*?[0-9]{1,}K[\. ]{10,}.*?[0-9]{1,}(M|K) =s', '', $shellanswer);
			$this->view->message[] = nl2br("Installationsfortschritt: (gestartet ".$started[0].") ". $shellanswer);
			return false;
		}else{
			$shellanswer = shell_exec("sudo /opt/max2play/install_lms.sh > /dev/null &");
			$this->view->message[] = _('Installation gestartet - Seite neu Laden oder Button erneut drücken, um Status der Installation zu sehen.');
			return true;
		}
	}
	
	/**
	 * This needs the sudoers.d rights for the script /opt/max2play/install_lms.sh
	 * @return boolean
	 */
	public function checkInstall(){
		$shellanswer = shell_exec("sudo /opt/max2play/install_lms.sh check");
		if(strpos($shellanswer, 'installed=1') !== FALSE){
			return true;
		}else 
			return false;
	}
		
}

$sp = new Squeezeserver();
