<?php 

/**
 Setup for Jivelite Squeezebox Visualisation
 
 @Copyright 2014 Stefan Rick
 @author Stefan Rick
 Mail: stefan@rick-software.de
 Web: http://www.netzberater.de
 
 */

class Jivelite_Setup extends Service {	
	protected $pname = 'jivelite';
	public $viewname = 'Jivelite';
	public $scriptPath = '';
	
	public function __construct(){		
		parent::__construct();		
		$this->registerLocale(dirname(__FILE__).'/../locale', 'jivelite');
		$this->pluginname = _('Jivelite');				
		$this->scriptPath = dirname(__FILE__).'/../scripts/';
		
		if($this->checkLicense(true) == false)
			return true;
		
		if($_GET['ajax'] == 1 && $_GET['action'] == 'install'){
			//Function to get Progress of Installation
			$this->_install(1);
			ob_end_clean();
			echo implode('<br />', $this->view->message);
			ob_flush();
			die();
		}
				
		if(isset($_GET['action'])){
			if($_GET['action'] == 'start'){			
				//Check auf Lightdm
				if($_GET['reinitx'] != FALSE){					
					$script = array('/etc/init.d/lightdm stop > /dev/null 2>&1 &','sleep 2', '/etc/init.d/lightdm start > /dev/null 2>&1 &');
					$output = $this->writeDynamicScript($script);
					sleep(5);
					$this->view->message[] = _('Restart Desktop-Manager completed (initialized Display)');
				}
				if($this->getSystemUser() == 'pi'){
					//Check for XSesssion
					$this->writeDynamicScript(array('export DISPLAY=\':0\';su -l pi -c startx > /dev/null 2>&1 &'));
					sleep(3);
					$this->view->message[] = $this->start($this->pname, 'export DISPLAY=\':0\';sudo -u pi -H /opt/jivelite/jivelite/bin/jivelite > /dev/null 2>&1 &', '', true);
				}else{					
					$this->view->message[] = $this->start($this->pname, 'export DISPLAY=\':0\';sudo -u odroid -H /opt/jivelite/jivelite/bin/jivelite > /dev/null 2>&1 &', '', true);
				}
			}
			
			if($_GET['action'] == 'stop'){			
				$this->view->message[] = $this->stop('jivelite', 'sudo kill -9 $PID');
				if($this->getSystemUser() == 'pi'){
					$this->writeDynamicScript(array('killall lxsession'));
				}
			}
			
			if($_GET['action'] == 'save'){							
				$this->selectAutostart(isset($_GET['autostart']) ? 1 : 0);
				$this->setAutohidePanel(isset($_GET['autohidepanel']) ? 1 : 0);
			}
			if($_GET['action'] == 'install'){							
				$this->_install();
			}else{
				$this->getJLVersion();
			}
		}else{
			$this->getJLVersion();
		}
		$this->view->autostart = $this->checkAutostart($this->pname, true);
		$this->view->pid = $this->status($this->pname);	
		$this->getAutohidePanel();					
	}	
	
	/**
	 * try to get Version / at least show if it is installed
	 * @return boolean
	 */
	private function getJLVersion(){						
		if(file_exists('/opt/jivelite/jivelite/bin/jivelite'))
			$this->view->installed = true;
		else{
			$this->view->message[] = _t('Jivelite is not installed - start installation by clicking on INSTALL button at the end of the page!');
		}
		
		return true;
	}
	
	private function _install($ajax = 0){
		if($ajax == 0){
			if($this->getProgressWithAjax('/opt/max2play/cache/install_jivelite.txt', 1, 1)){				
				$shellanswer = $this->writeDynamicScript(array($this->scriptPath."install_jivelite.sh >> /opt/max2play/cache/install_jivelite.txt 2>&1 &"));
			}
		}else{
			$status = $this->getProgressWithAjax('/opt/max2play/cache/install_jivelite.txt');
			$this->view->message[] = nl2br($status);
			if(strpos($status, 'finished') !== FALSE){
				//Finished Progress - did not delete progressfile yet				
				$this->view->message[] = _('INSTALL SUCCESSFUL');
				shell_exec('rm /opt/max2play/cache/install_jivelite.txt');				
			}
		}			
	 	return true;
	}
	
	/**
	 * function to get autohide value for panel
	 */
	private function getAutohidePanel(){
		$autohide=$this->writeDynamicScript(array('grep -a autohide /home/odroid/.config/lxpanel/Lubuntu/panels/panel'));
		if(strpos($autohide, 'autohide=0') !== FALSE){
			$this->view->autohidepanel = 0;
		}else{
			$this->view->autohidepanel = 1;
		}
		return true;
	}
	
	/**
	 * function to remove Taskbar with autohide
	 */
	private function setAutohidePanel($hide = 0){
		$this->getAutohidePanel();
		if($this->view->autohidepanel != $hide){
			//set new value & restart			
			$this->writeDynamicScript(array('sed -i \'s/autohide='.$this->view->autohidepanel.'/autohide='.$hide.'/\' /home/odroid/.config/lxpanel/Lubuntu/panels/panel',
											'export DISPLAY=\':0\';sudo --user=odroid -H lxpanelctl restart'));
			//shell_exec('/usr/bin/ssh odroid@localhost "export DISPLAY=\':0\';lxpanelctl restart"');
			$this->view->message[] = _t('Updated Autohide Panel Setting to').' '.$hide;
		}
		return true;
	}
}

$jl = new Jivelite_Setup();

include_once(dirname(__FILE__).'/../view/setup.php');

