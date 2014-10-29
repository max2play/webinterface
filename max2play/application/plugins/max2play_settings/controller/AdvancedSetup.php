<?php 

/**
 Advanced Settings Setup Controller
 Important: For most scripts sudo rights (without password) for www-data are required -> add scriptnames to /etc/sudoers.d/max2play
 Example sudoers.d/max2play: www-data ALL=(root)NOPASSWD: /etc/init.d/squeezelite 

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

class Advanced_Max2play_Setup extends Service {
	public $powerbutton = array();
	
	public function __construct(){		
		parent::__construct();
		$this->pluginname = _('Advanced Setup');
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'disableLEDBlink'){
				$this->_setLEDBlink();				
			}
			if($_GET['action'] == 'installMiniDLNA'){
				$this->_installMiniDLNA();
			}
			if($_GET['action'] == 'configurePowerButton'){
				$this->_configurePowerButton();
			}				
		}
		
		$this->_getPowerButton();
	}		
	
	private function _setLEDBlink(){
		//Call Scripts
		$this->view->message[] = _('Set LED-Blink - Reboot to activate changes');
		//Check, ob bereits die LEDs deaktiviert sind
		$output = shell_exec('cat /etc/init.d/rc.local');
		if(!preg_match('=Deactivate LED-Blink=', $output, $match)){
			$script[] = 'echo "#Deactivate LED-Blink start" >> /etc/init.d/rc.local';
			$script[] = 'echo "echo timer > /sys/class/leds/led1/trigger;echo 10 > /sys/class/leds/led1/delay_on;echo 10000 > /sys/class/leds/led1/delay_off" >> /etc/init.d/rc.local';			
			$this->view->message[] = $this->writeDynamicScript($script);
		}else{
			//Remove Script from rc.local with sed
			$script[] = "sed '/#Deactivate.*/ {N; s/#Deactivate.*//g}' /etc/init.d/rc.local > /etc/init.d/rc.local.tmp && mv /etc/init.d/rc.local.tmp /etc/init.d/rc.local && chmod +777 /etc/init.d/rc.local";
			$this->view->message[] = $this->writeDynamicScript($script);
		}
	}
	
	private function _installMiniDLNA(){
		//Call Scripts
		$this->view->message[] = _('MiniDLNA Installation started');
		$output = shell_exec('sudo '.$scriptPath.'minidlna.sh');
		$this->view->message[] = $output;
	}
	
	/**
	 * get Functions of Powerbutton
	 */
	private function _getPowerButton(){
		$output = shell_exec('cat /opt/max2play/pwrbutton.sh');
		if(preg_match('=#Short Press\r?\n(.*)\r?\n#Short Press END=',$output, $match)){
			$short_press = $match[1];
			if(strpos($short_press,'xbmc') !== false){
				$this->powerbutton['shortpress'] == 'xbmc';
			}elseif(strpos($short_press,'shutdown') !== false){
				$this->powerbutton['shortpress'] == 'shutdown';
			}else{
				$this->powerbutton['shortpress'] == 'myscript';
				$this->powerbutton['short_script'] = $short_press;
			}
		}
		if(preg_match('=#Long Press\r?\n(.*)\r?\n#Long Press END=',$output, $match)){
			$long_press = $match[1];
			$this->powerbutton['longpress'] == 'myscript';
			$this->powerbutton['long_script'] = $long_press;
		}
		return true;
	}
	
	/**
	 * Save Powerbutton functionality
	 */
	private function _configurePowerButton(){
		//Reset current button behavior 
		$script[]= 'awk \'/#Short Press/,/#Short Press END/ { next } 1\' /opt/max2play/pwrbutton.sh > /opt/max2play/pwrbutton.sh2;mv /opt/max2play/pwrbutton.sh2 /opt/max2play/pwrbutton.sh';		
		
		if($_GET['powerbuttonshort'] == 'shutdown'){									
			$script[]= 'sed -i \'s/#Short Button Code/#Short Button Code\n#Short Press\npoweroff\n#Short Press END/\' /opt/max2play/pwrbutton.sh';
		}
		if($_GET['powerbuttonshort'] == 'xbmc'){
			$script[]= 'sed -i \'s/#Short Button Code/#Short Button Code\n#Short Press\n\/opt\/max2play\/start_xbmc.sh\n#Short Press END/\' /opt/max2play/pwrbutton.sh';			
		}
		if($_GET['powerbuttonshort'] == 'myscript'){
			$script[]= 'sed -i \'s/#Short Button Code/#Short Button Code\n'.str_replace('/','\/', $_GET['powerbuttonshort_script']).'\n#Short Press END/\' /opt/max2play/pwrbutton.sh';
		}
		
		if($_GET['powerbuttonlong'] == 'myscript' && $_GET['powerbuttonlong_script'] != ''){
			$script[]= 'awk \'/#Long Press/,/#Long Press END/ { next } 1\' /opt/max2play/pwrbutton.sh > /opt/max2play/pwrbutton.sh2;mv /opt/max2play/pwrbutton.sh2 /opt/max2play/pwrbutton.sh';
			$script[]= 'sed -i \'s/#Long Button Code/#Long Button Code\n#Long Press\n'.str_replace('/','\/', $_GET['powerbuttonlong_script']).'\n#Long Press END/\' /opt/max2play/pwrbutton.sh';
		}
		
		$this->writeDynamicScript($script);
		
		$this->view->message[] = _('Power Button Settings Changed');
		
		return true;
	}
}

$as = new Advanced_Max2play_Setup();
print_r($as->view->message);
include_once(dirname(__FILE__).'/../view/advancedsetup.php');

