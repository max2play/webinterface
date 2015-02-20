<?php 

/**
 Squeezelite Administration Controller

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

class Squeezeplayer extends Service {
	protected $pname = 'squeezelite';
	public $viewname = 'Squeezelite';
	public $soundDevices = array();
	public $soundDeviceLog = '';
	public $equal = array('01. 31 Hz', '02. 63 Hz', '03. 125 Hz', '04. 250 Hz', '05. 500 Hz', '06. 1 kHz', '07. 2 kHz', '08. 4 kHz', '09. 8 kHz', '10. 16 kHz');
	public $equalvalues = array();	
	
	public function __construct(){								
		parent::__construct();
		$this->pluginname = _('Audioplayer');
		
		$this->getSoundDevices();
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'start'){			
				$this->view->message[] = $this->start($this->pname);
			}
			
			if($_GET['action'] == 'stop'){			
				$this->view->message[] = $this->stop($this->pname);			
			}
			
			if($_GET['action'] == 'kill'){
				$this->view->message[] = $this->kill($this->pname);
			}
			
			if($_GET['action'] == 'save'){
				$this->selectAutostart(isset($_GET['autostartsqueeze']) ? 1 : 0);
				$this->saveSqueezeliteCommandline();
				$this->setUseUSB_DAC();
			}
			
			if($_GET['action'] == 'resetEqualizer'){
				$this->updateEqualizer(0);
			}
			if($_GET['action'] == 'saveEqualizer'){
				$this->updateEqualizer($_GET['settingsEqualizer']);
			}
			
			if($_GET['action'] == 'installLadspa'){
				$this->installLADSPA();
			}
			if($_GET['update_squeezelite'] == 1){
				$this->updateSqueezelite();
			}
			if($_GET['action'] == 'setAudioOutputPI'){
				$this->setAudioOutputPI($_GET['AudioOutputPI']);
			}
			
		}
		$this->getEqualizer();
		
		$this->getAudioOutputPI();
		
		$this->configLADSPA();
		
		$this->getAllLogs();		
		
		$this->getSqueezeliteCommandline();
		
		$this->view->pid = $this->status($this->pname);
		
		$this->view->autostart = $this->checkAutostart($this->pname, true);
	}
	
	/**
	 * use Alsaequal 
	 */
	public function updateEqualizer($equalvalue){		
		$user = $this->getSystemUser();
		//if($_GET['use_equalizer'] == 1 && $this->checkLicense(true) == false)
		//	return true;
		if($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_EQUALIZER', ($_GET['use_equalizer'] == 1) ? 1 : 0)){
			//Changes successful
		}
		
		foreach($this->equal as $key){
			$value = (isset($equalvalue[$key])) ? (int)$equalvalue[$key] : 66;
			$script[] = 'su - '.$user.' -c \'amixer -D equal -q set "'.$key.'" '.$value.'\'';
		}
		
		$this->view->message[] = $this->writeDynamicScript($script);
		$this->view->message[] = _("Updated Equalizer Settings");
	}
	
	/**
	 * get Alsaequal Settings
	 */
	public function getEqualizer(){				
		$this->view->use_equalizer = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_EQUALIZER');
		if($this->view->use_equalizer){
			$user = $this->getSystemUser();
			foreach($this->equal as $key){
				$script = array('su - '.$user.' -c \'amixer -D equal sget "'.$key.'"\'');
				$output = $this->writeDynamicScript($script);
				preg_match('=\[(.*)\]=', $output, $match);
				$this->equalvalues[$key] = $match[1];
			}		
		}
		return true;
	}
	
	/**
	 * get Playable Sounddevices for Squeezelite / Shairport from Squeezelite
	 * Style:
	 * default:CARD=ALSA              - bcm2835 ALSA, bcm2835 ALSA - Default Audio Device
	 */
	public function getSoundDevices(){
		$this->soundDeviceLog = $this->writeDynamicScript(array('/opt/squeezelite/squeezelite -l'));
		//Regex zerlegung: 
		// $matches[1][x] = Name
		// $matches[2][x] = Karte/Device
		// $matches[3][x] = Beschreibung
		// $matches[4][x] = Name ist PCM Software Device
		// $matches[5][x] = Beschreibung von PCM Software Device
		// $matches[6][x] = PCM Software Device ohne Beschreibung
		preg_match_all('=^[ ]*([^:\n]+)[ ]*:[ ]*([^-\n ]+)[ ]*-[ ]*(.*)[ ]*|^[ ]*([^\n- ]+)[ ]*-[ ]*(.*)[ ]*|^[ ]*([^\n-:]+)[ ]*$=im', $this->soundDeviceLog, $matches);
		$count = count($matches[1]);
		for($i = 0; $i < $count; $i++){
			$dev = array();
			//Fallunterscheidung je nach Devices (Software / Hardware / mit und ohne Hinweistext)
			if($matches[1][$i] !== ''){
				$dev['name'] = $matches[1][$i];
				$dev['card'] = $matches[2][$i];			
				$dev['description'] = $matches[3][$i];
				$this->view->soundDevices[$matches[1][$i].':'.$matches[2][$i]] = $dev;
			}elseif($matches[4][$i] !== ''){
				$dev['name'] = $matches[4][$i]; 
				$dev['card'] = '';
				$dev['description'] = $matches[5][$i];
				$this->view->soundDevices['plug:'.$matches[4][$i]] = $dev;
			}elseif($matches[6][$i] !== ''){
				$dev['name'] = $matches[6][$i];
				$dev['card'] = '';
				$dev['description'] = $matches[6][$i];
				$this->view->soundDevices['plug:'.$matches[6][$i]] = $dev;
			}
		}
		unset($this->view->soundDevices['plug:null']);
		
		return true;
	}
	
	/**
	 * Save Command-Line Options from
	 * squeezelite_soundcard
	 * squeezelite_commandline
	 */
	public function saveSqueezeliteCommandline(){
		$commandLine = array();
		$setsoundcard = $_GET['squeezelite_soundcard'];
		if(in_array($setsoundcard, array_keys($this->view->soundDevices))){//array('plug:dmixer', 'plug:plugequal', 'hw:CARD=Audio,DEV=0', 'plughw:CARD=Audio,DEV=0', 'dmix:CARD=Audio,DEV=0', 'hw:CARD=Audio,DEV=1', 'plughw:CARD=Audio,DEV=1', 'dmix:CARD=Audio,DEV=1', 'iec958:CARD=Device,DEV=0', 'hw:CARD=Device,DEV=0', 'dmix:CARD=Device,DEV=0', 'plughw:CARD=Device,DEV=0'))){			
			$commandLine[] = '-o '.$setsoundcard;
		}else{
			$commandLine[] = '-o plug:dmixer';
		}
		//TODO: Regex für korrekte Erkennung der Commandlineeingabe der Parameter
		$commandLine[] = trim($_GET['squeezelite_commandline']);
		
		$value = trim(implode(' ', $commandLine));
		if($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'SQUEEZELITE_PARAMETER', $value)){
			$this->view->message[] = str_replace('$SERVICE', $this->viewname ,_('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
			
			if($this->status($this->pname) !== FALSE){
				//Restart Service
				$this->view->message[] = $this->stop($this->pname);
				$this->view->message[] = $this->start($this->pname);
			}
		}				
		
		return true;
	}
	
	/**
	 * Get Commandline Options for Audioplayer by Config File
	 * @return boolean
	 */
	public function getSqueezeliteCommandline(){
		$output = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'SQUEEZELITE_PARAMETER');
		if(preg_match_all('=-o ([^ ]*)( (.*))?=', $output, $match)){
			$this->view->squeezelite_soundcard = trim($match[1][0]);
			$this->view->squeezelite_commandline = $match[3][0];
		}
		$this->view->use_usb_dac = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_USB_DAC');
		return true;
	}
	
	/**
	 * Plugin for audiophile headphone listening
	 */
	public function installLADSPA(){		
		//if($this->checkLicense(true) == false)
		//	return true;
		$this->view->message[] = nl2br($this->writeDynamicScript(array('/opt/max2play/install_ladspa.sh')));
		return true;
	}
	
	/**
	 * controls [ 700 4.5 ] means lowpass filter cut frequency (700) and crossfeed level (4.5 dB)
	 * Possible values: 300-1000 for lowpass filter and 1 to 15 for crossfeed level
	 * Presets: http://bs2b.sourceforge.net/
	 */
	public function configLADSPA(){
		$installcheck=shell_exec('grep -a controls /etc/asound.conf');
		if($installcheck != ''){
			$this->view->ladspa_installed = true;
			//controls [ 500 4.5 ]
			preg_match_all('=controls.\[.([0-9]*).([0-9\.]*).?\]=',$installcheck, $matches);
			$this->view->ladspa_lowpass = $matches[1][0];
			$this->view->ladspa_crossfeed = $matches[2][0];
		}else{
			$this->view->ladspa_installed = false;
			return true;
		}
		if($_GET['action'] == 'saveLadspa' && $_GET['lowpass'] && $_GET['crossfeed']){
			$frequency = $_GET['lowpass'];
			$crossfeed = $_GET['crossfeed'];
			$this->writeDynamicScript(array('sed -i "s/controls \[.*\]/controls [ '.$frequency.' '.$crossfeed.' ]/" /etc/asound.conf;alsa force-reload;'));
			$this->view->message[] = _('Settings saved! Set Squeezelite options (advanced-button) to use headphones output with "-o headphones"');
			$this->view->ladspa_lowpass = $frequency;
			$this->view->ladspa_crossfeed = $crossfeed;
		}
		return true;
	}
	
	public function setUseUSB_DAC(){						
		if($_GET['use_usb_dac'] == 1){
			$value = 1;
			//Set Soundoutput to 100% for Card 1 all Speakers - should be done on FIRST usage
			$user = $this->getSystemUser();
			$this->writeDynamicScript(array('sudo --user '.$user.' amixer -c 1 sset Speaker 100%'));
		}else 
			$value = 0;
		
		if($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_USB_DAC', $value)){
			$this->view->message[] = str_replace('$SERVICE', 'USB DAC' ,_('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
		}
		return true;
	}
	
	private function getAllLogs(){
		$out['SQUEEZELITE VERSION'] = shell_exec('/opt/squeezelite/squeezelite -t | grep ^Squeezelite');
		//$out['SQUEEZESLAVE VERSION'] = shell_exec('/opt/squeezeslave/squeezeslave -V | grep ^squeezeslave');
		$out['AUDIOPLAYER CONFIG'] = shell_exec('cat /opt/max2play/audioplayer.conf');		
		$out['SQUEEZELITE -l'] = $this->soundDeviceLog;
		$out['SHAIRPORT VERSION'] = shell_exec('/opt/shairport/shairport -V');
		$this->view->debug = $out;
		return true;
	}
	
	/**
	 * Remove Autostart for time during Update!
	 * Update Squeezelite from Sources and Compile it
	 * 
	 * @return boolean
	 */
	private function updateSqueezelite(){
		$outfile = '/opt/max2play/cache/update_squeezelite.txt';
		ignore_user_abort(true);
		set_time_limit(3000);	
		
		$autostart = $this->checkAutostart($this->pname, true);
		if($autostart){
			$this->selectAutostart(0);
		}
		$this->view->message[] = $this->stop($this->pname);
		
		//$script[] = 'wget http://squeezelite-downloads.googlecode.com/git/squeezelite-armv6hf -O /opt/squeezelite/squeezelite 2>&1; chmod 777 /opt/squeezelite/squeezelite';
		//libfaad-dev libmpg123-dev libmad0-dev
		$script[] = 'echo "Y" | apt-get install libav-tools libsoxr-dev;cd /tmp;git clone https://code.google.com/p/squeezelite/;cd squeezelite;OPTS="-DFFMPEG -DRESAMPLE -DVISEXPORT" make;cp /tmp/squeezelite/squeezelite /opt/squeezelite/;echo "Finished Update - Restart Device!";';
		$this->view->message[] = nl2br($this->writeDynamicScript($script));
		$this->view->message[] = $this->start($this->pname);
		
		if($autostart){
			$this->selectAutostart(1);
		}
		return true;
	}
	
	/**
	 * Function for Raspberry PI to switch Output between Default, Jack and HDMI
	 * Default: if hdmi is connected it will be choosen	
	 */
	private function setAudioOutputPI($value = 0){
		if($this->getHardwareInfo() == 'Raspberry PI'){
			$this->view->audioOutputPI = $this->writeDynamicScript(array('sudo -u pi amixer cset numid=3 '.$value));
			$this->view->message[] = str_replace('$VALUE',$value, _('Raspberry PI Audio Output set to $VALUE'));
		}
		return true;
	}
	
	/**
	 * Function for Raspberry PI to get Audio-Output 
	 * can be Default=0, Headphone-Jack=1 or HDMI=2
	 */
	private function getAudioOutputPI(){
		if($this->getHardwareInfo() == 'Raspberry PI'){
			$this->view->audioOutputPI = trim($this->writeDynamicScript(array('sudo -u pi amixer cget numid=3 | grep ": values=" | sed "s/.*values=//"')), "\n");
			return $this->view->audioOutputPI;
		}else
			return false;
	}
	
	/**
	 * To Trim all values of an array
	 * @param string $value reference
	 * @return boolean
	 */
	private function trim_value(&$value)
	{
		$value = trim($value);
		return true;
	}
		
}

$sp = new Squeezeplayer();

//zusätzlich die Konfig für Shairport und DLNA client laden
include_once(dirname(__FILE__).'/Shairport.php');
include_once(dirname(__FILE__).'/Gmediarender.php');
  
include_once(dirname(__FILE__).'/../view/squeezeplayer.php');