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
		$this->pluginname = _('Advanced Setup Odroid U3');
		
		if($_GET['ajax'] == 1 && $_GET['action'] == 'upgradeSystem'){
			//Function to get Progress of Installation			
			$this->_upgradeSystem(true);
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'disableLEDBlink'){
				$this->_setLEDBlink();				
			}			
			if($_GET['action'] == 'configurePowerButton'){
				$this->_configurePowerButton();
			}	
			if($_GET['action'] == 'upgradeSystem'){
				$this->_upgradeSystem();
			}
			if($_GET['action'] == 'checkUpgrade'){
				$this->_checkUpgrade();
			}
			if($_GET['action'] == 'installFlash'){
				$this->_installFlash();
			}	
		}
		$this->_getPowerButton();
		$this->_getDebug();
	}		
	
	private function _setLEDBlink(){
		//Call Scripts
		$this->view->message[] = _('Set LED-Blink - Reboot to activate changes');
		//Check, ob bereits die LEDs deaktiviert sind
		$output = shell_exec('cat /etc/init.d/rc.local');
		if(!preg_match('=Deactivate LED-Blink=', $output, $match)){
			$script[] = 'echo "#Deactivate LED-Blink start" >> /etc/init.d/rc.local';
			$script[] = 'echo "echo timer > /sys/class/leds/led1/trigger;echo 10 > /sys/class/leds/led1/delay_on;echo 10000 > /sys/class/leds/led1/delay_off" >> /etc/init.d/rc.local';
			$script[] = "echo timer > /sys/class/leds/led1/trigger;echo 10 > /sys/class/leds/led1/delay_on;echo 10000 > /sys/class/leds/led1/delay_off";
			$this->view->message[] = $this->writeDynamicScript($script);
		}else{
			//Remove Script from rc.local with sed
			$script[] = "sed '/#Deactivate.*/ {N; s/#Deactivate.*//g}' /etc/init.d/rc.local > /etc/init.d/rc.local.tmp && mv /etc/init.d/rc.local.tmp /etc/init.d/rc.local && chmod +777 /etc/init.d/rc.local";
			$this->view->message[] = $this->writeDynamicScript($script);
		}
	}		
	
	/**
	 * get Functions of Powerbutton
	 */
	private function _getPowerButton(){
		$output = shell_exec('cat /opt/max2play/pwrbutton.sh');
		if(preg_match('=#Short Press\r?\n(.*)\r?\n#Short Press END=',$output, $match)){
			$short_press = $match[1];
			if(strpos($short_press,'start_xbmc.sh') !== false){
				$this->powerbutton['shortpress'] = 'xbmc';
			}elseif(strpos($short_press,'poweroff') !== false){
				$this->powerbutton['shortpress'] = 'shutdown';
			}else{
				$this->powerbutton['shortpress'] = 'myscript';
				$this->powerbutton['short_script'] = $short_press;
			}
		}
		if(preg_match('=#Long Press\r?\n(.*)\r?\n#Long Press END=',$output, $match)){
			$long_press = $match[1];
			$this->powerbutton['longpress'] = 'myscript';
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
			$script[]= 'sed -i \'s/#Short Button Code/#Short Button Code\n#Short Press\n'.$this->_escapeSEDCommand('unset DISPLAY;(/etc/init.d/lightdm stop > /dev/null 2>&1 &);sleep 2;(/etc/init.d/lightdm start > /dev/null 2>&1 &);sleep 5;export DISPLAY=\':0\';(sudo --user=odroid -H -s /opt/max2play/start_xbmc.sh > /dev/null 2>&1 &)').'\n#Short Press END/\' /opt/max2play/pwrbutton.sh';			
		}
		if($_GET['powerbuttonshort'] == 'myscript'){
			$script[]= 'sed -i \'s/#Short Button Code/#Short Button Code\n#Short Press\n'.$this->_escapeSEDCommand($_GET['powerbuttonshort_script']).'\n#Short Press END/\' /opt/max2play/pwrbutton.sh';
		}
		
		if($_GET['powerbuttonlong'] == 'myscript' && $_GET['powerbuttonlong_script'] != ''){
			$script[]= 'awk \'/#Long Press/,/#Long Press END/ { next } 1\' /opt/max2play/pwrbutton.sh > /opt/max2play/pwrbutton.sh2;mv /opt/max2play/pwrbutton.sh2 /opt/max2play/pwrbutton.sh';
			$script[]= 'sed -i \'s/#Long Button Code/#Long Button Code\n#Long Press\n'.$this->_escapeSEDCommand($_GET['powerbuttonlong_script']).'\n#Long Press END/\' /opt/max2play/pwrbutton.sh';
		}
		$script[] = 'chmod 777 /opt/max2play/pwrbutton.sh';
		$this->writeDynamicScript($script);
		
		$this->view->message[] = _('Power Button Settings Changed');
		
		return true;
	}
	
	private function _checkUpgrade(){
		$script[] = 'apt-get update;/usr/lib/update-notifier/apt-check --human-readable';		
		$this->view->message[] = 'Output:';
		$this->view->message[] = nl2br($this->writeDynamicScript($script));
		return true;
	}
	
	/**
	 * Update & Upgrade System
	 * @return boolean
	 */
	private function _upgradeSystem($ajax = 0){				
		//$this->view->message[] = _('NOT YET IMPLEMENTED');
		//return false;
		$outfile = '/opt/max2play/cache/upgradeSystem.txt';
		if($ajax == 0){
			ignore_user_abort(true);
			set_time_limit(3000);
				$this->view->message[] = _('Update / Upgrade to latest Ubuntu started');
				if($this->getProgressWithAjax($outfile, 1, 0)){					
					//run it as a deamon to enable apache updates
					$shellanswer = $this->writeDynamicScript(array("dpkg --configure -a >> ".$outfile." 2>&1; apt-get update >> ".$outfile." 2>&1;apt-get -y upgrade >> ".$outfile." 2>&1;echo 'Finished' >> ".$outfile), false, true);
				}
		}else{
			$status = $this->getProgressWithAjax($outfile);
			$this->view->message[] = nl2br($status);
			if(strpos($status, 'Finished') !== FALSE){
				//Finished Progress - did not delete progressfile				
				$this->view->message[] = _('UPDATE ENDED - CHECK ABOVE LOG FOR ERRORS');
				shell_exec('rm '.$outfile);				
			}		
		}
		return true;
	}
	
	/**
	 * Escape & $ . * / [ \ ] ^ '
	 * @param string $string
	 * @return escaped string 
	 */
	private function _escapeSEDCommand($string = ''){
		return str_replace(array('\\', '&', '/', '$', '.', '*', '[', ']', '^'), array('\\\\','\\&', '\\/', '\\$','\\.', '\\*', '\\[', '\\]', '\\^'), $string);
	}
	
	/**
	 * get Debuginformation
	 */
	private function _getDebug(){
		$out = array();				
		$this->view->debug = $out;
	}
	
	/**
	 * Install Pepperflash to make Chromium work with Flash and youtube,etc.
	 */
	private function _installFlash(){
		$script[] = 'wget http://odroidxu.leeharris.me.uk/PepperFlash-12.0.0.77-armv7h.tar.gz -O /opt/max2play/cache/pepperflash.tar.gz;tar -xzf /opt/max2play/cache/pepperflash.tar.gz -C /usr/lib;';
		$script[] = 'sed -i \'s/CHROMIUM_FLAGS=""/CHROMIUM_FLAGS=" --ppapi-flash-path=\/usr\/lib\/PepperFlash\/libpepflashplayer.so --ppapi-flash-version=11.7.700.225"/\' /etc/chromium-browser/default';
		$this->view->message[] = 'Start Install';
		$this->view->message[] = $this->writeDynamicScript($script);
		$this->view->message[] = _('After Installation you must open the Chromium URL "chrome:plugins" and set check [Always allowed] for Adobe Flash player. Then Restart Max2Play.');
		return true;
	}
}

$as = new Advanced_Max2play_Setup();
include_once(dirname(__FILE__).'/../view/advancedsetup.php');

