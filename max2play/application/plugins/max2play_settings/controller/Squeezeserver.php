<?php 

/**
 Squeezeserver Administration Controller

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


class Squeezeserver extends Service {
	protected $pname = 'logitechmediaserver';
	protected $prozessname = 'squeezeboxserve';
	public $viewname = 'Squeezebox Server';
	public $lmsversions = array();
	private $scriptPath = '';
	
	public function __construct(){						
		parent::__construct();
		$this->pluginname = _('Squeezebox Server');
		$this->scriptPath = dirname(__FILE__).'/../scripts/';
		
		if($_GET['ajax'] == 1 && $_GET['action'] == 'plugininstall'){
			$this->_pluginInstall($ajax = 1);
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
		
		if($_GET['ajax'] == 1 && $_GET['action'] == 'install'){
			//Function to get Progress of Installation
			$_GET['downloadurl'] = '';
			$this->installLMS();
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
		
		$this->getNomysqueezebox();
		$this->getDelayedStartup();
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'start'){			
				$this->view->message[] = $this->start($this->pname, '', $this->prozessname);	
				sleep(10); //Timeout to get process in processlist
			}
			
			if($_GET['action'] == 'stop'){			
				$this->view->message[] = $this->stop($this->pname, '', $this->prozessname);
				sleep(10); //Timeout to get process off processlist
			}
			
			if($_GET['action'] == 'restart'){
				$this->view->message[] = $this->restart($this->pname, '', $this->prozessname);
				sleep(10); //Timeout to get process off processlist
			}
			
			if($_GET['action'] == 'save'){
				$this->selectAutostart(isset($_GET['autostart']) ? 1 : 0, false);
			}
			
			if($_GET['action'] == 'save_nomysqueezebox'){
				$this->saveNomysqueezebox(isset($_REQUEST['nomysqueezebox']) ? 1 : 0);
				$this->saveDelayedStartup($_REQUEST['delayedstartup']);
			}
			if($_GET['action'] == 'install'){
				$this->installLMS($_GET['lmsversion']);
			}
			
			if($_GET['action'] == 'showavailablelms'){
				$this->getLMSVersions();
			}
			if($_GET['action'] == 'plugininstall'){
				$this->_pluginInstall();
			}
		}
		
		$this->view->installed = $this->checkInstall();
		$this->view->autostart = $this->checkAutostart($this->pname);
		$this->view->pid = $this->status($this->prozessname);
		$this->getAllLogs();
	}
	
	/**
	 * Due to the lizenz of LMS (not completly GPL) the user needs to start the installation manual
	 * Preinstalled LMS on images is not allowed
	 * This needs the sudoers.d rights for the script /opt/max2play/install_lms.sh
	 */
	public function installLMS($lmsversion = ''){		
		ignore_user_abort(true);
		set_time_limit(7200);
		$shellanswer = shell_exec("cat /opt/max2play/cache/install_lms.txt");
		if($shellanswer != ''){
			preg_match('=[0-9\: -]*=', $shellanswer, $started);
			//Use WGET Timestamp - install should never take more than 2 hours			
			//Check install_lms.txt for running installation!
			$lastmodified_seconds = trim(shell_exec("expr $(date +%s) - $(date +%s -r /opt/max2play/cache/install_lms.txt)"), "\n");
			if($lastmodified_seconds > 600 || (time() - 2*60*60) > strtotime(trim($started[0], '- '))){
				$this->view->message[] = _('Something went wrong in last Install Attempt - Deleting Progressfile');
				shell_exec("rm /opt/max2play/cache/install_lms.txt");
			}
			$shellanswer = preg_replace('=[0-9]{1,}s.*?[0-9]{1,}K[\. ]{10,}.*?[0-9]{1,}(M|K) =s', '', $shellanswer);
			$shellanswer = preg_replace('=([0-9]{1,}(m|s))*=s', '', $shellanswer);
			$this->view->message[] = nl2br(_('Installation Progress: ')."(startet ".$started[0].") ". $shellanswer);			
			return false;
		}else{
			if(!isset($this->lmsversions[$lmsversion]) && $_GET['downloadurl'] == ''){
				$this->view->message[] = _('LMS-Version not existing');
				$this->view->message[] = '<!-- finished -->';
				return false;
			}
			if($_GET['downloadurl'] != '' && strpos($_GET['downloadurl'], 'http://downloads.slimdevices.com') === 0){
				$downurl = $_GET['downloadurl'];
			}else{
				$downurl = $this->lmsversions[$lmsversion];
			}
			$shellanswer = shell_exec("sudo /opt/max2play/install_lms.sh update ".$downurl." > /dev/null &");
			$this->view->message[] = _('Installation startet - This Messages refreshes every 3 seconds to show current status of installation. If finished this message disappears.');
			$this->view->message[] = _('Installation Package: ').$downurl;			
			$this->view->message[] = '<div id="msgprogress"></div><script type="text/javascript">setTimeout(function(){reloadprogress("msgprogress", "/plugins/max2play_settings/controller/Squeezeserver.php", 1)}, 3000);</script>';
			return true;
		}
	}
	
	/**
	 * Enable / Disable Services from Mysqueezebox.com
	 * @param string $deactivate
	 * @return boolean
	 */
	public function saveNomysqueezebox($deactivate = false){
		if($deactivate)
			$this->saveConfigFileParameter('/etc/default/logitechmediaserver', 'SLIMOPTIONS', '\"--nomysqueezebox --nomigration\"');
		else{
			$this->saveConfigFileParameter('/etc/default/logitechmediaserver', 'SLIMOPTIONS', str_replace($this->view->nomysqueezebox, '--nomysqueezebox --nomigration', ''));
		}
		$this->getNomysqueezebox();
		return true;
	}		
		
	public function getNomysqueezebox(){
		$this->view->nomysqueezeboxvalue = $this->getConfigFileParameter('/etc/default/logitechmediaserver', 'SLIMOPTIONS');
		if(strpos($this->view->nomysqueezeboxvalue, '--nomysqueezebox --nomigration') !== FALSE)
			$this->view->nomysqueezebox = true;
		else 
			$this->view->nomysqueezebox = false;
		return true;
	}
	
	public function getDelayedStartup(){
		$this->view->delayedStartup = $this->getConfigFileParameter('/opt/max2play/options.conf', 'SQUEEZESERVER_DELAY_START');
		return true;
	}
	
	public function saveDelayedStartup($seconds = 0){
		if($seconds != $this->view->delayedStartup){
			//Check for init.d script
			if(intval(trim(shell_exec('grep -i "SQUEEZESERVER_DELAY_START" /etc/rc.local | wc -l'))) == 0){
				$this->writeDynamicScript(array('sed -i "s@^exit 0@SQUEEZESERVER_DELAY_START=\$(grep -a \"SQUEEZESERVER_DELAY_START\" /opt/max2play/options.conf | sed -n -e \'s/^[A-Z_]*\=//p\')\n	if [ \"\$SQUEEZESERVER_DELAY_START\" -gt \"0\" ]; then\n		sleep \$SQUEEZESERVER_DELAY_START;/etc/init.d/logitechmediaserver restart\n	fi\nexit 0@" /etc/rc.local'));
			}
			$this->saveConfigFileParameter('/opt/max2play/options.conf', 'SQUEEZESERVER_DELAY_START', intval($seconds));
			$this->getDelayedStartup();
		}		
		return true;
	}	
	
	/**
	 * This needs the sudoers.d rights for the script /opt/max2play/install_lms.sh
	 * @return boolean
	 */
	public function checkInstall(){
		$shellanswer = shell_exec("sudo /opt/max2play/install_lms.sh check 2>/dev/null");
		if(strpos($shellanswer, 'installed=1') !== FALSE){
			return true;
		}else 
			return false;
	}
	
	/**
	 * Get all available LMS-Downloads for Debian
	 */
	public function getLMSVersions(){		
		$slimdevices_download = 'http://downloads.slimdevices.com/';
		$html = file_get_contents($slimdevices_download);
		
		//get Regular Builds
		$count = preg_match_all('/<td><a href="(LogitechMediaServer_v7.[9]+[^"]+)">([^<]*)<\/a><\/td>/i', $html, $files);
		for ($i = 0; $i < $count; ++$i) {
			$name = str_replace('_v','_',substr($files[1][$i], 0, strlen($files[1][$i]) -1));
			$this->lmsversions[substr($files[2][$i], 0, strlen($files[2][$i]) -1)] = $slimdevices_download.$files[1][$i].strtolower($name).'_arm.deb';
		}
		
		//get Nightly Builds
		$html = file_get_contents($slimdevices_download.'nightly/');
		$count = preg_match_all('/<a href=([^>]+(7\.8|7\.9))>([^<]*)<\/a><br>/i', $html, $versions);
		for ($i = 0; $i < $count; ++$i) {			
			$html_version = file_get_contents($slimdevices_download.'nightly/'.$versions[1][$i]);			
			$found = preg_match_all('/<a href="(.*_arm\.deb)">[^<]*<\/a>/i', $html_version, $html_link);
			if($found){
				$this->lmsversions['Nightly '.$versions[2][$i]] = $slimdevices_download.'nightly/'.$html_link[1][0];
			}
		}
		return true;
	}
	
	/**
	 * 1-Click-Installer for rather complicated Shairtunes installer
	 */
	private function _pluginInstall($ajax = 0){
		ignore_user_abort(true);
		set_time_limit(1800);
		if($_GET['lmsplugin'] == 'shairtunes')
			$this->view->message[] = $this->formatMessageOutput($this->writeDynamicScript(array($this->scriptPath.'lms_plugin_shairtunes.sh')));
		if($_GET['lmsplugin'] == 'shairtunes2')
			$this->view->message[] = $this->formatMessageOutput($this->writeDynamicScript(array($this->scriptPath.'lms_plugin_shairtunes.sh ShairTunes2')));		
		if($_GET['lmsplugin'] == 'shairtunes' || $_GET['lmsplugin'] == 'shairtunes2')
			$this->view->message[] = _('Next steps: Reboot the device (in settings -> reboot) and you are ready to use your Squeezeplayers as Airplay device.');
		
		if($_REQUEST['lmsplugin'] == 'googlemusic'){
			if($ajax == 0){
				if($this->getProgressWithAjax('/opt/max2play/cache/install_googlemusic.txt', 1, 1)){					
					$shellanswer = $this->writeDynamicScript(array($this->scriptPath."lms_plugin_googlemusic.sh >> /opt/max2play/cache/install_googlemusic.txt 2>&1 &"), false, true);					
				}
			}else{
				$status = $this->getProgressWithAjax('/opt/max2play/cache/install_googlemusic.txt', 0, 1, 30);
				$this->view->message[] = nl2br($status);
				if(strpos($status, 'Finished') !== FALSE){
					//Finished Progress - did not delete progressfile yet
					$this->view->message[] = _('GoogleMusic Plugin installed.<br><b>Important:</b> Now edit the settings of the Plugin within Squeezebox Server Settings, add your GoogleMusic credentials (including a mobile device ID!) and you are ready to go.');
					shell_exec('rm /opt/max2play/cache/install_googlemusic.txt');
				}
			}					
		}
		return true;
	}
	
	private function getAllLogs(){
		$out['SQUEEZESERVER LOG'] = shell_exec('cat /var/log/squeezeboxserver/server.log 2>/dev/null');
		$out['PERL VERSION'] = trim(shell_exec('perl -v | grep -e "v[0-9\.]\{5,\}" -o'));
		$this->view->perlversion = substr($out['PERL VERSION'], 1, 4);
		$this->view->debug = $out;
		return true;
	}
		
}

$sp = new Squeezeserver();
include_once(dirname(__FILE__).'/../view/squeezeserver.php');