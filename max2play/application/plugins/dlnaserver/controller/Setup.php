<?php 

/**
 DLNA-Server Setup Controller
 
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
class Dlnaserver_Setup extends Service {
		
	public $minidlna = array();
	
	public function __construct(){
		parent::__construct();
		
		//Set your Pluginname
		$this->pluginname = _('DLNA-Server');
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'installMiniDLNA'){
				$this->_installMiniDLNA();
			}
			if($_GET['action'] == 'uninstallMiniDLNA'){
				$this->_installMiniDLNA(true);
			}
			if($_GET['action'] == 'reloadMiniDLNA'){
				$this->_reloadMiniDLNA();
			}
		}
		$this->_getMiniDLNASetup();
		$this->_getDebug();
	}
	
	private function _reloadMiniDLNA(){
		$script[] = '/etc/init.d/minidlna force-reload';
		$this->view->message[] = $this->writeDynamicScript($script);
		return true;
	}
	
	/**
	 * Install MiniDLNA Service
	 * @return boolean
	 */
	private function _installMiniDLNA($uninstall = false){
		ignore_user_abort(true);
		set_time_limit(3000);
		if($uninstall){
			$script[] = '/opt/max2play/install_minidlna.sh';
			$this->writeDynamicScript($script);
			$this->view->message[] = _('MiniDLNA Installation removed');
		}else{
			$script[] = '/opt/max2play/install_minidlna.sh install';
			$this->writeDynamicScript($script);
			$this->view->message[] = _('MiniDLNA Installation completed - you may now edit detailed settings');
		}
		return true;
	}
	
	private function _getMiniDLNASetup(){
		if(!file_exists('/etc/minidlna.conf')){
			$this->minidlna['installed'] = false;
		}else{
			$this->minidlna['installed'] = true;
			if($_GET['action'] == 'saveMiniDLNA'){
				$this->view->message[] = _('MiniDLNA Settings saved');
				$script[] = 'sed -i \'s/^media_dir=.*/media_dir='.str_replace('/','\\/', $_GET['minidlnaMediapath']).'/\' /etc/minidlna.conf';
				$this->writeDynamicScript($script);
			}
			$output = shell_exec('cat /etc/minidlna.conf');
			//Grep Path
			if(preg_match_all("=^media_dir\=([^\n]*)=m", $output, $matches)){
				$this->minidlna['mediapath'] = $matches[1][0];
			}
		}
		return true;
	}
	
	/**
	 * get Debuginformation
	 */
	private function _getDebug(){
		$out = array();
		$out['MiniDLNA RUNNING'] = shell_exec('ps -Al | grep minidlna');
		if(file_exists('/opt/max2play/cache/minidlna/minidlna.log'))
			$out['MiniDLNA LOG'] = shell_exec('tail -20 /opt/max2play/cache/minidlna/minidlna.log');
		else
			$out['MiniDLNA LOG'] = shell_exec('tail -20 /var/log/minidlna.log');
		$this->view->debug = $out;
	}
	
}

//Create an instance of your Class
$dlnaserver = new Dlnaserver_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

