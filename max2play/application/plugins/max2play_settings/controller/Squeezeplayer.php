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
			}
			
			if($_GET['action'] == 'resetEqualizer'){
				$this->updateEqualizer(0);
			}
			if($_GET['action'] == 'saveEqualizer'){
				$this->updateEqualizer($_GET['settingsEqualizer']);
			}
			
		}
		$this->getEqualizer();
		
		$this->view->pid = $this->status($this->pname);
		
		$this->view->autostart = $this->checkAutostart($this->pname, true);
	}
	
	/**
	 * use Alsaequal 
	 */
	public function updateEqualizer($equalvalue){		
		
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
		foreach($this->equal as $key){
			$script = array('su - odroid -c \'amixer -D equal sget "'.$key.'"\'');
			$output = $this->writeDynamicScript($script);
			preg_match('=\[(.*)\]=', $output, $match);
			$this->equalvalues[$key] = $match[1];
		}		
		return true;
	}
		
}

$sp = new Squeezeplayer();

//zusätzlich die Shairport konfig laden
include_once(dirname(__FILE__).'/Shairport.php');
include_once(dirname(__FILE__).'/Squeezeslave.php');
	  
include_once(dirname(__FILE__).'/../view/squeezeplayer.php');