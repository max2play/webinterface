<?php 

/**
	 Shairport Administration Controller
	
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


class Shairport extends Service {
	public $view;
	protected $pname = 'shairport';
	public $viewname = 'Shairport (Airplay)';
	
	public function __construct(){								
		
		if($_GET['action'] == 'startap'){			
			$this->view->message[] = $this->start($this->pname);			
		}
		
		if($_GET['action'] == 'stopap'){			
			$this->view->message[] = $this->stop($this->pname);			
		}
		
		if($_GET['action'] == 'killap'){
			$this->view->message[] = $this->kill($this->pname);
		}
		
		if($_GET['action'] == 'save'){
			$this->selectAutostart((bool)$_GET['autostartap']);
		}
		
		$this->view->pid = $this->status($this->pname);
		
		$this->view->autostart = $this->checkAutostart($this->pname, true);
	}
		
}

$ap = new Shairport();
