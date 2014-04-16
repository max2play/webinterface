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

include_once('../application/model/Mount.php');

class Filesystem extends Service {
	
	//protected $_fstabPath = '/home/webuser/';
	protected $_fstabPath = '/etc/';
	
	public function __construct(){		
		parent::__construct();
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'add'){
				$this->addMount();
			}
			if(strpos($_GET['action'],'delete') !== FALSE){
				$pos = explode('_', $_GET['action']);
				$this->removeMount($pos[1]);
			}
		}
		
		$this->getMountsFstab();
	}
	
	public function addMount(){
		//Prüfen der Eingaben!
		$m = new Mount();
		
		$test1 = $m->setMountpoint($_GET['mountpoint']);
		$test2 = $m->setPath($_GET['path']);
		$test3 = $m->setType($_GET['type']);
		$test4 = $m->setOptions($_GET['options']);
		
		//Hinzufügen in FSTAB hinter ##USERMOUNT (Trennlinie für Usereigene Mounts)
		if($test1 && $test2 && $test3 && $test4){			
			shell_exec("echo ".$m->getMountpoint()." ".$m->getPath()." ".$m->getType()." ".$m->getOptions()." >> ".$this->_fstabPath."fstab");			
			$this->reloadMount();
			$this->view->message[] = _("Mountpoint successfully added");
			return true;
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
		shell_exec("sudo mount -a");
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
	 * Alles vor ##USERMOUNT gehört zu System Mountpoints
	 */
	private function _separateNonUserMounts($mounts){
		$mounts = explode("##USERMOUNT\n", $mounts);
		$usermounts = $mounts[1];
		return $usermounts;
	} 
}

$fs = new Filesystem();