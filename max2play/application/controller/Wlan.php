<?php 

/**
 Lan / Wlan Administration

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

include_once 'Service.php';

class Wlan extends Service {
	public $view;
	private $wpa_config = '/opt/max2play/wpa_supplicant.conf';
	private $networkinterfaces = '/etc/network/interfaces';
	private $mac_address = '/etc/smsc95xx_mac_addr';
	
	public function __construct(){								
		$this->_getWirelessConfig();
		
		if($_GET['action'] == 'save'){
			$this->_saveWirelessConfig();
			$this->view->message[] = "Daten gespeichert";
			$this->_getWirelessConfig();
		}

		if($_GET['action'] == 'scanWlanNetworks'){
			$this->_showWlanNetworks();
		}
	}
	
	private function _showWlanNetworks(){
		$shellanswer = shell_exec("sudo iwlist scan");
		if(strpos($shellanswer, 'Device or resource busy') !== false){
			$this->view->message[] = "WLAN Device reagiert nicht - Gerät neu starten.";
			return false;
		}
		preg_match_all('=ESSID:"(.{1,50}?)".*?Group Cipher : (TKIP|CCMP).*?Pairwise Ciphers \(1\) : (TKIP|CCMP).*?Authentication Suites \(1\) : (PSK)=is',$shellanswer, $matches);
		if(count($matches[1]) > 0){
			$this->view->message[] = "Netzwerke gefunden und in Auswahlliste eingefügt";
			for($i = 0; $i < count($matches[1]); $i++){
				$this->view->wlanNetworks[] = array('ESSID' => $matches[1][$i], 'GCIPHER' => $matches[2][$i], 'PCIPHER' => $matches[3][$i], 'AUTH' => $matches[4][$i]);
			}			
		}else{
			$this->view->message[] = "Keine Netzwerke gefunden";
		}
		return true;
	}
	private function _saveWirelessConfig(){
		$ssid = urldecode($_GET['ssid']);
		$psk = urldecode($_GET['psk']);
		if(in_array($_GET['groupcipher'], array('TKIP', 'CCMP'))){
			$gcipher = $_GET['groupcipher'];
		}else{
			$gcipher = $this->view->groupcipher;
		}
		
		$shellanswer = shell_exec("cat ".$this->wpa_config);
		$shellanswer = str_replace('ssid="'.$this->view->ssid.'"', 'ssid="'.$ssid.'"', $shellanswer);
		$shellanswer = str_replace('group="'.$this->view->groupcipher.'"', 'group="'.$gcipher.'"', $shellanswer);
		if(strlen(str_replace('*', '', $psk)) > 0 || $psk == ''){
			$shellanswer = str_replace('psk="'.$this->view->psk.'"', 'psk="'.$psk.'"', $shellanswer);
		}
		
		$shellanswer_eth = shell_exec("cat ".$this->networkinterfaces);				
		if($_GET['lanmac'] != '' && $this->view->lanmac != $_GET['lanmac'] && preg_match("=([1-9abcdef]{2}:){5}[1-9abcdef]{2}=", $_GET['lanmac'], $matches) == true){			
			//shell_exec("echo '".$_GET['lanmac']."' > ".$this->mac_address);					
			$shellanswer_eth = str_replace('hwaddress ether '.$this->view->lanmac, 'hwaddress ether '.$matches[0], $shellanswer_eth);
			shell_exec("echo '".$shellanswer_eth."' > ".$this->networkinterfaces);			
			$this->view->message[] = 'MAC-Adresse geändert - bitte Neustarten';
		}
		
		shell_exec("echo '".$shellanswer."' > ".$this->wpa_config);				
		
		//Wenn Netzwerk gesetzt muss dieses in der etc/network/interfaces geladen werden				
		if(strpos($shellanswer_eth, '#pre-up wpa_supplicant') !== FALSE){
			//WLAN ist deaktiviert
			$wlanstatus = false;
		}else{
			$wlanstatus = true;
		}
		
		if($ssid != '' && $wlanstatus == false && $_GET['wlan_configured'] != false){
			//WLAN aktivieren
			$this->view->message[] = 'WLAN aktiviert - bitte Neustarten';
			$shellanswer_eth = str_replace(
					array('#pre-up wpa_supplicant','#allow-hotplug wlan0','#auto wlan0','#iface wlan0 inet dhcp','#post-down killall'), 
					array('pre-up wpa_supplicant','allow-hotplug wlan0','auto wlan0','iface wlan0 inet dhcp','post-down killall'), 
					$shellanswer_eth);
			shell_exec("echo '".$shellanswer_eth."' > ".$this->networkinterfaces);
		}elseif(($ssid == '' || $_GET['wlan_configured'] == false) && $wlanstatus == true){
			$this->view->message[] = 'WLAN deaktiviert - kein Netzwerk gewählt - bitte Neustarten';
			$shellanswer_eth = str_replace(
					array('pre-up wpa_supplicant','allow-hotplug wlan0','auto wlan0','iface wlan0 inet dhcp','post-down killall'),  
					array('#pre-up wpa_supplicant','#allow-hotplug wlan0','#auto wlan0','#iface wlan0 inet dhcp','#post-down killall'),
					$shellanswer_eth);
			shell_exec("echo '".$shellanswer_eth."' > ".$this->networkinterfaces);
		}
		return true;
	}
	
	private function _getWirelessConfig(){		
		
		//Allgemeine Interface Config
		$shellanswer_if = shell_exec("/sbin/ifconfig");
		preg_match('=wlan0=', $shellanswer_if, $match);
		if($match[0]){
			$this->view->wlan_configured = true;
		}
		$this->view->ifconfig_txt = $shellanswer_if;
		
		
		$shellanswer_eth = shell_exec("cat ".$this->networkinterfaces);
		//Wenn Netzwerk gesetzt muss dieses in der etc/network/interfaces geladen werden
		if(strpos($shellanswer_eth, '#pre-up wpa_supplicant') !== FALSE){
			//WLAN ist deaktiviert
			$this->view->wlan_configured = false;
		}else{
			$this->view->wlan_configured = true;
		}
		
		//iwconfig zur Erkennung des WLAN-Sticks
		$shellanswer_iwconfig = shell_exec("/sbin/iwconfig | grep wlan0 | wc -l");
		if($shellanswer_iwconfig == 1){
			//WLAN-Device verfügbar
			$this->view->wlan_active = true;
		}				
		
		//WPA Konfiguration
		$shellanswer = shell_exec("cat ".$this->wpa_config);		
		preg_match('=ssid\="(.*?)"=', $shellanswer, $match);
		if($match[1]){
			$this->view->ssid = $match[1];
		}else{
			$this->view->ssid = '';
		}
		
		preg_match('=group\=(.*)=', $shellanswer, $match);		
		if($match[1]){
			$this->view->groupcipher = $match[1];
		}
		
		preg_match('=psk\="(.*?)"=', $shellanswer, $match);
		if($match[1]){
			$this->view->psk = $match[1];
			//Original durch Sterne ersetzen
			$star = '';
			for($i=0; $i<strlen($match[1]); $i++)
				$star .= '*';
			$this->view->webpsk = $star;			
		}else{
			$this->view->webpsk = '';
			$this->view->psk = '';			
		}
		
		//Netzwerk Konfiguration ETH0 / /etc/smsc95xx_mac_addr löschen -> wird neu zugewiesen		
		//$shellanswer = shell_exec("cat /etc/smsc95xx_mac_addr");
		//preg_match('=([0-9a-z:]*)=', $shellanswer, $match);		
		preg_match('=hwaddress ether ([0-9a-z:]*)=', $shellanswer_eth, $match);
		if($match[1]){
			$this->view->lanmac = $match[1];			
		}
		return true;
	}	
}

$wlan = new Wlan();
