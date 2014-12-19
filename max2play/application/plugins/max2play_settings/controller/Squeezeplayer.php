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
	public $equal = array('01. 31 Hz', '02. 63 Hz', '03. 125 Hz', '04. 250 Hz', '05. 500 Hz', '06. 1 kHz', '07. 2 kHz', '08. 4 kHz', '09. 8 kHz', '10. 16 kHz');
	public $equalvalues = array();	
	
	public function __construct(){								
		parent::__construct();
		$this->pluginname = _('Audioplayer');
		
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
			
		}
		$this->getEqualizer();
		
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
		if($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_EQUALIZER', ($_GET['use_equalizer'] == 1) ? 1 : 0)){
			$this->view->message[] = _('Equalizer settings changed');
		}
		
		foreach($this->equal as $key){
			$value = (isset($equalvalue[$key])) ? (int)$equalvalue[$key] : 66;
			$script[] = 'su - odroid -c \'amixer -D equal -q set "'.$key.'" '.$value.'\'';
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
			foreach($this->equal as $key){
				$script = array('su - odroid -c \'amixer -D equal sget "'.$key.'"\'');
				$output = $this->writeDynamicScript($script);
				preg_match('=\[(.*)\]=', $output, $match);
				$this->equalvalues[$key] = $match[1];
			}		
		}
		return true;
	}
	
	/**
	 * Save Command-Line Options from
	 * squeezelite_soundcard
	 * squeezelite_commandline
	 */
	public function saveSqueezeliteCommandline(){
		$commandLine = array();
		if(in_array($_GET['squeezelite_soundcard'], array('plug:dmixer', 'plug:plugequal', 'hw:CARD=Audio,DEV=0', 'plughw:CARD=Audio,DEV=0', 'dmix:CARD=Audio,DEV=0', 'hw:CARD=Audio,DEV=1', 'plughw:CARD=Audio,DEV=1', 'dmix:CARD=Audio,DEV=1', 'iec958:CARD=Device,DEV=0', 'hw:CARD=Device,DEV=0', 'dmix:CARD=Device,DEV=0', 'plughw:CARD=Device,DEV=0')))
			$commandLine[] = '-o '.$_GET['squeezelite_soundcard'];
		else{
			$commandLine[] = '-o plug:dmixer';
		}
		//TODO: Regex für korrekte Erkennung der Commandlineeingabe der Parameter
		$commandLine[] = trim($_GET['squeezelite_commandline']);
		
		$value = trim(implode(' ', $commandLine));
		if($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'SQUEEZELITE_PARAMETER', $value)){
			$this->view->message[] = str_replace('$SERVICE', $this->viewname ,_('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
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
			$this->writeDynamicScript(array('sudo --user odroid amixer -c 1 sset Speaker 100%'));
		}else 
			$value = 0;
		
		if($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'USE_USB_DAC', $value)){
			$this->view->message[] = str_replace('$SERVICE', 'USB DAC' ,_('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
		}
		return true;
	}
	
	private function getAllLogs(){
		$out['SQUEEZELITE VERSION'] = shell_exec('/opt/squeezelite/squeezelite --help | grep ^Squeezelite');
		//$out['SQUEEZESLAVE VERSION'] = shell_exec('/opt/squeezeslave/squeezeslave -V | grep ^squeezeslave');
		$out['AUDIOPLAYER CONFIG'] = shell_exec('cat /opt/max2play/audioplayer.conf');		
		$out['SQUEEZELITE -l'] = $this->writeDynamicScript(array('/opt/squeezelite/squeezelite -l'));
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
		
// 		if($ajax == 0){
// 			ignore_user_abort(true);
// 			set_time_limit(3000);			
// 				$this->view->message[] = _('Update started');				
// 				if($this->getProgressWithAjax($outfile, 1, 0)){
// 					$shellanswer = $this->writeDynamicScript(array("/opt/max2play/update_callblocker.sh >> ".$outfile." &"));
// 				}			
// 		}else{
// 			$status = $this->getProgressWithAjax($outfile);
// 			$this->view->message[] = nl2br($status);
// 			if(strpos($status, 'Finished') !== FALSE){
// 				//Finished Progress - did not delete progressfile
// 				if(strpos($status, '-lpthread -lm -lrt -ldl -o squeezelite') !== FALSE){
// 					$this->view->message[] = _('UPDATE SUCCESSFUL');
// 					shell_exec('rm '.$outfile);
// 				}
// 				else
// 					$this->view->message[] = _('UPDATE NOT SUCCESSFUL');
// 			}
// 		}				
		
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
		
}

$sp = new Squeezeplayer();

//zusätzlich die Shairport konfig laden
include_once(dirname(__FILE__).'/Shairport.php');
include_once(dirname(__FILE__).'/Squeezeslave.php');
	  
include_once(dirname(__FILE__).'/../view/squeezeplayer.php');