<?php 

/**
 * Klasse für Services mit Methoden zum Filesystem Mounten
 * 
 * Mounten immer über FSTAB - Schreibrechte geben nicht vergessen
 * 
 */

/**
	 Filesystem Administration Controller
	
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

include_once(APPLICATION_PATH.'/model/Mount.php');
include_once(APPLICATION_PATH.'/model/Samba.php');

class Filesystem extends Service {
	
	//protected $_fstabPath = '/home/webuser/';
	protected $_fstabPath = '/etc/';
	protected $_sambaconf = '/opt/max2play/samba.conf';
	
	public function __construct(){		
		parent::__construct();
		$this->pluginname = _('Filesystem Mount');
		
		if(isset($_REQUEST['ajax']) && isset($_REQUEST['shownetworkshares'])){
			ob_end_clean();
			$this->getSMBTree(true);			
			ob_flush();
			die();
		}
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'add'){
				$this->addMount();
			}
			if(strpos($_GET['action'],'delete') !== FALSE){
				$pos = explode('_', $_GET['action']);
				$this->removeMount($pos[1]);
			}
			if($_REQUEST['action'] == 'setfixmount'){
				$this->addMountpointSDA();
			}
		}
		
		if(isset($_GET['sambaaction'])){
			if($_GET['sambaaction'] == 'add'){
				$this->addSambashare();
			}
			if(strpos($_GET['sambaaction'],'delete') !== FALSE){
				$pos = explode('_', $_GET['sambaaction']);
				$this->removeSambashare($pos[1]);
			}
			if($_GET['sambaaction'] == 'savepassword'){
				$this->setSambaPassword();
			}
		}
		
		$this->getMountsFstab();
		$this->getSambaConfig();
		$this->getMountpointsSDA();
		$this->showHelpSidebar();
	}
	
	public function addMount($m = false){
		if(!$m){
			//Prüfen der Eingaben!
			$m = new Mount();
			
			$test1 = $m->setMountpoint(trim($_GET['mountpoint']));
			$test2 = $m->setPath(trim($_GET['path']));
			$test3 = $m->setType($_GET['type']);
			$test4 = $m->setOptions($_GET['options']);
		}else{
			$test1 = $test2 = $test3 = $test4 = true;
		}
		//only allowed in mnt and media to mount directories
		if(strpos($m->getPath(), '/mnt/') === 0 || strpos($m->getPath(), '/media/') === 0){			
			//richtiges Verzeichnis
			//Check ob Unterverzeichnis existiert
			if(!file_exists($m->getPath())){
				$this->writeDynamicScript(array('mkdir "'.$m->getPath().'"; chmod 777 "'.$m->getPath().'"'));
				$this->view->message[] = _("Directory created: ".$m->getPath());
			}
		}else{
			$test2 = false;
			$this->view->message[] = _("Wrong Path: ".$m->getPath());
		}
		
		//Hinzufügen in FSTAB hinter ##USERMOUNT (Trennlinie für Usereigene Mounts)
		if($test1 && $test2 && $test3 && $test4){			
			shell_exec("echo '".$m->getMountpoint(true, true)." ".$m->getPath()." ".$m->getType()." ".$m->getOptions()."' >> ".$this->_fstabPath."fstab");			
			if($this->reloadMount()){
				$this->view->message[] = _("Mountpoint successfully added");
				return true;
			}
		}
		$this->view->message[] = _("Mountpoint NOT added! Please refer to the description below!");
		
		return false;
	}
	
	public function removeMount($pos){
		//Komplette Zeile entfernen
		$this->getMountsFstab();
				
		$content = str_replace(array('/'), array('\/'), $this->view->mounts[$pos]->getMountpoint(true, true)." ".$this->view->mounts[$pos]->getPath()." ".$this->view->mounts[$pos]->getType()." ".$this->view->mounts[$pos]->getOptions());
		
		shell_exec("sed -n '/".$content."/!p' ".$this->_fstabPath."fstab > /tmp/fstab && cp /tmp/fstab ".$this->_fstabPath."fstab");	
		
		$this->view->message[] = _('Entry deleted');
		
		$this->reloadMount();
		return true;
	}
	
	public function reloadMount(){
		$reload = shell_exec("sudo mount -a 2>&1");
		if($reload != ''){
			$this->view->message[] = $reload;
			return false;
		}
		return true;
	}
	
	public function getMountsFstab(){
		$this->view->mounts = array();
		
		$allmounts = shell_exec("cat ".$this->_fstabPath."fstab");
		
		//Nur Mountpoints behalten, die vom User stammen! Andere Interessieren nicht
		$this->view->mount_txt = $this->_separateNonUserMounts($allmounts);
		
		$mounts = explode("\n",$this->view->mount_txt);	
		
		foreach($mounts as $mount){
			$mount = preg_replace('=[ ]+=', ' ',$mount);
			$mountvars = explode(" ",$mount);		
			
			if(count($mountvars) > 3){							
				$m = new Mount();
				
				$test1 = $m->setMountpoint($mountvars[0]);
				$test2 = $m->setPath($mountvars[1]);
				$test3 = $m->setType($mountvars[2]);
				$test4 = $m->setOptions($mountvars[3]);
	
				if($test1 && $test2 && $test3 && $test4)
					$this->view->mounts[] = $m;
			}
		}
		
		return true;
	}
	
	/**
	 * 
	 * Everything before ##USERMOUNT belongs to System Mountpoints and SHOULD NEVER BE CHANGED
	 */
	private function _separateNonUserMounts($mounts){
		$mounts = explode("##USERMOUNT\n", $mounts);
		$usermounts = $mounts[1];
		return $usermounts;
	}
	
	/**
	 * get Configuration of Samba Shares from /opt/max2play/samba.conf
	 * 
	 * Config File uses this Options - Example
	 * 			[SHARENAME]
	 * 			comment = max2play share
 	 *			path = /home/odroid
 	 *			writeable = yes
 	 *			create mode = 664
	 */
	public function getSambaConfig(){
		$this->view->sambashares = array();
		$shellanswer = shell_exec("cat ".$this->_sambaconf);
		//Split config
		if(preg_match_all('=(\[[^\[]*)=si', $shellanswer, $matches)){
			foreach($matches[1] as $match){
				$smb = new Samba($match);
				$this->view->sambashares[] = $smb;
			}
		}
		return true;		
	}
	
	/**
	 * Add a new Sambashare
	 * If this Method is called with $share as a already existing share it is not really adding a new one but deleting another -> see removeSambashare
	 * use testparm to check config
	 * @param Samba $share
	 * @return boolean
	 */
	public function addSambashare($share = false){
		if(!$share){
			$smb = new Samba();
			$test1 = $smb->setName($_GET['name']);
			$test2 = $smb->setOption('path', $_GET['path']);
			$test3 = $smb->setOption('comment', $_GET['comment']);
			$test4 = $smb->setOption('writeable', $_GET['writeable']);
			$test5 = $smb->setOption('create mode', $_GET['create_mode']);
		}else{
			$smb = $share;
			$test1 = true;
		}
		if($test1){						
			shell_exec("echo '".$smb->getShareConf()."' >> ".$this->_sambaconf);
			$this->getSambaConfig();
			if(!$share){
				$this->view->message[] = _("Sambashare successfully added");
				$this->restartSamba();
			}
			return true;
		}
		$this->view->message[] = _("Sambashare NOT added! Please refer to the description below!");
		return false;
	}
	
	/**
	 * Remove one Entry in Sambashares by
	 *  - get all active shares
	 *  - delete all shares
	 *  - add shares that shall remain
	 * @param number $pos
	 */
	public function removeSambashare($pos = 0){
		$this->getSambaConfig();
		shell_exec("echo '#Samba Config Max2Play' > ".$this->_sambaconf);
		
		$i = 0;
		foreach($this->view->sambashares as $share){
			if($i != $pos){
				$this->addSambashare($share);
			}
			$i++;
		}			
		
		$this->view->message[] = _('Entry deleted');
		
		$this->getSambaConfig();
		$this->restartSamba();
	}
	
	public function setSambaPassword(){		
		if(strlen(str_replace('*', '', $_GET['sambpass'])) > 0){			
			$output = shell_exec('sudo /opt/max2play/setSambaPass.sh "'.$_GET['sambpass'].'"');
			$this->view->message[] = _('Password changed').' - '.$output;
			$this->restartSamba();
		}
	}
	
	public function restartSamba(){
		shell_exec("sudo /etc/init.d/samba restart");
		$this->view->message[] = _('Samba Service restarted');
	}
	
	/**
	 * set a fix Mountpoint in FSTAB by UUID for SDA-Device to prevent different Mountpoints on Bootup
	 */
	public function addMountpointSDA(){
		$this->getMountsFstab();
		$this->getMountpointsSDA();
		foreach($_REQUEST['fixmount'] as $device => $value){
			if(!isset($this->view->mountpointsSDA[$device]['fixmounted']) || $this->view->mountpointsSDA[$device]['fixmounted'] == false){
				$m = new Mount();
					
				$test1 = $m->setMountpoint('UUID='.$this->view->mountpointsSDA[$device]['uuid']);
				$test2 = $m->setPath($this->view->mountpointsSDA[$device]['path']);
				$test3 = $m->setType($this->view->mountpointsSDA[$device]['type']);
				$test4 = $m->setOptions('defaults');
				
				$this->addMount($m);
			}
		}
		return true;
	}
	
	/**
	 * Get Mountpoints for external Devices (e.g. USB-Drives)
	 * @return boolean
	 */
	public function getMountpointsSDA(){
		$output = explode("\n", $this->writeDynamicScript(array("blkid")));
		if(isset($output[0])){
			$this->view->mountpointsSDA = array();
			foreach($output as $value){				
				if(preg_match('@(/dev/sd[^:]*): LABEL="([^"]*)" UUID="([^"]*)" TYPE="([^"]*)"@', $value, $match)){					
					$this->view->mountpointsSDA[$match[1]] = array('device' => $match[1], 'label' => $match[2], 'uuid' => $match[3], 'type' => $match[4]);
					if(isset($this->view->mounts[0])){
						foreach($this->view->mounts as $mnt){
							if($mnt->getMountpoint() == 'UUID='.$match[3]){
								$this->view->mountpointsSDA[$match[1]]['fixmounted'] = true;
							}
						}
					}
				}
			}
		}else
			$this->view->mountpointsSDA = false;		
		
		$output = explode("\n", shell_exec("mount | grep /dev/sd"));		 
		if(isset($output[0])){
		 	foreach($output as $value){	 		
		 		if(preg_match('=(/dev/sd[^ ]*) on (/[^ ]*) type =', $value, $match)){
		 			$this->view->mountpointsSDA[$match[1]]['path'] = $match[2];
		 		}
		 	}
		}else
			$this->view->mountpointsSDA = false;
		return true;
	}
	
	/**
	 * Get possible Network Shares and IPs to Print in assistant window
	 */
	public function getSMBTree($directoutput = true){
		$this->view->networkshares = array();
		$output = shell_exec('smbtree -N');
		if(preg_match_all('=(\\\\[^\$\t]*)[\t]+([^\n]*)$=im', $output, $matches)){
			for($i = 0; $i < count($matches[0]); $i++ ){
				$this->view->networkshares[] = array('serverpath' => str_replace('\\','/',$matches[1][$i]), 'description' =>  $matches[2][$i]);
			}
		}
		if($directoutput){
			echo "<br />"._("The following list shows available Network Resources. On some resources the name of the share (path) might be missing. To set up a network share you need the server name (or IP-address) and the name of the share. See help for the correct syntax.");
			echo "<br /><br /><table><tr><td><b>"._("Server / Path")."</b></td><td><b>"._("Description")."</b></td></tr>";
			foreach($this->view->networkshares as $value){
				echo "<tr><td>".$value['serverpath']."</td><td>".$value['description']."</td><td>";
				if(preg_match('=//[^/]+/=', $value['serverpath'], $match))
					echo "<input type='submit' onclick='document.getElementById(\"mountpoint\").value=\"".$value['serverpath']."\";$(\"body\").removeClass(\"loading\");return false;' value='"._("set path")."' />";
				echo "</td></tr>";
			}
			echo "</table>";
		}			
		return true;
	}
	
	public function showHelpSidebar(){
		global $helpSidebar;
		$helpSidebar['title'] = _('Help - Mounts / Shares');
		$helpSidebar['content'] = _('<ul><li>For mounting a network share from a Diskstation or any other network storage you need the servers IP-Address, the name of the Share and the Login information (user, password). Enter everything like it is shown below the input fields and <a href="https://www.youtube.com/watch?v=3klQkxF6iNA" target="blank">watch this video on mounting (enable subtitles for english)</a>, if you encounter any problems.</li><li>You may also see your connected USB-drives and set up Shares on this device (via Samba) to get access from other computers in your network.</li></ul>');
		return true;
	}
	
}

$fs = new Filesystem();
include_once(dirname(__FILE__).'/../view/filesystem.php');