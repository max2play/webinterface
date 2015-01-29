<?php 

/**
 Callbot Setup Controller
 
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
class Callbot_Setup extends Service {
	
	/**
	 * To add your scripts in your own folder change the scriptPath to /opt/myscripts/
	 */
	public $scriptPath = '/opt/max2play/';
	
	/**
	 * Add further global variables like this one to get access from view
	 */
	
	protected $pname = 'callbot';
	
	public function __construct(){
		parent::__construct();
		$this->configfile = '/opt/max2play/options.conf';
		
		//Set your Pluginname
		$this->pluginname = _('Callbot Setup');
		
		if($this->checkLicense(true) == false)
			return true;
		
		//If Button clicked or Form sent do Something
		if(isset($_GET['action'])){
			if($_GET['action'] == 'save'){
				$this->_saveConfig($_GET['config']);
			}
			if($_GET['action'] == 'startcall'){
				$this->_startCall();
			}
			if($_GET['action'] == 'record'){
				$this->_recordWav();
			}
		}
		
		//Functions to call everytime
		$this->_getConfig();
		
		//Get Debug Info
		$this->_getAllLogs();
				
	}
	
	/**
	 * 
	 */
	private function _getConfig(){		
		$this->config->enabled = $this->getConfigFileParameter($this->configfile, 'CALLBOT_enabled');
		$this->config->phonenumber = $this->getConfigFileParameter($this->configfile, 'CALLBOT_phonenumber');
		$this->config->mp3_1 = $this->getConfigFileParameter($this->configfile, 'CALLBOT_mp3_1');
	}
	
	/**
	 * 
	 * 
	*/
	private function _saveConfig($config){		
		if($this->saveConfigFileParameter($this->configfile, 'CALLBOT_enabled', $config['enabled'])){			
			$this->view->message[] = _('Active status saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'CALLBOT_phonenumber', $config['phonenumber'])){
			$this->view->message[] = _('Phonenumber saved');
		}
		if($this->saveConfigFileParameter($this->configfile, 'CALLBOT_mp3_1', $config['mp3_1'])){
			$this->view->message[] = _('Message 1 saved');
		}
		return true;
	}
		
	
	/**
	 * get some Debug Output and save it for view
	 */
	private function _getAllLogs(){		
		
		$out['RUNNING LINPHONE'] = shell_exec('ps -Al | grep linphone');
		$out['LOGFILE'] = shell_exec('cat /opt/max2play/cache/callbot.txt');
	
		$this->view->debug = $out;
	}
	
	private function _startCall(){
		$this->_getConfig();
		
		$this->view->message[] = $this->writeDynamicScript(array(
				str_replace(array('$NUMBER', '$WAVFILE'), 
							array($this->config->phonenumber, $_GET['wavfile']), 
							'ACTION="play";'.$this->sip_start_call)));	
		
		return true;
	}
	
	private function _recordWav(){
		$this->_getConfig();
		$this->view->message[] = $this->writeDynamicScript(array(
				str_replace(array('$NUMBER', '$WAVFILE'),
						array($this->config->phonenumber, $_GET['wavfile-record']),
						'ACTION="record";'.$this->sip_start_call)));
		
		return true;
	}
	
	protected $sip_start_call = 'NOTREGISTERED=$(linphonecsh generic "status registered" | grep "registered=-1\|registered=0" | wc -l);
         if [ "$NOTREGISTERED" -gt "0" ]; then echo "NOT REGISTERED"; exit 1; fi
         linphonecsh generic "call $NUMBER";
		 linphonecsh generic "mute";
         STREAMRUNNING=0
         COUNTER=0
         while [ "$STREAMRUNNING" -lt "1" -a "$COUNTER" -lt "20" ]; do
           STREAMRUNNING=$(linphonecsh generic "calls" | grep StreamsRunning | wc -l);
           let COUNTER=COUNTER+1;
           sleep 1;
         done
         if [ "$STREAMRUNNING" -gt "0" ]; then			
            if [ "$ACTION" = "play" ]; then
                  echo "Start sending WAV-File $WAVFILE"                  
			      linphonecsh generic "unmute";
				  linphonecsh generic "play /opt/max2play/record$WAVFILE.wav";
                else
                  echo "Record WAV-File $WAVFILE"
                  rm /opt/max2play/record$WAVFILE.wav
                  linphonecsh generic "record /opt/max2play/record$WAVFILE.wav";
                fi
            sleep 10
         fi
         linphonecsh generic "terminate"
 	';
}

//Create an instance of Callbot
$callbot = new Callbot_Setup();

//This Line includes the View-Script -> it should have the same name as your class
include_once(dirname(__FILE__).'/../view/setup.php');

