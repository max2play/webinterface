<?php 

/**
	 XBMC Administration Controller
	
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


class Xbmc extends Service {
	
	protected $pname = 'xbmc';
	public $viewname = 'Xbmc';
	private $_autostartfile = '/home/odroid/.config/lxsession/Lubuntu/autostart';
	//private $_autostartfile = '/home/odroid/.config/autostart_off/XBMC.desktop'; DEBIAN-Version
	
	public function __construct(){								
		parent::__construct();
		$this->pluginname = _('XBMC (Mediacenter)');
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'start'){			
				//Check auf Lightdm
				if($_GET['reinitx'] != FALSE){					
					$script = array('/etc/init.d/lightdm stop > /dev/null 2>&1 &','sleep 2', '/etc/init.d/lightdm start > /dev/null 2>&1 &');
					//$script = array('kill `cat /tmp/.X0-lock`', 'lightdm > /dev/null 2>&1 &');
					$output = $this->writeDynamicScript($script);
					sleep(5);
					$this->view->message[] = _('Restart Desktop-Manager completed (initialized Display)');
				}				
				//dafür muss unter www-data mittels ssh-keygen ein Key erzeugt und zu odroid exportiert werden!				
				$this->view->message[] = $this->start($this->pname, '/usr/bin/ssh odroid@localhost "/etc/init.d/squeezelite stop;/etc/init.d/squeezeslave stop;/etc/init.d/shairport stop;export DISPLAY=\':0\'; /usr/local/bin/xbmc > /dev/null 2>&1 &"');					
			}
			
			if($_GET['action'] == 'stop'){			
				$this->view->message[] = $this->stop('xbmc.bin', 'sudo kill -9 $PID');
			}
			
			if($_GET['action'] == 'save'){							
				$this->selectAutostart(isset($_GET['autostart']) ? 1 : 0);
			}
		}
		$this->view->autostart = $this->checkAutostart($this->pname, true);
		$this->view->pid = $this->status($this->pname);
		$this->getXbmcVersion();
	}

	public function installXBMC(){
		ignore_user_abort(true);
		set_time_limit(7200);
		$shellanswer = shell_exec("cat /opt/max2play/cache/install_xbmc.txt");
		if($shellanswer != ''){
			preg_match('=[0-9\: -]*=', $shellanswer, $started);
			//Use WGET Timestamp - install should never take more than 2 hours
			if((time() - 2*60*60) > strtotime(trim($started[0], '- '))){
				$this->view->message[] = _('Something went wrong in last Install Attempt - Deleting Progressfile');
				shell_exec("rm /opt/max2play/cache/install_xbmc.txt");
			}
			$shellanswer = preg_replace('=[0-9]{1,}s.*?[0-9]{1,}K[\. ]{10,}.*?[0-9]{1,}(M|K) =s', '', $shellanswer);
			$this->view->message[] = nl2br("Installationsfortschritt: (gestartet ".$started[0].") ". $shellanswer);
			return false;
		}else{
			if($_GET['downloadurl'] == ''){
				$this->view->message[] = _('No Link for download given');
				return false;
			}
			if($_GET['downloadurl'] != ''){
				$downurl = $_GET['downloadurl'];
			}
			$shellanswer = $this->writeDynamicScript(array("/opt/max2play/install_xbmc.sh update ".$downurl." > /dev/null &"));
			$this->view->message[] = _('Installation gestartet - Seite neu Laden oder Button erneut drücken, um Status der Installation zu sehen.');
			$this->view->message[] = _('Installationspaket: ').$downurl;
			return true;
		}
	}
	
	public function getXbmcVersion(){
		 $this->xbmcversion = $this->writeDynamicScript(array('dpkg -s xbmc | grep Version'));
		 return true;
	}
	
}

$sp = new Xbmc();
include_once(dirname(__FILE__).'/../view/xbmc.php');