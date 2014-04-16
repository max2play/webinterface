<?php 

/**
	 XBMC Administration Controller
	
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


class Xbmc extends Service {
	public $view;
	protected $pname = 'xbmc';
	private $_autostartfile = '/home/odroid/.config/lxsession/Lubuntu/autostart';
	//private $_autostartfile = '/home/odroid/.config/autostart_off/XBMC.desktop'; DEBIAN-Version
	
	public function __construct(){								
		
		if($_GET['action'] == 'start'){			
			//dafür muss unter www-data mittels ssh-keygen ein Key erzeugt und zu odroid exportiert werden!
			$this->view->message[] = $this->start($this->pname, '/usr/bin/ssh odroid@localhost "/etc/init.d/squeezelite stop;export DISPLAY=\':0\'; /usr/local/bin/xbmc > /dev/null 2>&1 &"');					
		}
		
		if($_GET['action'] == 'stop'){			
			$this->view->message[] = $this->stop('xbmc.bin', 'sudo kill $PID');
		}
		
		if($_GET['action'] == 'save'){			
			$this->selectAutostart((int)$_GET['autostart']);
		}
		
		$this->view->autostart = $this->checkAutostart($this->pname, true);
		$this->view->pid = $this->status($this->pname);
	}		
	
}

$sp = new Xbmc();
