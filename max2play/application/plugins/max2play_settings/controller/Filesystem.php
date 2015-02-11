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
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'add'){
				$this->addMount();
			}
			if(strpos($_GET['action'],'delete') !== FALSE){
				$pos = explode('_', $_GET['action']);
				$this->removeMount($pos[1]);
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
	}
	
	public function addMount(){
		//Prüfen der Eingaben!
		$m = new Mount();
		
		$test1 = $m->setMountpoint($_GET['mountpoint']);
		$test2 = $m->setPath($_GET['path']);
		$test3 = $m->setType($_GET['type']);
		$test4 = $m->setOptions($_GET['options']);
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
			shell_exec("echo ".$m->getMountpoint()." ".$m->getPath()." ".$m->getType()." ".$m->getOptions()." >> ".$this->_fstabPath."fstab");			
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
				
		$content = str_replace(array('/'), array('\/'), $this->view->mounts[$pos]->getMountpoint()." ".$this->view->mounts[$pos]->getPath()." ".$this->view->mounts[$pos]->getType()." ".$this->view->mounts[$pos]->getOptions());
		
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
}

$fs = new Filesystem();
include_once(dirname(__FILE__).'/../view/filesystem.php');