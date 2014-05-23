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
	
	public function __construct(){		
		parent::__construct();
	
		if(isset($_GET['action'])){
			if($_GET['action'] == 'disableLEDBlink'){
				$this->_setLEDBlink();				
			}
							
		}
			
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
}

$advanced_max2play_setup = new Advanced_Max2play_Setup();

include_once(dirname(__FILE__).'/../view/setup.php');

