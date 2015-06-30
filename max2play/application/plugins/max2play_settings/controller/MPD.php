<?php 

/**
	 MPD Administration Controller
	
	 @Copyright 2015 Stefan Rick
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


class MPD extends Service {
	protected $pname = 'mpd';
	public $viewname = 'MPD';
	
	public function __construct(){								
		parent::__construct();
		
		if(isset($_GET['action'])){
			if($_GET['action'] == 'startmpd'){			
				$this->view->message[] = $this->start($this->pname);			
			}
			
			if($_GET['action'] == 'stopmpd'){			
				$this->view->message[] = $this->stop($this->pname);			
			}
			
			if($_GET['action'] == 'killmpd'){
				$this->view->message[] = $this->kill($this->pname);
			}
			
			if($_GET['action'] == 'save'){
				$this->selectAutostart(isset($_GET['autostartmpd']) ? 1 : 0);
			}
		}
		$this->view->pid = $this->status($this->pname);
		
		$this->view->autostart = $this->checkAutostart($this->pname, true);
	}
	
	private function _install(){
		$script[] = 'sudo apt-get install mpd mpc alsa-utils --yes';
		//Edit Config File to Access MPD from every computer in network and remove fixed bitrates
		$script[] = 'sed -i "s@^bind_to_address.*@bind_to_address    \"any\"@" /etc/mpd.conf';
		
	}
		
}

$mpd = new MPD();
