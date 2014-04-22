<?php 

/**
   Basic Administration Controller
    
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

class Basic extends Service {	
	
	public function __construct(){								
		parent::__construct();
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'reboot'){
				shell_exec('sudo /sbin/reboot -n');
				$this->view->message[] = _("REBOOT gestartet");
			}
			
			if($_GET['action'] == 'reset'){
				$this->view->message[] = $this->resetFactoryDefaults();
			}
			
			if($_GET['action'] == 'save'){
				$this->view->message[] = $this->updatePlayername($_GET['playername']);
				$this->view->message[] = $this->updateDisplayResolution($_GET['displayResolution']);
				$this->updateMax2playNetworkLookup();
			}
		}
		$this->getMax2playNetworkLookup();
		$this->getPlayername();
		$this->getDisplayResolutions();
	}		
	
	public function getDisplayResolutions(){
		$output = shell_exec('ls /boot/ | grep boot-hdmi');
		preg_match_all("=boot-hdmi-(.*?).scr=", $output, $matches);
		if($matches[1])
			$this->view->displayResolutions = $matches[1];
		$output = shell_exec('cat /boot/boot.scr ');
		preg_match_all("=Boot\.scr for hdmi at ([0-9pihz]*)=", $output, $matches);
		
		if($matches[1][0])
			$this->view->currentResolution = $matches[1][0];
		return true;
	}
	
	public function updateDisplayResolution($newResolution = ''){
		$this->getDisplayResolutions();
		if($this->view->currentResolution != $newResolution && in_array($newResolution,$this->view->displayResolutions)){			
			$output = shell_exec('sudo cp /boot/boot-hdmi-'.$newResolution.'.scr /boot/boot.scr');
			return _('Changed display resolution - Reboot needed');
		}else{
			return _('no valid resolution choosen');
		}
	}
	
	public function getPlayername(){
		$output = shell_exec('cat /opt/max2play/playername.txt');
		$this->view->playername = $output;
		$output = shell_exec('cat /etc/hostname');
		$this->view->hostname = $output;
		return $output;
	}
	
	/**
	 * Change Player Name
	 */
	public function updatePlayername($name = ''){
		$name = preg_replace('=[^a-zA-Z0-9]=i', '', $name);
		if($name != ''){
			if($name != $this->view->playername){
				//Squeezelite anpassen
				$output = shell_exec('echo '.$name.' > /opt/max2play/playername.txt');
				
				//Hostname anpassen
				$output = shell_exec('echo '.$name.' > /etc/hostname');
			}
		}else{
			return _("Name not possible! Please do not use special characters.");
		}
		return _("Changes successful - Reboot needed");
	}
	
	/**
	 * Alle Grundeinstellungen wiederherstellen: Autostart, XBMC, PlayerName, Wlan, etc.
	 */
	public function resetFactoryDefaults(){
		//Ursprungsfiles sind als Sicherungen in den jeweiligen Ordnern enthalten
		$files = array('/opt/max2play/playername.txt', 
					   '/etc/hostname', 
				       '/etc/network/interfaces', 				 
					   '/etc/fstab',
					   '/boot/boot.scr',
					   '/opt/wpa_supplicant.conf'
				);
		
		foreach($files as $filename)
			$output = shell_exec('cat '.$filename.'.sav > '.$filename);
		
		return _("Config Files restored for ").": <br />".implode('<br />',$files);
	}
	
	public function getMax2playNetworkLookup(){
		$this->view->Max2PlayNetworkLookup = $this->checkAutostart('Max2PlayNetworkLookup', true);
	}
	
	public function updateMax2playNetworkLookup(){		
		$this->updateAutostart('Max2PlayNetworkLookup', (bool)$_GET['Max2PlayNetworkLookup'], true);
		$this->view->message[] = _('Max2Play Network Player Lookup saved');
	}
}

//Create Instance for view
$basic = new Basic();
