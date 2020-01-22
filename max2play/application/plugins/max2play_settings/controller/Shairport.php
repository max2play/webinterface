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
class Shairport extends Service
{

    protected $pname = 'shairport';

    public $viewname = 'Shairport (Airplay)';

    public function __construct()
    {
        parent::__construct();
        
        $this->getParameters();
        
        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'startap') {
                $this->view->message[] = $this->start($this->pname, $command = '', $statusname = '', $rootstart = false, $background = '/tmp/shairport.txt');
            }
            
            if ($_GET['action'] == 'stopap') {
                $this->view->message[] = $this->stop($this->pname);
            }
            
            if ($_GET['action'] == 'killap') {
                $this->view->message[] = $this->kill($this->pname);
            }
            
            if ($_GET['action'] == 'save') {
                $this->selectAutostart(isset($_GET['autostartap']) ? 1 : 0);
                $this->saveShairportCommandline();
                $this->getParameters();
            }
        }
        $this->view->pid = $this->status($this->pname);
        
        $this->view->autostart = $this->checkAutostart($this->pname, true);
        
        $this->getShairportCommandline();
    }

    private function getParameters()
    {
        $this->view->squeezebox_serverip = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'LMSIP');
        $this->view->ignore_running_kodi = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'IGNORE_KODI_SHAIRPORT');
        return true;
    }

    /**
     * Save Command-Line Options from
     * shairport_soundcard
     */
    public function saveShairportCommandline()
    {
        if ($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'IGNORE_KODI_SHAIRPORT', intval($_REQUEST['ignore_running_kodi']))) {
            $this->view->message[] = str_replace('$SERVICE', 'Shairport', _('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
        }
        $commandLine = array();
        $setsoundcard = $_REQUEST['shairport_soundcard'];
        // Sounddevices werden in Squeezeplayer geladen!
        global $sp;
        
        if (in_array($setsoundcard, array_keys($sp->view->soundDevices))) {
            $commandLine[] = '-d ' . $setsoundcard;
        } else {
            $commandLine[] = '-d plug:plugequal';
        }
        $commandLine[] = trim($_REQUEST['shairport_commandline']);
        
        $value = trim(implode(' ', $commandLine));
        if ($this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'SHAIRPORT_PARAMETER', $value)) {
            $this->view->message[] = str_replace('$SERVICE', $this->viewname, _('Updated $SERVICE Settings - Restart $SERVICE to apply changes!'));
            
            if ($this->status($this->pname) !== FALSE) {
                // Restart Service
                $this->view->message[] = $this->stop($this->pname);
                $this->view->message[] = $this->start($this->pname);
            }
        }
        // Additional Parameters
        if ($_REQUEST['squeezebox_serverip'] != $this->view->squeezebox_serverip) {
            $this->saveConfigFileParameter('/opt/max2play/audioplayer.conf', 'LMSIP', str_replace('http://', '', $_REQUEST['squeezebox_serverip']));
            $this->getParameters();
        }
        
        return true;
    }

    /**
     * Get Commandline Options for Audioplayer by Config File
     * 
     * @return boolean
     */
    public function getShairportCommandline()
    {
        $output = $this->getConfigFileParameter('/opt/max2play/audioplayer.conf', 'SHAIRPORT_PARAMETER');
        if (preg_match_all('=-d ([^ ]*)( (.*))?=', $output, $match)) {
            $this->view->shairport_soundcard = trim($match[1][0]);
            $this->view->shairport_commandline = $match[3][0];
        } else {
            return false;
        }
        return true;
    }
}

$ap = new Shairport();
